<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require "db_connect.php";
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

// Ensure ENUM supports new statuses
$conn->query("ALTER TABLE users MODIFY COLUMN assignment_status ENUM('none', 'pending', 'approved', 'rejected', 'trainer_invite', 'looking_for_trainer') DEFAULT 'none'");

// Table for Trainer Applications (Interest)
$conn->query("CREATE TABLE IF NOT EXISTS trainer_applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    trainer_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_app (client_id, trainer_id)
)");

// Table for Detailed Requests (Goal & Preferences)
$conn->query("CREATE TABLE IF NOT EXISTS client_trainer_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    goal VARCHAR(100),
    training_style VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE
)");

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['find_trainer'])) {
        $goal = $_POST['goal'] ?? 'General Fitness';
        $style = $_POST['style'] ?? 'Any';
        $notes = $_POST['notes'] ?? '';

        // Step 1: Save Request Details
        $stmt = $conn->prepare("INSERT INTO client_trainer_requests (client_id, goal, training_style, notes) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $userId, $goal, $style, $notes);
        $stmt->execute();

        // Step 2: Set Client Status to 'looking_for_trainer'
        $conn->query("UPDATE users SET assignment_status = 'looking_for_trainer' WHERE user_id = $userId");
        header("Location: liteuser_dashboard.php"); exit();
    }

    if (isset($_POST['cancel_request'])) {
        $conn->query("UPDATE users SET assignment_status = 'none' WHERE user_id = $userId");
        $conn->query("DELETE FROM client_trainer_requests WHERE client_id = $userId");
        header("Location: liteuser_dashboard.php"); exit();
    }
    
    // Accept/Decline Invite
    // Accept/Decline Invite
    if (isset($_POST['accept_invite'])) {
        $conn->query("UPDATE users SET assignment_status = 'approved' WHERE user_id = $userId");
        // Notify Trainer
        $chk = $conn->query("SELECT assigned_trainer_id FROM users WHERE user_id = $userId")->fetch_assoc();
        if ($chk && $chk['assigned_trainer_id']) {
            $tid = $chk['assigned_trainer_id'];
            $uName = $_SESSION['user_name'] ?? 'Lite User';
            $conn->query("INSERT INTO user_notifications (user_id, notification_type, message) VALUES ($tid, 'client_accepted', '$uName has accepted your request.')");
        }
        header("Location: liteuser_dashboard.php"); exit();
    }
    if (isset($_POST['decline_invite'])) {
        $conn->query("UPDATE users SET assignment_status = 'none', assigned_trainer_id = NULL WHERE user_id = $userId");
        header("Location: liteuser_dashboard.php"); exit();
    }
}

// Sync Role - This dashboard serves 'lite' users
$userId = $_SESSION['user_id'];
$checkRole = $conn->query("SELECT role FROM users WHERE user_id = $userId")->fetch_assoc();
$realRole = $checkRole['role'] ?? 'free'; // Default to free if unknown
$_SESSION['user_role'] = $realRole;

if ($realRole !== 'lite') {
    if ($realRole === 'free') { header("Location: freeuser_dashboard.php"); exit(); }
    if ($realRole === 'pro') { header("Location: prouser_dashboard.php"); exit(); }
    if ($realRole === 'trainer') { header("Location: trainer_dashboard.php"); exit(); }
    if ($realRole === 'admin') { header("Location: admin_dashboard.php"); exit(); }
    header('Location: login.php');
    exit();
}

// Ensure table exists (Self-healing)
$conn->query("CREATE TABLE IF NOT EXISTS user_notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type VARCHAR(50),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)");

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Lite User';
$initials = strtoupper(substr($userName, 0, 1) . substr($userName, strpos($userName, ' ') + 1, 1));
if (strlen($initials) < 2) $initials = strtoupper(substr($userName, 0, 2));

// Fetch profile info for personalized stats
$profileSql = "SELECT * FROM client_profiles WHERE user_id = ?";
$stmt = $conn->prepare($profileSql);
$stmt->bind_param("i", $userId);
if ($stmt->execute()) {
    $profile = $stmt->get_result()->fetch_assoc();
}
$stmt->close();



// Fetch Trainer Assignment Status
$trainerStatusSql = "SELECT u.assigned_trainer_id, u.assignment_status, t.first_name as trainer_name, t.last_name as trainer_last 
                     FROM users u 
                     LEFT JOIN users t ON u.assigned_trainer_id = t.user_id 
                     WHERE u.user_id = ?";
$stmt = $conn->prepare($trainerStatusSql);
$currentTrainerName = 'Unknown Trainer';
$currentAssignmentStatus = 'none';
$assignedTrainerId = 0;

if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $currentAssignmentStatus = $res['assignment_status'];
        if ($res['assigned_trainer_id']) {
            $currentTrainerName = $res['trainer_name'] . ' ' . $res['trainer_last'];
            $assignedTrainerId = $res['assigned_trainer_id'];
        }
    }
    $stmt->close();
}

// Fetch all notifications for this user
$notifSql = "SELECT * FROM user_notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$stmt = $conn->prepare($notifSql);
$notifications = [];
$unreadCount = 0;

if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
        if (!$row['is_read']) {
            $unreadCount++;
        }
    }
    $stmt->close();
}

$notificationCount = $unreadCount;

// Fetch Gym Membership Status
$gymSql = "SELECT gym_membership_status FROM users WHERE user_id = ?";
$gStmt = $conn->prepare($gymSql);
$gStmt->bind_param("i", $userId);
$gStmt->execute();
$gymResult = $gStmt->get_result()->fetch_assoc();
$gymStatus = $gymResult ? $gymResult['gym_membership_status'] : 'inactive';
$gStmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lite Dashboard - FitNova</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Lite/Standard Color Palette */
            --primary-color: #0F2C59;
            --secondary-color: #8D99AE; /* More muted for Lite */
            --accent-color: #E63946;
            --bg-color: #F8F9FA;
            --sidebar-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6C757D;
            --border-color: #E9ECEF;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --border-radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
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
            line-height: 1.6;
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
            z-index: 100;
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

        .sidebar-menu {
            padding: 20px 0;
            flex: 1;
            overflow-y: auto;
        }

        .menu-item {
            display: block;
            padding: 12px 30px;
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid transparent;
        }

        .menu-item:hover {
            color: var(--primary-color);
            background-color: rgba(15, 44, 89, 0.05);
        }

        .menu-item.active {
            color: var(--primary-color);
            background-color: rgba(15, 44, 89, 0.05);
            border-left-color: var(--primary-color);
        }

        .menu-item i { width: 20px; text-align: center; }

        /* User Profile Preview */
        .user-profile-preview {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
        }
        
        .user-avatar-sm {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--primary-color); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px 40px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .welcome-text h1 { font-family: 'Outfit', sans-serif; font-size: 28px; color: var(--primary-color); }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            border-bottom: 3px solid transparent;
        }

        .stat-card.blue { border-bottom-color: var(--primary-color); }
        .stat-card.gold { border-bottom-color: var(--secondary-color); }
        .stat-card.green { border-bottom-color: var(--success-color); }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
            font-family: 'Outfit', sans-serif;
            margin-top: 10px;
        }

        .dashboard-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .section-card {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .section-title {
            font-family: 'Outfit', sans-serif;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 992px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .dashboard-layout { grid-template-columns: 1fr; }
        }

        /* Neat Workout List */
        .workout-list-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 12px;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }
        .workout-list-item:hover {
            background: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-color: #eee;
            transform: translateY(-2px);
        }
        .wi-icon {
            width: 45px; height: 45px;
            background: white;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary-color);
            font-size: 18px;
            margin-right: 15px;
            border: 1px solid #eee;
        }
        .wi-content { flex: 1; }
        .wi-content h4 { margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--text-color); }
        .wi-content span { font-size: 0.8rem; color: var(--text-light); display: block; margin-top: 2px; }
        .wi-btn {
            text-decoration: none;
            color: var(--primary-color);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 6px 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #eee;
            transition: 0.2s;
        }
        .wi-btn:hover { background: var(--primary-color); color: white; border-color: var(--primary-color); }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="home.php" class="brand-logo">
                <i class="fas fa-dumbbell"></i> FitNova <span
                    style="font-size: 10px; background: #e9ecef; color: var(--text-color); padding: 2px 5px; border-radius: 4px; margin-left: 5px;">LITE</span>
            </a>
        </div>

        <nav class="sidebar-menu">
            <a href="liteuser_dashboard.php" class="menu-item active">
                <i class="fas fa-home"></i> Lite Dashboard
            </a>
            <a href="view_my_workout.php" class="menu-item">
                <i class="fas fa-dumbbell"></i> Workout Plans
            </a>
            <a href="healthy_recipes.php" class="menu-item">
                <i class="fas fa-utensils"></i> Recipes
            </a>
            <a href="my_progress.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Basic Progress
            </a>
             <a href="client_profile_setup.php" class="menu-item">
                <i class="fas fa-user-circle"></i> Profile
            </a>
            <a href="fitshop.php" class="menu-item">
                <i class="fas fa-store"></i> FitShop
            </a>
             <a href="subscription_plans.php" class="menu-item" style="color: var(--accent-color);">
                <i class="fas fa-gem"></i> Upgrade to Pro
            </a>
        </nav>

        <div class="user-profile-preview">
            <div class="user-avatar-sm"><?php echo $initials; ?></div>
            <div class="user-info-sm">
                <h4 style="margin:0; font-size:14px;"><?php echo htmlspecialchars($userName); ?></h4>
                <p style="margin:0; font-size:12px; color: #6c757d;">Lite Member</p>
            </div>
            <a href="logout.php" style="margin-left: auto; color: var(--text-light);"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="dashboard-header">
            <div class="welcome-text">
                <h1>Welcome, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?>!</h1>
                <p style="color: var(--text-light);">Track your workouts and stay fit with Lite.</p>
            </div>
            <div>
                <?php if ($currentAssignmentStatus === 'none'): ?>
                     <button type="button" onclick="document.getElementById('requestModal').style.display='block'" class="btn-primary" style="box-shadow: 0 4px 15px rgba(15, 44, 89, 0.2); border:none; cursor:pointer;"><i class="fas fa-search" style="margin-right:8px;"></i> Find My Trainer</button>
                <?php elseif ($currentAssignmentStatus === 'looking_for_trainer'): ?>
                     <div style="background: white; padding: 15px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px dashed var(--secondary-color); text-align:center;">
                         <div style="color: var(--primary-color); font-weight:700; margin-bottom:5px;"><i class="fas fa-satellite-dish"></i> Request Pending</div>
                         <div style="font-size: 13px; color: var(--text-light);">Your request has been sent to the Admin. Please wait for a match.</div>
                         <form method="POST" style="margin-top:5px;">
                             <button type="submit" name="cancel_request" style="font-size:11px; color:#ef4444; background:none; border:none; text-decoration:underline; cursor:pointer;">Cancel Request</button>
                         </form>
                     </div>
                <?php elseif ($currentAssignmentStatus === 'trainer_invite'): ?>
                    <div style="background: white; padding: 10px 20px; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 15px; border: 1px solid var(--primary-color);">
                         <div>
                             <div style="font-weight: 700; color: var(--primary-color); font-size: 14px;">Coach <?php echo htmlspecialchars($currentTrainerName); ?></div>
                             <div style="font-size: 12px; color: var(--text-light);">Sent you a request</div>
                         </div>
                         <div style="display:flex; gap:5px;">
                             <a href="messages.php?trainer=<?php echo $assignedTrainerId; ?>" style="background: var(--primary-color); color:white; padding:6px 15px; border-radius:20px; text-decoration:none; font-size:12px;">Chat</a>
                             <form method="POST" style="display:flex; gap:5px;">
                                 <button type="submit" name="accept_invite" style="background: var(--success-color); color:white; border:none; padding:6px 15px; border-radius:20px; font-weight:600; cursor:pointer; font-size:12px;">Accept</button>
                                 <button type="submit" name="decline_invite" style="background: #ef4444; color:white; border:none; padding:6px 15px; border-radius:20px; font-weight:600; cursor:pointer; font-size:12px;">Decline</button>
                             </form>
                         </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div><i class="fas fa-fire" style="color: var(--primary-color);"></i> Calories Burned</div>
                <div class="stat-value">0</div>
            </div>
            <div class="stat-card gold">
                <div><i class="fas fa-dumbbell" style="color: #8D99AE;"></i> Current Weight</div>
                <div class="stat-value"><?php echo $profile['weight_kg'] ?? '0'; ?>kg</div>
            </div>
            <div class="stat-card green">
                <?php
                // Fetch assigned workout plans count
                $wStmt = $conn->prepare("SELECT COUNT(*) as count FROM trainer_workouts WHERE user_id = ?");
                $wStmt->bind_param("i", $userId);
                $wStmt->execute();
                $wCount = $wStmt->get_result()->fetch_assoc()['count'];
                $wStmt->close();
                ?>
                <div><i class="fas fa-clipboard-list" style="color: var(--success-color);"></i> Workout Plans</div>
                <div class="stat-value"><?php echo $wCount; ?></div>
            </div>
        </div>

        <!-- Offline Gym Section -->
        <div id="offline-gym-section" class="section-card" style="margin-top: 30px; border-left: 5px solid var(--accent-color);">
            <div class="section-header">
                <h3 class="section-title"><i class="fas fa-building" style="margin-right: 10px;"></i>Offline Gym Access</h3>
                <?php if ($gymStatus === 'active'): ?>
                    <span class="trainer-status" style="background: rgba(40, 167, 69, 0.1); color: var(--success-color);">Active Member</span>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 300px;">
                    <?php if ($gymStatus === 'active'): ?>
                        <h4 style="font-size: 1.2rem; color: var(--primary-color); margin-bottom: 15px;">Your Access Pass</h4>
                        <p style="color: var(--text-light); margin-bottom: 20px;">
                            Show this QR code at the reception desk to check in.
                        </p>
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=FitNova-User-<?php echo $userId; ?>-Access" alt="Gym Access QR" style="border-radius: 10px; border: 1px solid #eee;">
                            <div>
                                <ul style="list-style: none; color: var(--text-light); font-size: 0.9rem;">
                                    <li style="margin-bottom: 8px;"><i class="fas fa-check-circle" style="color: var(--success-color); margin-right: 8px;"></i> Unlimited Gym Floor Access</li>
                                    <li style="margin-bottom: 8px;"><i class="fas fa-check-circle" style="color: var(--success-color); margin-right: 8px;"></i> Locker Room & Showers</li>
                                    <li style="margin-bottom: 8px;"><i class="fas fa-check-circle" style="color: var(--success-color); margin-right: 8px;"></i> Group Classes Included</li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <h4 style="font-size: 1.2rem; color: var(--primary-color); margin-bottom: 15px;">Take Your Training to the Next Level</h4>
                        <p style="color: var(--text-light); margin-bottom: 20px; line-height: 1.6;">
                            Combine your digital plan with physical gym access. Get full access to all FitNova locations, premium equipment, and on-site trainers.
                        </p>
                        <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                            <div style="display: flex; align-items: center; gap: 10px; color: var(--text-color); font-weight: 500;">
                                <i class="fas fa-map-marker-alt" style="color: var(--accent-color);"></i> All Locations
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; color: var(--text-color); font-weight: 500;">
                                <i class="fas fa-clock" style="color: var(--accent-color);"></i> 24/7 Access
                            </div>
                        </div>
                        <button onclick="subscribeGym()" class="btn-primary" style="background: var(--accent-color); cursor:pointer;">
                            Book now with extra payment ₹10
                        </button>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; min-width: 300px;">
                    <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Gym Interior" style="width: 100%; border-radius: 12px; height: 250px; object-fit: cover;">
                </div>
            </div>
        </div>

        <div class="dashboard-layout">
            <!-- Left Column -->
            <div class="col-left">
                <!-- Standard Plans Intro -->
                <div class="section-card">
                    <h3 class="section-title">Your Lite Plan</h3>
                    <p style="color: var(--text-light); margin-bottom: 15px;">
                        As a Lite member, you have access to our library of advanced workout plans and diet guides. 
                        Upgrade to <strong>Pro</strong> to get a dedicated Personal Trainer.
                    </p>
                    <div style="display:flex; gap:10px;">
                        <a href="view_my_workout.php" style="flex:1; background:var(--bg-color); color:var(--primary-color); padding: 10px; text-align:center; border-radius:8px; text-decoration:none; font-weight:600;">Browse Plans</a>
                        <a href="healthy_recipes.php" style="flex:1; background:var(--bg-color); color:var(--primary-color); padding: 10px; text-align:center; border-radius:8px; text-decoration:none; font-weight:600;">View Recipes</a>
                    </div>
                </div>


            </div>

            <!-- Right Column -->
            <div class="col-right">
                <div class="section-card">
                    <h3 class="section-title">Upgrade to Pro</h3>
                    <p style="font-size: 14px; color: var(--text-light); margin-bottom: 15px;">
                        Unlock advanced analytics, macro calculator, live sessions, and more with FitNova Pro.
                    </p>
                    <a href="subscription_plans.php" style="display: block; width: 100%; text-align: center; background: var(--accent-color); color: white; padding: 10px; border-radius: 8px; text-decoration: none; font-weight: 600;">Upgrade Now</a>
                </div>
            </div>
        </div>
    </main>
<!-- Request Modal -->
<div id="requestModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
    <div style="background:white; width:90%; max-width:500px; margin:10% auto; padding:25px; border-radius:15px; position:relative;">
        <span onclick="document.getElementById('requestModal').style.display='none'" style="position:absolute; right:20px; top:15px; cursor:pointer; font-size:24px;">&times;</span>
        <h3 style="color:var(--primary-color); margin-bottom:20px;">Trainer Preferences</h3>
        <form method="POST">
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Main Fitness Goal</label>
                <select name="goal" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                    <option value="Weight Loss">Weight Loss</option>
                    <option value="Muscle Building">Muscle Building</option>
                    <option value="Yoga & Flexibility">Yoga & Flexibility</option>
                    <option value="Cardio & Endurance">Cardio & Endurance</option>
                    <option value="Other">Other / General</option>
                </select>
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Preferred Style</label>
                <select name="style" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                    <option value="Gym Training">Gym / Strength</option>
                    <option value="HIIT">HIIT / Circuit</option>
                    <option value="Yoga">Yoga / Pilates</option>
                    <option value="Running">Running / Athletics</option>
                    <option value="No Preference">No Preference</option>
                </select>
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Any specific notes?</label>
                <textarea name="notes" rows="3" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;" placeholder="e.g. I have a back injury..."></textarea>
            </div>
            <button type="submit" name="find_trainer" class="btn-primary" style="width:100%;">Submit Request</button>
        </form>
    </div>
</div>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        function subscribeGym() {
            var options = {
                "key": "rzp_test_S9XwIrDZ3gAbfv",
                "amount": 1000,
                "currency": "INR",
                "name": "FitNova Gym Access",
                "description": "Offline Gym Access Subscription",
                "image": "https://via.placeholder.com/100x100.png?text=FitNova",
                "handler": function (response) {
                    fetch('subscribe_offline_gym.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            action: 'subscribe',
                            payment_id: response.razorpay_payment_id 
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Payment Successful! Your gym access is now active.');
                            location.reload();
                        } else {
                            alert('Error activating subscription: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Payment received but error activating subscription.');
                    });
                },
                "prefill": {
                    "name": "<?php echo htmlspecialchars($userName); ?>",
                    "email": "",
                    "contact": ""
                },
                "theme": {
                    "color": "#0F2C59"
                }
            };
            
            var rzp1 = new Razorpay(options);
            rzp1.on('payment.failed', function (response){
                alert('Payment Failed: ' + response.error.description);
            });
            rzp1.open();
        }
    </script>
</body>
</html>
