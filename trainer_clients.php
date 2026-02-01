<?php
session_start();
require "db_connect.php";

// Redirect to login if not logged in or not a trainer
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$trainerId = $_SESSION['user_id'];

// Check Account Status explicitly to prevent access if status changes
$statusSql = "SELECT account_status FROM users WHERE user_id = ?";
$stmt = $conn->prepare($statusSql);
$stmt->bind_param("i", $trainerId);
$stmt->execute();
$resStatus = $stmt->get_result();
if ($resStatus->num_rows > 0) {
    $userStatus = $resStatus->fetch_assoc()['account_status'];
    if ($userStatus === 'pending') {
        header("Location: trainer_pending.php");
        exit();
    }
    if ($userStatus === 'inactive' || $userStatus === 'rejected') {
        session_destroy();
        header("Location: login.php?error=account_inactive");
        exit();
    }
}
$stmt->close();
$trainerName = $_SESSION['user_name'];
$trainerEmail = $_SESSION['user_email'];
$trainerInitials = strtoupper(substr($trainerName, 0, 1) . substr(explode(' ', $trainerName)[1] ?? '', 0, 1));

// Fetch assigned clients
$sql = "SELECT * FROM users WHERE assigned_trainer_id = ? AND assignment_status = 'approved'";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $trainerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $clients = [];
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
    $stmt->close();
} else {
    $clients = []; // Fallback if table/column missing
}

// If no clients are assigned yet, let's show some dummy data for demonstration if we are in "demo mode" 
// Or just show a message. For this task, I'll add a few dummy ones if the list is empty to show the design.
// If no clients are assigned, the list remains empty.
// No dummy data will be displayed.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Clients - FitNova Trainer</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Professional Color Palette matching Free User */
            --primary-color: #0F2C59;
            /* Deep Navy Blue */
            --secondary-color: #DAC0A3;
            /* Warm Beige/Champagne */
            --accent-color: #E63946;
            /* Professional Red */
            --bg-color: #F8F9FA;
            --sidebar-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6C757D;
            --border-color: #E9ECEF;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --border-radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles (Consistent with Dashboard) */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.02);
        }

        .sidebar-brand {
            padding: 30px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 24px;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo span {
            color: var(--secondary-color);
            font-size: 10px;
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
            font-weight: 700;
        }

        .sidebar-info {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            background: #fcfdfe;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #ef4444 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .user-details h4 {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .user-details p {
            font-size: 11px;
            color: var(--text-light);
            text-transform: uppercase;
        }

        .sidebar-menu {
            padding: 20px 0;
            flex: 1;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 30px;
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            border-left: 3px solid transparent;
        }

        .menu-item:hover, .menu-item.active {
            color: var(--primary-color);
            background-color: rgba(15, 44, 89, 0.05);
        }

        .menu-item.active {
            border-left-color: var(--primary-color);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        .user-profile-preview {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-sm {
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .user-info-sm h4 {
            font-size: 14px;
            margin-bottom: 2px;
            color: var(--text-color);
        }

        .user-info-sm p {
            font-size: 11px;
            color: var(--text-light);
            text-transform: uppercase;
        }

        .logout-btn {
            margin: 20px;
            padding: 12px;
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: #dc2626;
            color: white;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-title h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .header-title p {
            color: var(--text-light);
            font-size: 15px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 12px 15px 12px 40px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            width: 300px;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .btn-add {
            padding: 12px 20px;
            background: var(--primary-color);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .btn-add:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        /* Clients Grid */
        .clients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .client-card {
            background: white;
            border-radius: 15px;
            border: 1px solid var(--border-color);
            padding: 24px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .client-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .client-status {
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-pro { background: #eef2ff; color: #4f46e5; }
        .badge-free { background: #f8fafc; color: #64748b; }

        .client-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .client-image {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .client-name h4 {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }

        .client-name p {
            font-size: 13px;
            color: var(--text-light);
        }

        .client-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            padding: 15px 0;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 20px;
        }

        .stat-item span {
            display: block;
            font-size: 11px;
            color: var(--text-light);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .stat-item strong {
            font-size: 14px;
            color: #1e293b;
        }

        .client-actions {
            display: flex;
            gap: 10px;
        }

        .btn-client {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-view {
            background: var(--bg-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .btn-view:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .btn-plan {
            background: var(--primary-color);
            color: white;
        }

        .btn-plan:hover {
            background: var(--primary-dark);
        }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="home.php" class="brand-logo">
                <i class="fas fa-dumbbell"></i> FitNova <span>TRAINER</span>
            </a>
        </div>

        <nav class="sidebar-menu">
            <a href="trainer_dashboard.php" class="menu-item">
                <i class="fas fa-home"></i> Overview
            </a>
            <a href="trainer_clients.php" class="menu-item active">
                <i class="fas fa-users"></i> My Clients
            </a>
            <a href="trainer_schedule.php" class="menu-item">
                <i class="fas fa-calendar-alt"></i> Schedule
            </a>
            <a href="trainer_workouts.php" class="menu-item">
                <i class="fas fa-clipboard-list"></i> Workout Plans
            </a>
            <a href="trainer_diets.php" class="menu-item">
                <i class="fas fa-utensils"></i> Diet Plans
            </a>
            <a href="trainer_achievements.php" class="menu-item">
                <i class="fas fa-medal"></i> Achievements
            </a>
            <a href="trainer_performance.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Performance
            </a>
            <a href="trainer_messages.php" class="menu-item">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="client_profile_setup.php" class="menu-item">
                <i class="fas fa-user-circle"></i> Profile
            </a>
        </nav>

        <div class="user-profile-preview" style="padding: 20px; border-top: 1px solid #E9ECEF; display: flex; align-items: center; gap: 12px; margin-top: auto; background: #fff;">
            <div style="width: 40px; height: 40px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                <?php echo $trainerInitials; ?>
            </div>
            <div>
                <h4 style="font-size:15px; margin:0; color:#333; font-weight:600;"><?php echo htmlspecialchars($trainerName); ?></h4>
                <p style="font-size:11px; margin:0; color:#64748b; text-transform:uppercase; font-weight:600; letter-spacing:0.5px;">Expert Trainer</p>
            </div>
            <a href="logout.php" title="Logout" style="margin-left: auto; color: #64748b; text-decoration: none; font-size: 16px; transition: 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#64748b'">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header-section">
            <div class="header-title">
                <h2>My Clients</h2>
                <p>Manage and track your assigned clients</p>
            </div>
            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search by name or email...">
                </div>
                <button class="btn-add"><i class="fas fa-user-plus"></i> Add New</button>
            </div>
        </div>

        <?php if (empty($clients)): ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 15px; grid-column: 1 / -1; box-shadow: var(--shadow);">
                <i class="fas fa-users" style="font-size: 48px; color: var(--text-light); margin-bottom: 20px; opacity: 0.5;"></i>
                <h3 style="color: var(--text-color); margin-bottom: 10px;">No Clients Assigned Yet</h3>
                <p style="color: var(--text-light);">When clients are assigned to you, they will appear here.</p>
            </div>
        <?php else: ?>
        <div class="clients-grid">
            <?php foreach ($clients as $client): 
                $initials = strtoupper(substr($client['first_name'], 0, 1) . substr($client['last_name'], 0, 1));
                $memberSince = date('M Y', strtotime($client['created_at']));
                $roleBadge = ($client['role'] === 'pro') ? 'badge-pro' : 'badge-free';
                $roleText = ($client['role'] === 'pro') ? 'Pro Member' : 'Free Member';
            ?>
            <div class="client-card">
                <div class="client-status">
                    <span class="badge <?php echo $roleBadge; ?>"><?php echo $roleText; ?></span>
                </div>
                <div class="client-header">
                    <div class="client-image"><?php echo $initials; ?></div>
                    <div class="client-name">
                        <h4><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></h4>
                        <p><?php echo htmlspecialchars($client['email']); ?></p>
                    </div>
                </div>
                <div class="client-stats">
                    <div class="stat-item">
                        <span>Member Since</span>
                        <strong><?php echo $memberSince; ?></strong>
                    </div>
                    <div class="stat-item">
                        <span>Last Session</span>
                        <strong>2 days ago</strong>
                    </div>
                    <div class="stat-item">
                        <span>Workouts</span>
                        <strong>24 Completed</strong>
                    </div>
                    <div class="stat-item">
                        <span>Goal</span>
                        <strong>Weight Loss</strong>
                    </div>
                </div>
                <div class="client-actions">
                    <a href="view_client_profile.php?user_id=<?php echo $client['user_id']; ?>" class="btn-client btn-view">View Profile</a>
                    <button onclick="openAssignModal(<?php echo $client['user_id']; ?>, '<?php echo htmlspecialchars($client['first_name']); ?>')" class="btn-client btn-plan" style="border:none; cursor:pointer;">Assign Plan</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <!-- Assignment Modal -->
    <div id="assignModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Assign Plan</h3>
                <button class="close-btn" onclick="closeAssignModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>What type of plan do you want to assign to <strong id="modalClientName">Client</strong>?</p>
                <div class="assign-options">
                    <a href="#" id="assignWorkoutLink" class="assign-option">
                        <div class="icon-box"><i class="fas fa-dumbbell"></i></div>
                        <span>Workout Plan</span>
                    </a>
                    <a href="#" id="assignDietLink" class="assign-option">
                        <div class="icon-box" style="background: #ecfdf5; color: #10b981;"><i class="fas fa-apple-alt"></i></div>
                        <span>Diet Plan</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 2000; animation: fadeIn 0.2s; }
        .modal { background: white; width: 400px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: slideUp 0.3s; }
        .modal-header { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { font-size: 18px; margin: 0; color: #1e293b; }
        .close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #999; }
        .modal-body { padding: 25px; text-align: center; }
        .assign-options { display: flex; gap: 15px; margin-top: 20px; }
        .assign-option { flex: 1; padding: 20px; border: 1px solid #eee; border-radius: 10px; text-decoration: none; color: #333; transition: 0.2s; display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .assign-option:hover { background: #f8fafc; border-color: var(--primary-color); transform: translateY(-2px); }
        .icon-box { width: 50px; height: 50px; background: #eef2ff; color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>

    <script>
        // Simple search functionality
        const searchInput = document.querySelector('.search-box input');
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const cards = document.querySelectorAll('.client-card');
            cards.forEach(card => {
                const name = card.querySelector('.client-name h4').textContent.toLowerCase();
                const email = card.querySelector('.client-name p').textContent.toLowerCase();
                if (name.includes(term) || email.includes(term)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        function openAssignModal(userId, name) {
            document.getElementById('modalClientName').textContent = name;
            document.getElementById('assignWorkoutLink').href = 'trainer_workouts.php?assign_to=' + userId;
            document.getElementById('assignDietLink').href = 'trainer_diets.php?assign_to=' + userId;
            document.getElementById('assignModal').style.display = 'flex';
        }

        function closeAssignModal() {
            document.getElementById('assignModal').style.display = 'none';
        }
    </script>
</body>
</html>
