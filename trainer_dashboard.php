<?php
session_start();

// Redirect to login if not logged in or not a trainer
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

require "db_connect.php";

$trainerId = $_SESSION['user_id'];
$trainerName = $_SESSION['user_name'];
$trainerEmail = $_SESSION['user_email'];
$trainerInitials = strtoupper(substr($trainerName, 0, 1) . substr(explode(' ', $trainerName)[1] ?? '', 0, 1));

// Fetch assigned clients count
$countSql = "SELECT COUNT(*) as client_count FROM users WHERE assigned_trainer_id = ?";
$stmt = $conn->prepare($countSql);
if ($stmt) {
    $stmt->bind_param("i", $trainerId);
    $stmt->execute();
    $countResult = $stmt->get_result()->fetch_assoc();
    $clientCount = $countResult['client_count'] ?? 0;
    $stmt->close();
} else {
    $clientCount = 0; // Fallback
}

// Fetch Monthly Revenue (INR)
$revenueSql = "SELECT SUM(amount) as total FROM payments WHERE trainer_id = ? AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())";
$stmt = $conn->prepare($revenueSql);
$revenue = 0;
if ($stmt) {
    $stmt->bind_param("i", $trainerId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $revenue = $res['total'] ?? 0;
    $stmt->close();
}

// Fetch Rating
$ratingSql = "SELECT AVG(rating) as avg_rating FROM trainer_ratings WHERE trainer_id = ?";
$stmt = $conn->prepare($ratingSql);
$rating = "0.0"; // Default
if ($stmt) {
    $stmt->bind_param("i", $trainerId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res['avg_rating']) {
        $rating = number_format($res['avg_rating'], 1);
    }
    $stmt->close();
}

// Fetch Pending Requests
$pendingSql = "SELECT user_id, first_name, last_name, email FROM users WHERE assigned_trainer_id = ? AND assignment_status = 'pending'";
$stmt = $conn->prepare($pendingSql);
$pendingRequests = [];
if ($stmt) {
    $stmt->bind_param("i", $trainerId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $pendingRequests[] = $row;
    }
    $stmt->close();
}

// Fetch today's schedule
$today = date('Y-m-d');
$dashboardSchedules = [];

// Tables might not exist or schema differs, so use try/catch or simple check approach
$scheduleSql = "SELECT * FROM trainer_schedules WHERE trainer_id = ? AND session_date = ? ORDER BY session_time ASC LIMIT 4";
$stmt = $conn->prepare($scheduleSql);

if ($stmt) {
    $stmt->bind_param("is", $trainerId, $today);
    $stmt->execute();
    $scheduleResult = $stmt->get_result();
    while ($row = $scheduleResult->fetch_assoc()) {
        $dashboardSchedules[] = $row;
    }
    $stmt->close();
} else {
    // If table doesn't exist, we just show empty schedule
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard - FitNova</title>
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

        /* Sidebar Styles */
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

        .user-profile-preview {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: auto;
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

        .sidebar-menu {
            padding: 20px 0;
            flex: 1;
            overflow-y: auto;
        }

        .menu-item {
            padding: 12px 20px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: var(--transition);
            border-left: 3px solid transparent;
            text-decoration: none;
            font-weight: 500;
        }

        .menu-item:hover {
            background: #f1f5f9;
            color: var(--primary-color);
        }

        .menu-item.active {
            background: #eef2ff;
            color: var(--primary-color);
            border-left-color: var(--primary-color);
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

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7c3aed 100%);
            padding: 30px;
            border-radius: 15px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-banner h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            margin-bottom: 8px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 500;
        }

        /* Section Layout */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .section-box {
            background: white;
            padding: 25px;
            border-radius: 15px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Client Items */
        .client-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            border: 1px solid transparent;
            transition: var(--transition);
        }

        .client-item:hover {
            background: #f8fafc;
            border-color: var(--border-color);
        }

        .client-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        .client-info h5 {
            font-size: 15px;
            font-weight: 600;
        }

        .client-info p {
            font-size: 13px;
            color: var(--text-light);
        }

        /* Schedule Items */
        .schedule-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .schedule-time {
            min-width: 60px;
            text-align: center;
            font-weight: 700;
            color: var(--primary-color);
        }

        .schedule-content {
            flex: 1;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .schedule-content h5 {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .schedule-content p {
            font-size: 12px;
            color: var(--text-light);
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-light);
        }

        .btn-outline:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .dashboard-layout { grid-template-columns: 1fr; }
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
            <a href="trainer_dashboard.php" class="menu-item active">
                <i class="fas fa-home"></i> Overview
            </a>
            <a href="trainer_clients.php" class="menu-item">
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

        <div class="user-profile-preview">
            <div class="user-avatar-sm"><?php echo $trainerInitials; ?></div>
            <div class="user-info-sm">
                <h4><?php echo htmlspecialchars($trainerName); ?></h4>
                <p>Expert Trainer</p>
            </div>
            <a href="logout.php" style="margin-left: auto; color: var(--text-light);"><i
                    class="fas fa-sign-out-alt fa-flip-horizontal"></i></a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="welcome-banner">
            <div>
                <h2>Hello, Coach <?php echo htmlspecialchars(explode(' ', $trainerName)[0]); ?>! 👋</h2>
                <p>You have 4 training sessions scheduled for today.</p>
            </div>
            <i class="fas fa-dumbbell" style="font-size: 40px; opacity: 0.3;"></i>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?php echo $clientCount; ?></div>
                <div class="stat-label">Active Clients</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fffbeb; color: #f59e0b;"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-value">42</div>
                <div class="stat-label">Total Sessions</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #ecfdf5; color: #10b981;"><i class="fas fa-wallet"></i></div>
                <div class="stat-value">₹<?php echo number_format($revenue); ?></div>
                <div class="stat-label">Monthly Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fdf2f8; color: #ec4899;"><i class="fas fa-star"></i></div>
                <div class="stat-value"><?php echo $rating; ?></div>
                <div class="stat-label">Coach Rating</div>
            </div>
        </div>

        <div class="dashboard-layout">
            <!-- Feed / Schedule -->
            <div class="main-column">
                <div class="section-box">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-calendar-day"></i> Today's Schedule</h3>
                        <a href="trainer_schedule.php" class="btn-action btn-outline" style="text-decoration: none;">View Full Schedule</a>
                    </div>
                    
                    <?php if (empty($dashboardSchedules)): ?>
                        <p style="color: var(--text-light); padding: 20px; text-align: center;">No sessions scheduled for today.</p>
                    <?php else: ?>
                        <?php foreach ($dashboardSchedules as $ds): 
                            $dsTime = date('h:i A', strtotime($ds['session_time']));
                            $borderCol = 'var(--primary-color)';
                            if (strpos(strtolower($ds['session_type']), 'cardio') !== false) $borderCol = 'var(--secondary-color)';
                            if (strpos(strtolower($ds['session_type']), 'consultation') !== false) $borderCol = 'var(--success-color)';
                        ?>
                        <div class="schedule-item">
                            <div class="schedule-time"><?php echo $dsTime; ?></div>
                            <div class="schedule-content" style="border-left-color: <?php echo $borderCol; ?>;">
                                <h5><?php echo htmlspecialchars($ds['session_type']); ?> • <?php echo htmlspecialchars($ds['client_name']); ?></h5>
                                <p><?php echo htmlspecialchars($ds['notes'] ?? 'Status: ' . ucfirst($ds['status'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="section-box">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-plus-circle"></i> Quick Actions</h3>
                    </div>
                    <div style="display: flex; gap: 15px;">
                        <a href="trainer_workouts.php" class="btn-action btn-primary" style="flex: 1; text-align: center; text-decoration: none;"><i class="fas fa-file-medical"></i> Create Workout Plan</a>
                        <a href="trainer_diets.php" class="btn-action btn-primary" style="flex: 1; background: var(--secondary-color); text-align: center; text-decoration: none;"><i class="fas fa-apple-alt"></i> Create Diet Plan</a>
                        <a href="trainer_clients.php" class="btn-action btn-outline" style="flex: 1; text-align: center; text-decoration: none;"><i class="fas fa-user-plus"></i> View Clients</a>
                    </div>
                </div>
            </div>

            <!-- Side Column -->
            <div class="side-column">
                <div class="section-box">
                    <div class="section-header">
                        <h3 class="section-title">New Requests</h3>
                    </div>
                    <?php if (empty($pendingRequests)): ?>
                        <p style="padding: 15px; color: var(--text-light); font-size: 13px; text-align: center;">No new requests.</p>
                    <?php else: ?>
                        <?php foreach ($pendingRequests as $req): 
                            $reqInitials = strtoupper(substr($req['first_name'], 0, 1) . substr($req['last_name'], 0, 1));
                        ?>
                        <div class="client-item" id="req-<?php echo $req['user_id']; ?>">
                            <div class="user-avatar" style="width: 35px; height: 35px; font-size: 12px; background: var(--secondary-color); color: var(--primary-color); display: flex; align-items: center; justify-content: center; border-radius: 50%;"><?php echo $reqInitials; ?></div>
                            <div class="client-info">
                                <h5><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></h5>
                                <p>Wants to hire you</p>
                                <div style="margin-top: 5px;">
                                    <button onclick="handleRequest(<?php echo $req['user_id']; ?>, 'approve')" class="btn-action btn-primary" style="padding: 4px 10px; font-size: 11px;">Approve</button>
                                    <button onclick="handleRequest(<?php echo $req['user_id']; ?>, 'reject')" class="btn-action btn-outline" style="padding: 4px 10px; font-size: 11px; border: 1px solid #ccc;">Reject</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <script>
                        function handleRequest(clientId, action) {
                            if(!confirm('Are you sure you want to ' + action + ' this client?')) return;
                            
                            fetch('trainer_handle_request.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ client_id: clientId, action: action })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    document.getElementById('req-' + clientId).remove();
                                    // Optionally reload to update valid client count
                                    // location.reload(); 
                                } else {
                                    alert('Error: ' + (data.message || 'Unknown error'));
                                }
                            })
                            .catch(err => console.error(err));
                        }
                        </script>
                    <?php endif; ?>
                    <button class="btn-action btn-outline" style="width: 100%; margin-top: 10px;">Review all Requests</button>
                </div>

                <div class="section-box" style="background: #f1f5f9;">
                    <h4 style="margin-bottom: 10px;">Weekly Progress</h4>
                    <p style="font-size: 13px; color: var(--text-light); margin-bottom: 15px;">You've completed 85% of your scheduled sessions this week.</p>
                    <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="width: 85%; height: 100%; background: var(--success-color);"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
