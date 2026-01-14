<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pro') {
    header('Location: login.php');
    exit();
}
require "db_connect.php";

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
$userName = $_SESSION['user_name'] ?? 'Pro User';
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

// Fetch Assigned Workouts
$workoutSql = "SELECT * FROM trainer_workouts WHERE client_name = ? ORDER BY created_at DESC LIMIT 3";
$stmt = $conn->prepare($workoutSql);
if ($stmt) {
    $stmt->bind_param("s", $userName);
    $stmt->execute();
    $assignedWorkouts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $assignedWorkouts = []; // Fallback
}

// Fetch Assigned Diets
$dietSql = "SELECT * FROM trainer_diet_plans WHERE client_name = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($dietSql);
if ($stmt) {
    $stmt->bind_param("s", $userName);
    $stmt->execute();
    $assignedDiets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Log error or fallback
    error_log("Diet Prepare Error: " . $conn->error);
    $assignedDiets = [];
}

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


// Sync missing notifications
if ($currentAssignmentStatus === 'pending' && !empty($currentTrainerName)) {
    // Check if we have a notification for this
    $checkSql = "SELECT notification_id FROM user_notifications WHERE user_id = ? AND notification_type = 'trainer_request_pending' LIMIT 1";
    $cStmt = $conn->prepare($checkSql);
    $cStmt->bind_param("i", $userId);
    $cStmt->execute();
    if ($cStmt->get_result()->num_rows === 0) {
        // Missing notification, create it
        $msg = "Request sent to Coach " . $currentTrainerName . ". Approval pending.";
        $insSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, 'trainer_request_pending', ?)";
        $iStmt = $conn->prepare($insSql);
        $iStmt->bind_param("is", $userId, $msg);
        $iStmt->execute();
        $iStmt->close();
    }
    $cStmt->close();
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
    <title>Pro Dashboard - FitNova</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Professional Color Palette */
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
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
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
            transition: var(--transition);
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

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        /* Pro Badge in Sidebar */
        .pro-badge-sidebar {
            background-color: var(--primary-color);
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            margin-left: auto;
            text-transform: uppercase;
        }

        .user-profile-preview {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(to right, rgba(15, 44, 89, 0.03), transparent);
        }

        .user-avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: var(--secondary-color);
            border: 2px solid var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            position: relative;
        }

        .user-avatar-sm::after {
            content: '\f005';
            /* Star icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            bottom: -5px;
            right: -5px;
            background: var(--secondary-color);
            color: var(--primary-color);
            width: 18px;
            height: 18px;
            font-size: 10px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .user-info-sm h4 {
            font-size: 14px;
            color: var(--primary-color);
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .user-info-sm p {
            font-size: 12px;
            color: var(--text-light);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px 40px;
        }

        /* Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .welcome-text h1 {
            font-size: 28px;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .welcome-text p {
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .trainer-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            padding: 2px 8px;
            border-radius: 50px;
            font-weight: 500;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 1px solid var(--border-color);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .btn-icon .badge {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 18px;
            height: 18px;
            background-color: var(--accent-color);
            color: white;
            border-radius: 50%;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .btn-icon:hover {
            background-color: var(--bg-color);
            color: var(--primary-color);
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
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: #0a1f3f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 44, 89, 0.2);
        }

        /* Stats Grid */
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
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid transparent;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .stat-card.blue {
            border-bottom-color: var(--primary-color);
        }

        .stat-card.gold {
            border-bottom-color: var(--secondary-color);
        }

        .stat-card.red {
            border-bottom-color: var(--accent-color);
        }

        .stat-card.green {
            border-bottom-color: var(--success-color);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .blue .stat-icon {
            background: rgba(15, 44, 89, 0.1);
            color: var(--primary-color);
        }

        .gold .stat-icon {
            background: rgba(218, 192, 163, 0.2);
            color: #8F7250;
        }

        .red .stat-icon {
            background: rgba(230, 57, 70, 0.1);
            color: var(--accent-color);
        }

        .green .stat-icon {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
            font-family: 'Outfit', sans-serif;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 14px;
        }

        /* Dashboard Grid */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .section-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            color: var(--primary-color);
        }

        .section-subtitle {
            font-size: 14px;
            color: var(--text-light);
            margin-top: -15px;
            margin-bottom: 20px;
            display: block;
        }

        .view-all {
            color: var(--accent-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        /* Workout List - Pro Style */
        .workout-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
            position: relative;
        }

        .workout-item.live {
            background-color: rgba(230, 57, 70, 0.03);
            padding: 15px;
            margin: 0 -15px;
            border-radius: 8px;
            border-bottom: none;
            margin-bottom: 10px;
        }

        .workout-item:last-child {
            border-bottom: none;
        }

        .workout-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
        }

        .workout-item.live .workout-img {
            border: 2px solid var(--accent-color);
        }

        .workout-info {
            flex: 1;
        }

        .workout-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .live-tag {
            font-size: 10px;
            background-color: var(--accent-color);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            text-transform: uppercase;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 1;
            }
        }

        .workout-meta {
            font-size: 13px;
            color: var(--text-light);
            display: flex;
            gap: 15px;
        }

        .workout-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-action {
            background-color: var(--bg-color);
            color: var(--primary-color);
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-size: 13px;
            transition: var(--transition);
        }

        .btn-action:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-join {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: var(--transition);
            box-shadow: 0 4px 10px rgba(230, 57, 70, 0.3);
        }

        .btn-join:hover {
            background-color: #d62828;
            transform: translateY(-2px);
        }

        /* Trainer Card */
        .trainer-card {
            background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .trainer-img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--secondary-color);
        }

        .trainer-info h4 {
            color: var(--primary-color);
            margin-bottom: 2px;
            font-size: 1rem;
        }

        .trainer-info p {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 8px;
        }

        .btn-chat {
            background: white;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            font-size: 0.8rem;
            padding: 5px 12px;
            border-radius: 50px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .btn-chat:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Progress Circle */
        .progress-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .progress-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: conic-gradient(var(--success-color) 85%, #f0f0f0 0);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            position: relative;
        }

        .progress-circle::before {
            content: '';
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            position: absolute;
        }

        .progress-value {
            position: relative;
            z-index: 1;
            font-size: 32px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            color: var(--text-color);
        }

        .progress-label {
            font-size: 13px;
            color: var(--text-light);
            display: block;
            margin-top: 5px;
            font-weight: 400;
        }

        .daily-goals-list {
            width: 100%;
            margin-top: 20px;
        }

        .goal-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
            color: var(--text-light);
        }

        .goal-item strong {
            color: var(--text-color);
        }

        /* Mobile View */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .dashboard-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="home.php" class="brand-logo">
                <i class="fas fa-dumbbell"></i> FitNova <span
                    style="font-size: 10px; background: var(--secondary-color); color: var(--primary-color); padding: 2px 5px; border-radius: 4px; margin-left: 5px;">PRO</span>
            </a>
        </div>

        <nav class="sidebar-menu">
            <a href="prouser_dashboard.php" class="menu-item active">
                <i class="fas fa-home"></i> Premium Dashboard
            </a>
            <a href="free_workouts.php" class="menu-item">
                <i class="fas fa-dumbbell"></i> Workout Videos
            </a>
            <a href="healthy_recipes.php" class="menu-item">
                <i class="fas fa-utensils"></i> Healthy Recipes
            </a>
            <a href="my_progress.php" class="menu-item">
                <i class="fas fa-chart-line"></i> My Progress
            </a>
            <a href="messages.php" class="menu-item">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="client_profile_setup.php" class="menu-item">
                <i class="fas fa-user-circle"></i> Profile
            </a>
            <a href="fitshop.php" class="menu-item">
                <i class="fas fa-store"></i> FitShop
            </a>
            <a href="#offline-gym-section" class="menu-item" onclick="document.getElementById('offline-gym-section').scrollIntoView({behavior: 'smooth'})">
                <i class="fas fa-building"></i> Offline Gym
            </a>
            <a href="subscription_plans.php" class="menu-item" style="color: var(--accent-color);">
                <i class="fas fa-gem"></i> Upgrade Plan
            </a>
        </nav>

        <div class="user-profile-preview">
            <div class="user-avatar-sm"><?php echo $initials; ?></div>
            <div class="user-info-sm">
                <h4><?php echo htmlspecialchars($userName); ?> <i class="fas fa-check-circle"
                        style="color: var(--secondary-color); font-size: 12px;"></i></h4>
                <p>Pro Member</p>
            </div>
            <a href="logout.php" style="margin-left: auto; color: var(--text-light);"><i
                    class="fas fa-sign-out-alt fa-flip-horizontal"></i></a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="welcome-text">
                <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?>! 👋</h1>
                <p>
                    <span class="trainer-status"><i class="fas fa-circle" style="font-size: 8px;"></i> Coach <?php echo $currentAssignmentStatus === 'approved' ? htmlspecialchars($currentTrainerName) : 'Mike'; ?> is
                        online</span>
                    Ready for your transformation?
                </p>
            </div>
            <div class="header-actions">
                <div style="position: relative;">
                    <button class="btn-icon" id="notificationBtn" onclick="toggleNotifications()">
                        <i class="fas fa-bell"></i>
                        <?php if ($notificationCount > 0): ?>
                            <span class="badge"><?php echo $notificationCount; ?></span>
                        <?php endif; ?>
                    </button>
                    <!-- Notification Dropdown -->
                    <div id="notificationDropdown" class="notification-dropdown">
                        <div class="notification-header">Notifications</div>
                        <div class="notification-body">
                            <?php if (empty($notifications)): ?>
                                <div class="notification-empty">
                                    <p>No notifications yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): 
                                    $timeAgo = '';
                                    $timestamp = strtotime($notif['created_at']);
                                    $diff = time() - $timestamp;
                                    
                                    if ($diff < 60) {
                                        $timeAgo = 'Just now';
                                    } elseif ($diff < 3600) {
                                        $mins = floor($diff / 60);
                                        $timeAgo = $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
                                    } elseif ($diff < 86400) {
                                        $hours = floor($diff / 3600);
                                        $timeAgo = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
                                    } else {
                                        $days = floor($diff / 86400);
                                        $timeAgo = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
                                    }
                                    
                                    $unreadClass = !$notif['is_read'] ? 'unread' : '';
                                ?>
                                <div class="notification-item <?php echo $unreadClass; ?>">
                                    <div class="notif-icon"><i class="fas fa-info-circle"></i></div>
                                    <div class="notif-content">
                                        <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                        <span class="notif-time"><?php echo $timeAgo; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                
                <a href="#" class="btn-primary">
                    <i class="fas fa-calendar-plus"></i> Book Session
                </a>
            </div>
        </header>

        <style>
            .notification-dropdown {
                position: absolute;
                top: 60px;
                right: 0;
                width: 320px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                display: none;
                z-index: 1000;
                border: 1px solid var(--border-color);
                animation: slideDown 0.2s ease;
            }

            .notification-dropdown.active {
                display: block;
            }

            .notification-header {
                padding: 15px 20px;
                font-weight: 700;
                border-bottom: 1px solid var(--border-color);
                color: var(--primary-color);
            }

            .notification-body {
                max-height: 300px;
                overflow-y: auto;
            }

            .notification-item {
                padding: 15px 20px;
                border-bottom: 1px solid var(--border-color);
                display: flex;
                gap: 15px;
                transition: background 0.2s;
            }

            .notification-item:hover {
                background: #f8f9fa;
            }
            
            .notification-item.unread {
                background: rgba(15, 44, 89, 0.03);
            }

            .notif-icon {
                width: 35px;
                height: 35px;
                border-radius: 50%;
                background: #e0e7ff;
                color: var(--primary-color);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .notif-content p {
                font-size: 13px;
                margin-bottom: 4px;
                color: var(--text-color);
                line-height: 1.4;
            }

            .notif-time {
                font-size: 11px;
                color: var(--text-light);
            }

            .notification-empty {
                padding: 30px;
                text-align: center;
                color: var(--text-light);
                font-size: 14px;
            }

            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>

        <script>
            function toggleNotifications() {
                const dropdown = document.getElementById('notificationDropdown');
                const badge = document.querySelector('.btn-icon .badge');
                dropdown.classList.toggle('active');

                // Mark all as read when opened
                if (dropdown.classList.contains('active') && badge) {
                    fetch('mark_notifications_read.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && badge) {
                            badge.style.display = 'none';
                            // Remove unread styling from all items
                            document.querySelectorAll('.notification-item.unread').forEach(item => {
                                item.classList.remove('unread');
                            });
                        }
                    })
                    .catch(err => console.error('Error marking notifications as read:', err));
                }
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const isClickInside = document.getElementById('notificationBtn').contains(event.target) || 
                                      document.getElementById('notificationDropdown').contains(event.target);
                
                if (!isClickInside) {
                    document.getElementById('notificationDropdown').classList.remove('active');
                }
            });
        </script>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fas fa-fire"></i></div>
                <div class="stat-value">2,450</div>
                <div class="stat-label">Calories (Weekly Avg)</div>
            </div>
            <div class="stat-card gold">
                <div class="stat-icon"><i class="fas fa-dumbbell"></i></div>
                <div class="stat-value"><?php echo $profile['weight_kg'] ?? '72'; ?>kg</div>
                <div class="stat-label">Current Weight</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                <div class="stat-value">98%</div>
                <div class="stat-label">Plan Adherence</div>
            </div>
        </div>

        <div class="dashboard-layout">
            <!-- Left Column: Custom Plan -->
            <div class="col-left">
                <!-- My Trainer Section -->
                <?php if ($currentAssignmentStatus === 'approved' && !empty($currentTrainerName)): ?>
                    <div class="trainer-card">
                        <div style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, var(--secondary-color), var(--primary-color)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 24px; border: 3px solid var(--secondary-color);">
                            <?php 
                                $nameParts = explode(' ', $currentTrainerName);
                                echo strtoupper(substr($nameParts[0], 0, 1));
                                if (isset($nameParts[1])) echo strtoupper(substr($nameParts[1], 0, 1));
                            ?>
                        </div>
                        <div class="trainer-info">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                                <div>
                                    <h4>Coach <?php echo htmlspecialchars($currentTrainerName); ?></h4>
                                    <p>Your Personal Trainer</p>
                                    <a href="messages.php?trainer=<?php echo $assignedTrainerId; ?>" class="btn-chat" style="text-decoration:none; display:inline-block; text-align:center;"><i class="far fa-comment-alt"></i> Chat Now</a>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-size: 0.8rem; color: var(--text-light); display: block;">Status:</span>
                                    <span style="font-weight: 600; color: var(--success-color);">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($currentAssignmentStatus === 'pending'): ?>
                    <div class="trainer-card" style="background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);">
                        <div style="width: 70px; height: 70px; border-radius: 50%; background: #ffc107; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="trainer-info">
                            <h4 style="color: #f59e0b;">Trainer Request Pending</h4>
                            <p>Waiting for Coach <?php echo htmlspecialchars($currentTrainerName); ?> to approve your request.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="trainer-card" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">
                        <div style="width: 70px; height: 70px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 24px;">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="trainer-info">
                            <h4 style="color: var(--text-color);">No Trainer Assigned</h4>
                            <p>Select a trainer from our expert team to get personalized guidance.</p>
                            <a href="trainers.php" class="btn-chat" style="background: var(--primary-color); color: white; text-decoration: none;"><i class="fas fa-search"></i> Browse Trainers</a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Your Assigned Plans</h3>
                        <a href="#" class="view-all">View Full Plan</a>
                    </div>

                    <div class="workout-list">
                        <?php if (empty($assignedWorkouts)): ?>
                            <p style="color: var(--text-light); padding: 20px; text-align: center;">No workout routines assigned by your trainer yet.</p>
                        <?php else: ?>
                            <?php foreach($assignedWorkouts as $w): ?>
                            <div class="workout-item">
                                <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80"
                                    alt="Strength" class="workout-img">
                                <div class="workout-info">
                                    <h4 class="workout-name"><?php echo htmlspecialchars($w['plan_name']); ?></h4>
                                    <div class="workout-meta">
                                        <span><i class="far fa-clock"></i> <?php echo $w['duration_weeks']; ?> Weeks</span>
                                        <span><i class="fas fa-layer-group"></i> <?php echo ucfirst($w['difficulty']); ?></span>
                                    </div>
                                </div>
                                <button class="btn-action">Start Log</button>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Assigned Diet Plan</h3>
                        <a href="#" class="view-all">View Details</a>
                    </div>
                    <?php if (empty($assignedDiets)): ?>
                        <p style="color: var(--text-light); padding: 20px; text-align: center;">Your custom diet plan will appear here once assigned.</p>
                    <?php else: 
                        $diet = $assignedDiets[0];
                    ?>
                        <div class="workout-item">
                            <img src="https://images.unsplash.com/photo-1490645935967-10de6ba17061?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80"
                                alt="Meal" class="workout-img">
                            <div class="workout-info">
                                <h4 class="workout-name"><?php echo htmlspecialchars($diet['plan_name']); ?></h4>
                                <div class="workout-meta">
                                    <span><i class="fas fa-utensils"></i> <?php echo $diet['target_calories']; ?> kcal/day</span>
                                    <span><i class="fas fa-bullseye"></i> Type: <?php echo ucfirst($diet['diet_type']); ?></span>
                                </div>
                            </div>
                            <button class="btn-action">View</button>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Today's Macros</h3>
                        <a href="#" class="view-all">Log Meal</a>
                    </div>
                    <div style="display: flex; gap: 20px; align-items: center; justify-content: space-around;">
                        <div style="text-align: center;">
                            <span
                                style="display: block; font-size: 24px; font-weight: 700; color: var(--primary-color);">165g</span>
                            <span style="font-size: 13px; color: var(--text-light);">Protein</span>
                            <div
                                style="width: 60px; height: 4px; background: #e9ecef; margin: 5px auto; border-radius: 2px;">
                                <div
                                    style="width: 80%; height: 100%; background: var(--primary-color); border-radius: 2px;">
                                </div>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <span
                                style="display: block; font-size: 24px; font-weight: 700; color: var(--accent-color);">240g</span>
                            <span style="font-size: 13px; color: var(--text-light);">Carbs</span>
                            <div
                                style="width: 60px; height: 4px; background: #e9ecef; margin: 5px auto; border-radius: 2px;">
                                <div
                                    style="width: 60%; height: 100%; background: var(--accent-color); border-radius: 2px;">
                                </div>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <span
                                style="display: block; font-size: 24px; font-weight: 700; color: var(--secondary-color);">75g</span>
                            <span style="font-size: 13px; color: var(--text-light);">Fats</span>
                            <div
                                style="width: 60px; height: 4px; background: #e9ecef; margin: 5px auto; border-radius: 2px;">
                                <div
                                    style="width: 45%; height: 100%; background: var(--secondary-color); border-radius: 2px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Progress -->
            <div class="col-right">
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Phase Progress</h3>
                    </div>
                    <div class="progress-container">
                        <div class="progress-circle">
                            <div class="progress-value">
                                85%
                                <span class="progress-label">On Track</span>
                            </div>
                        </div>
                        <p style="color: var(--text-light); font-size: 14px; margin-bottom: 20px; text-align: center;">
                            "Great consistent effort this week. Let's push hard on the legs tomorrow." - Coach Mike</p>

                        <button
                            style="width: 100%; padding: 12px; background: var(--bg-color); color: var(--primary-color); border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">View
                            Detailed Analytics</button>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Equipment Store</h3>
                        <a href="#" class="view-all">Shop</a>
                    </div>
                    <div class="workout-item" style="border: none;">
                        <img src="https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80"
                            alt="Supplements" class="workout-img">
                        <div class="workout-info">
                            <h4 class="workout-name">Pro Whey Isolate</h4>
                            <div class="workout-meta">
                                <span style="color: var(--success-color); font-weight: 600;">₹3,450.00</span>
                                <span
                                    style="font-size: 11px; background: var(--secondary-color); color: var(--primary-color); padding: 1px 4px; border-radius: 2px;">PRO
                                    15% OFF</span>
                            </div>
                        </div>
                        <button class="btn-action" onclick="addToCart()"><i class="fas fa-shopping-cart"></i></button>
                    </div>
                </div>
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
                        <button onclick="subscribeGym()" class="btn-primary" style="background: var(--accent-color);">
                            Book now with extra payment ₹10
                        </button>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; min-width: 300px;">
                    <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Gym Interior" style="width: 100%; border-radius: 12px; height: 250px; object-fit: cover;">
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Pro Dashboard Loaded');
        });

        function addToCart() {
            const userId = "<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>";
            const cartKey = userId ? `cart_${userId}` : 'cart_guest';
            
            const product = {
                name: 'Pro Whey Isolate',
                price: 3450,
                image: 'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80',
                size: '1kg',
                quantity: 1
            };
            
            let cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
            
            // Check if product already exists in cart
            const existingIndex = cart.findIndex(item => item.name === product.name && item.size === product.size);
            
            if (existingIndex !== -1) {
                cart[existingIndex].quantity += 1;
                alert('Product quantity updated in cart!');
            } else {
                cart.push(product);
                alert('Product added to cart!');
            }
            
            localStorage.setItem(cartKey, JSON.stringify(cart));
            
            // Trigger cart update in header if available
            if (typeof window.updateCartDisplay === 'function') {
                window.updateCartDisplay();
            }
        }

        function subscribeGym() {
            if (confirm('Confirm subscription to Offline Gym Access for ₹10/month?')) {
                // In a real app, this would redirect to Stripe/Payment Gateway
                // Here we verify update directly
                fetch('subscribe_offline_gym.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'subscribe' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Subscription Successful! Welcome to FitNova Gyms.');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>

</html>
