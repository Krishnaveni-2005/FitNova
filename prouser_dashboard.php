<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require "db_connect.php";
require_once "gamification_helper.php"; // Gamification Helper

// Sync Role - This dashboard serves 'pro' users now (restored to original dashboard)
$userId = $_SESSION['user_id'];

// Initialize Gamification
checkAndAwardBadges($userId);

// Fetch Recent Activity Logs
$logSql = "SELECT * FROM user_activity_logs WHERE user_id = ? ORDER BY log_date DESC, created_at DESC LIMIT 5";
$lStmt = $conn->prepare($logSql);
$lStmt->bind_param("i", $userId);
$lStmt->execute();
$recentLogs = $lStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$lStmt->close();
$userStats = getUserStats($userId);
$userBadges = getUserBadges($userId);

$checkRole = $conn->query("SELECT role FROM users WHERE user_id = $userId")->fetch_assoc();
$realRole = $checkRole['role'] ?? 'free';
$_SESSION['user_role'] = $realRole;

if ($realRole !== 'pro') {
    if ($realRole === 'free') { header("Location: freeuser_dashboard.php"); exit(); }
    if ($realRole === 'lite') { header("Location: liteuser_dashboard.php"); exit(); } 
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
// Fetch Assigned Workouts
$workoutSql = "SELECT * FROM trainer_workouts WHERE user_id = ? ORDER BY created_at DESC LIMIT 3";
$stmt = $conn->prepare($workoutSql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $assignedWorkouts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $assignedWorkouts = []; // Fallback
}

// Fetch Assigned Diets
// Fetch Assigned Diets
$dietSql = "SELECT * FROM trainer_diet_plans WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($dietSql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
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

// Fetch Session Requests
$sessionReqSql = "SELECT sr.request_id, sr.status, sr.created_at, t.first_name, t.last_name 
                  FROM session_requests sr 
                  JOIN users t ON sr.trainer_id = t.user_id 
                  WHERE sr.user_id = ? 
                  ORDER BY sr.created_at DESC";
$stmt = $conn->prepare($sessionReqSql);
$mySessionRequests = [];
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $mySessionRequests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch User Full Name for Schedule Matching
$uFnStmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
$uFnStmt->bind_param("i", $userId);
$uFnStmt->execute();
$uFnRes = $uFnStmt->get_result()->fetch_assoc();
$dbFullName = $uFnRes['first_name'] . ' ' . $uFnRes['last_name'];
$firstNameOnly = explode(' ', trim($uFnRes['first_name']))[0];
$uFnStmt->close();
// Fetch User Orders
$myOrders = [];
$orderSql = "SELECT o.*, 
            (SELECT COUNT(*) FROM shop_order_items WHERE order_id = o.order_id) as item_count
            FROM shop_orders o
            WHERE o.user_id = ?
            ORDER BY o.order_date DESC LIMIT 5";
$stmt = $conn->prepare($orderSql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $myOrders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
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
            background: conic-gradient(var(--success-color) 0%, #f0f0f0 0);
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

        /* Order Tracker Styles */
        .track-container {
            margin: 30px 0;
            position: relative;
        }
        .validation-step-list {
            display: flex;
            justify-content: space-between;
            position: relative;
            list-style: none;
        }
        .validation-step-list::before {
            content: "";
            position: absolute;
            top: 20px;
            left: 0;
            width: 100%;
            height: 4px;
            background: #e2e8f0;
            z-index: 1;
        }
        .validation-step {
            position: relative;
            z-index: 2;
            text-align: center;
            width: 33.33%;
        }
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #cbd5e1;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            border: 4px solid #fff;
            transition: all 0.3s;
        }
        .step-label {
            margin-top: 10px;
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Active/Completed States */
        .step-active .step-icon {
            background: #f59e0b;
            box-shadow: 0 0 0 2px #fef3c7;
        }
        .step-completed .step-icon {
            background: #10b981;
        }
        
        .progress-bar-track {
            position: absolute;
            top: 20px;
            left: 0;
            height: 4px;
            background: linear-gradient(to right, #10b981, #f59e0b);
            z-index: 1;
            width: 0%;
            transition: width 0.3s;
        }
    </style>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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
            <a href="view_my_workout.php" class="menu-item">
                <i class="fas fa-dumbbell"></i> Workout Details
            </a>
            <a href="healthy_recipes.php" class="menu-item">
                <i class="fas fa-utensils"></i> Healthy Recipes
            </a>
            <a href="my_progress.php" class="menu-item">
                <i class="fas fa-chart-line"></i> My Progress
            </a>
            <a href="my_badges.php" class="menu-item">
                <i class="fas fa-medal"></i> My Badges
            </a>
            <a href="my_trainers.php" class="menu-item">
                <i class="fas fa-users"></i> Trainers
            </a>
            <a href="messages.php" class="menu-item">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="client_profile_setup.php" class="menu-item">
                <i class="fas fa-user-circle"></i> Profile
            </a>
            <a href="macro_calculator.php" class="menu-item">
                <i class="fas fa-calculator"></i> Macro Calculator
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
                    <?php if ($currentAssignmentStatus === 'approved'): ?>
                    <span class="trainer-status"><i class="fas fa-circle" style="font-size: 8px;"></i> Coach <?php echo htmlspecialchars($currentTrainerName); ?> is
                        online</span>
                    <?php endif; ?>
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
                
                
                <a href="trainers.php" class="btn-primary">
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
                <div class="stat-value" id="dashCalories"><?php echo number_format($userStats['total_calories'] ?? 0); ?></div>
                <div class="stat-label">Calories Burned (Total)</div>
            </div>
            <div class="stat-card gold">
                <div class="stat-icon"><i class="fas fa-dumbbell"></i></div>
                <div class="stat-value" id="dashWeight"><?php echo $profile['weight_kg'] ?? '0'; ?>kg</div>
                <div class="stat-label">Current Weight</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                <div class="stat-value" id="dashWorkouts"><?php echo number_format($userStats['completed_workouts'] ?? 0); ?></div>
                <div class="stat-label">Workouts Logged</div>
            </div>
        </div>

        <!-- Gamification Section -->
        <div class="section-card" style="margin-bottom: 30px; background: linear-gradient(to right, #ffffff, #f8f9fa);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 class="section-title" style="margin: 0;"><i class="fas fa-medal" style="color: #f1c40f; margin-right: 10px;"></i>My Achievements</h3>
                <a href="my_badges.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 5px; background: white; padding: 6px 15px; border-radius: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: all 0.2s ease;">
                    View All <i class="fas fa-chevron-right" style="font-size: 0.8em;"></i>
                </a>
            </div>
            
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <!-- Streak Box -->
                <div style="flex: 0 0 150px; background: #fff3cd; border: 1px solid #ffeeba; padding: 15px; border-radius: 12px; text-align: center;">
                    <i class="fas fa-fire fa-2x" style="color: #ffc107; margin-bottom: 10px;"></i>
                    <h4 style="font-size: 24px; margin: 0; color: #856404;"><?php echo $userStats['current_streak'] ?? 0; ?></h4>
                    <p style="font-size: 12px; margin: 0; color: #856404;">Day Streak</p>
                </div>

                <!-- Points Box -->
                <div style="flex: 0 0 150px; background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 12px; text-align: center;">
                    <i class="fas fa-star fa-2x" style="color: #17a2b8; margin-bottom: 10px;"></i>
                    <h4 style="font-size: 24px; margin: 0; color: #0c5460;"><?php echo $userStats['total_points'] ?? 0; ?></h4>
                    <p style="font-size: 12px; margin: 0; color: #0c5460;">Total Points</p>
                </div>

                <!-- Badges List -->
                <div style="flex: 1; display: flex; gap: 15px; overflow-x: auto; padding-bottom: 5px; align-items: center;">
                    <?php if (empty($userBadges)): ?>
                        <div style="color: var(--text-light); font-size: 14px; font-style: italic;">
                            No badges yet. Start your journey today!
                        </div>
                    <?php else: ?>
                        <?php foreach ($userBadges as $badge): ?>
                            <div class="badge-item" title="<?php echo htmlspecialchars($badge['description']); ?>" style="text-align: center; min-width: 80px;">
                                <div style="width: 50px; height: 50px; background: <?php echo $badge['color']; ?>; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 5px; box-shadow: 0 3px 6px rgba(0,0,0,0.1);">
                                    <i class="<?php echo $badge['icon_class']; ?>"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; color: var(--text-color); display: block; line-height: 1.2;"><?php echo htmlspecialchars($badge['name']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <span style="font-size: 0.8rem; color: var(--text-light);">Status:</span>
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

                <!-- Session Requests Section -->
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">My Session Requests</h3>
                    </div>
                    <div class="workout-list">
                        <?php if (empty($mySessionRequests)): ?>
                            <p style="color: var(--text-light); padding: 20px; text-align: center;">No session requests found.</p>
                        <?php else: ?>
                            <?php foreach($mySessionRequests as $req): 
                                $statusColor = 'var(--text-light)';
                                $statusIcon = 'fa-circle';
                                if ($req['status'] === 'approved') { $statusColor = 'var(--success-color)'; $statusIcon = 'fa-check-circle'; }
                                if ($req['status'] === 'rejected') { $statusColor = 'var(--accent-color)'; $statusIcon = 'fa-times-circle'; }
                                if ($req['status'] === 'pending') { $statusColor = '#f59e0b'; $statusIcon = 'fa-clock'; }
                            ?>
                            <div class="workout-item">
                                <div style="width: 50px; height: 50px; background: <?php echo $statusColor; ?>20; color: <?php echo $statusColor; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-size: 1.2rem;">
                                    <i class="fas <?php echo $statusIcon; ?>"></i>
                                </div>
                                <div class="workout-info">
                                    <h4 class="workout-name">Request to Coach <?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></h4>
                                    <div class="workout-meta">
                                        <span><i class="far fa-calendar-alt"></i> <?php echo date('M d, Y h:i A', strtotime($req['created_at'])); ?></span>
                                        <span style="color: <?php echo $statusColor; ?>; font-weight: 700; text-transform: capitalize;">
                                            <?php echo ucfirst($req['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                // Fetch Upcoming live sessions aimed at this user
                // Robust matching: Exact Full, Exact First, Starts With First, or User Full Starts With Schedule (reverse)
                $scheduleSql = "SELECT ts.*, t.first_name as trainer_first, t.last_name as trainer_last 
                                FROM trainer_schedules ts
                                JOIN users t ON ts.trainer_id = t.user_id
                                WHERE ts.client_name LIKE CONCAT(?, '%')
                                AND ts.session_date >= CURDATE()
                                AND ts.status != 'cancelled'
                                ORDER BY ts.session_date ASC, ts.session_time ASC
                                LIMIT 3";
                $stmt = $conn->prepare($scheduleSql);
                $mySchedules = [];
                if ($stmt) {
                    // Use just the first part of the first name for robust matching (ignoring middle names/spacing issues)
                    $stmt->bind_param("s", $firstNameOnly);
                    $stmt->execute();
                    $mySchedules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();
                }
                ?>

                <!-- Upcoming Sessions Card -->
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Upcoming Live Sessions</h3>
                    </div>
                    <?php if (empty($mySchedules)): ?>
                        <div style="text-align: center; padding: 30px; border: 2px dashed #eee; border-radius: 10px;">
                            <i class="fas fa-calendar-times" style="font-size: 30px; color: #ddd; margin-bottom: 10px;"></i>
                            <p style="color: var(--text-light); font-size: 14px;">No upcoming sessions scheduled.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($mySchedules as $session): 
                            $sDate = date('M d', strtotime($session['session_date']));
                            $dayName = date('D', strtotime($session['session_date']));
                            $sTime = date('h:i A', strtotime($session['session_time']));
                        ?>
                        <div class="workout-item live">
                             <div class="workout-img" style="background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; flex-direction: column; width: 60px; height: 60px;">
                                <span style="font-size: 10px; opacity: 0.8;"><?php echo $dayName; ?></span>
                                <span style="font-size: 16px;"><?php echo date('d', strtotime($session['session_date'])); ?></span>
                             </div>
                             <div class="workout-info">
                                 <h4 class="workout-name">
                                     <?php echo htmlspecialchars($session['session_type']); ?>
                                     <span class="live-tag">UPCOMING</span>
                                 </h4>
                                 <div class="workout-meta">
                                     <span><i class="far fa-clock"></i> <?php echo $sTime; ?></span>
                                     <span><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($session['trainer_first']); ?></span>
                                 </div>
                             </div>
                             <button class="btn-join" onclick='showSessionDetails(<?php echo json_encode($session); ?>)'>Details</button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Your Assigned Plans</h3>

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
                                <a href="view_my_workout.php?plan_id=<?php echo $w['workout_id']; ?>" class="btn-action" style="text-decoration:none;">View</a>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Assigned Diet Plan</h3>

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
                            <a href="view_my_diet.php" class="btn-action" style="text-decoration:none; display:inline-block; text-align:center;">View</a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php 
                // Dynamic Macros Calculation Logic
                $showMacros = false;
                $valP = 0; $valC = 0; $valF = 0;
                $sourceLabel = "";

                // 1. Get Assigned Diet Timestamp
                $assignedDate = 0;
                $assignedPlan = null;
                if (!empty($assignedDiets) && isset($assignedDiets[0]['target_calories']) && $assignedDiets[0]['target_calories'] > 0) {
                    $assignedPlan = $assignedDiets[0];
                    $assignedDate = strtotime($assignedPlan['created_at']);
                }

                // 2. Get Calculated Macros Timestamp
                $calcDate = 0;
                $calcMacros = null;
                $checkCol = $conn->query("SHOW COLUMNS FROM client_profiles LIKE 'custom_macros_json'");
                if ($checkCol && $checkCol->num_rows > 0) {
                     $macroSql = "SELECT custom_macros_json FROM client_profiles WHERE user_id = ?";
                     $mStmt = $conn->prepare($macroSql);
                     $mStmt->bind_param("i", $userId);
                     $mStmt->execute();
                     $mRes = $mStmt->get_result()->fetch_assoc();
                     $mStmt->close();
                     
                     if ($mRes && !empty($mRes['custom_macros_json'])) {
                         $decoded = json_decode($mRes['custom_macros_json'], true);
                         if (isset($decoded['daily_calories']) && $decoded['daily_calories'] > 0) {
                             $calcMacros = $decoded;
                             // Use calculated_at if exists, else default to 1 (older than any real plan unless plan is 0)
                             $calcDate = isset($decoded['calculated_at']) ? strtotime($decoded['calculated_at']) : 1;
                         }
                     }
                }

                // 3. Compare and Select
                // Prefer Calculated if it's NEWER than assigned setup, or if no assigned setup exists
                if ($calcMacros && ($calcDate >= $assignedDate)) {
                     $valP = $calcMacros['protein'];
                     $valC = $calcMacros['carbs'];
                     $valF = $calcMacros['fats'];
                     $showMacros = true;
                     $sourceLabel = "From Calculator";
                } elseif ($assignedPlan) {
                    $calTarget = $assignedPlan['target_calories'];
                    $dType = strtolower($assignedPlan['diet_type'] ?? 'balanced');
                    
                    // Ratio Logic
                    $rP = 0.30; $rC = 0.50; $rF = 0.20;
                    if (strpos($dType, 'keto') !== false) { $rP = 0.25; $rC = 0.05; $rF = 0.70; }
                    elseif (strpos($dType, 'muscle') !== false) { $rP = 0.40; $rC = 0.40; $rF = 0.20; }
                    elseif (strpos($dType, 'weight loss') !== false) { $rP = 0.40; $rC = 0.30; $rF = 0.30; }

                    $valP = round(($calTarget * $rP) / 4);
                    $valC = round(($calTarget * $rC) / 4);
                    $valF = round(($calTarget * $rF) / 9);
                    $showMacros = true;
                    $sourceLabel = "From Trainer Plan";
                }

                if ($showMacros):
                ?>
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Today's Macros</h3>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="font-size:11px; background:#eef2ff; color:#0F2C59; padding:2px 8px; border-radius:10px; font-weight:600;"><?php echo htmlspecialchars($sourceLabel); ?></span>
                            <a href="#" class="view-all">Log Meal</a>
                        </div>
                    </div>
                    <div style="display: flex; gap: 20px; align-items: center; justify-content: space-around;">
                        <div style="text-align: center;">
                            <span
                                style="display: block; font-size: 24px; font-weight: 700; color: var(--primary-color);"><?php echo $valP; ?>g</span>
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
                                style="display: block; font-size: 24px; font-weight: 700; color: var(--accent-color);"><?php echo $valC; ?>g</span>
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
                                style="display: block; font-size: 24px; font-weight: 700; color: var(--secondary-color);"><?php echo $valF; ?>g</span>
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
                <?php endif; ?>
            </div>

            <!-- Right Column: Progress -->
            <div class="col-right">

                <!-- Recent Activity Section -->
                <div class="section-card">
                    <div class="section-header" style="align-items: center; margin-bottom: 20px;">
                        <h3 class="section-title" style="margin:0;">Recent Activity</h3>
                        <a href="my_progress.php" style="background: var(--bg-color); color: var(--primary-color); padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-decoration: none; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">View All <i class="fas fa-arrow-right" style="font-size: 10px; margin-left: 3px;"></i></a>
                    </div>
                    <?php if (empty($recentLogs)): ?>
                        <div style="text-align: center; padding: 30px 20px;">
                            <div style="width: 50px; height: 50px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: #94a3b8;">
                                <i class="fas fa-walking" style="font-size: 20px;"></i>
                            </div>
                            <p style="color: var(--text-light); font-size: 0.9rem; margin: 0;">No activities logged yet.</p>
                            <a href="my_progress.php" style="display: inline-block; margin-top: 10px; font-size: 0.85rem; color: var(--accent-color); font-weight: 600; text-decoration: none;">Log your first workout</a>
                        </div>
                    <?php else: ?>
                        <div class="activity-list">
                        <?php foreach($recentLogs as $log): 
                            $icon = 'fa-dumbbell';
                            $act = strtolower($log['activity_type']);
                            if (strpos($act, 'run') !== false) $icon = 'fa-running';
                            elseif (strpos($act, 'walk') !== false) $icon = 'fa-walking';
                            elseif (strpos($act, 'cycle') !== false) $icon = 'fa-bicycle';
                            elseif (strpos($act, 'swim') !== false) $icon = 'fa-swimmer';
                            elseif (strpos($act, 'yoga') !== false) $icon = 'fa-spa';
                        ?>
                        <div style="display: flex; align-items: center; padding: 12px; margin-bottom: 10px; background: #f8f9fa; border-radius: 12px; transition: 0.2s; border: 1px solid transparent;" onmouseover="this.style.background='white'; this.style.borderColor='#eee'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.05)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#f8f9fa'; this.style.borderColor='transparent'; this.style.boxShadow='none'; this.style.transform='none';">
                            <div style="width: 45px; height: 45px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: var(--primary-color); font-size: 1.2rem; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--text-color);"><?php echo htmlspecialchars(ucfirst($log['activity_type'])); ?></h4>
                                <div style="font-size: 0.8rem; color: #888; margin-top: 3px; display: flex; align-items: center; gap: 5px;">
                                    <i class="far fa-clock" style="font-size: 0.75rem;"></i> <?php echo $log['duration_minutes']; ?> min
                                    <span style="font-size: 5px; vertical-align: middle; color: #cbd5e1;">●</span>
                                    <?php echo date('M d', strtotime($log['log_date'])); ?>
                                </div>
                            </div>
                            <span style="font-weight: 700; color: var(--accent-color); font-size: 0.9rem; background: rgba(230, 57, 70, 0.1); padding: 4px 10px; border-radius: 20px;">
                                <?php echo $log['calories_burned']; ?> <span style="font-size: 0.75rem; opacity: 0.8;">kcal</span>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Phase Progress</h3>
                    </div>
                    <div class="progress-container">
                        <div class="progress-circle" id="progressRing">
                            <div class="progress-value" id="progressText">
                                0%
                                <span class="progress-label">On Track</span>
                            </div>
                        </div>
                        <p id="progressQuote" style="color: var(--text-light); font-size: 14px; margin-bottom: 20px; text-align: center;">
                            "Your fitness journey begins today. Let's get started!"</p>

                        <button onclick="window.location.href='my_progress.php'"
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

                <!-- Recent Orders Section -->
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Recent Orders</h3>
                    </div>
                    <?php if(empty($myOrders)): ?>
                        <p style="color: var(--text-light); text-align: center;">No orders placed yet.</p>
                    <?php else: ?>
                        <?php foreach($myOrders as $order): ?>
                            <div class="workout-item">
                                <div style="display: flex; flex-direction: column; align-items: center; margin-right: 15px; width: 60px;">
                                    <div style="background: var(--bg-color); color: var(--primary-color); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <span style="font-size: 10px; color: var(--text-light); margin-top: 4px;">#<?php echo $order['order_id']; ?></span>
                                </div>
                                <div class="workout-info">
                                    <h4 class="workout-name">Order #<?php echo $order['order_id']; ?></h4>
                                    <div class="workout-meta">
                                        <span><?php echo $order['item_count']; ?> Items</span>
                                        <span style="display: inline-block; width: 4px; height: 4px; background: #ccc; border-radius: 50%; margin: 6px 4px;"></span>
                                        <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                        <span style="display: inline-block; width: 4px; height: 4px; background: #ccc; border-radius: 50%; margin: 6px 4px;"></span>
                                        <span style="color: <?php echo $order['order_status']=='Delivered'?'var(--success-color)':'var(--warning-color)'; ?>; font-weight: 600;">
                                            <?php echo htmlspecialchars($order['order_status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <button class="btn-action" onclick="openTrackModal('<?php echo $order['order_status']; ?>', <?php echo $order['order_id']; ?>)">
                                    Track
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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

    <!-- Track Order Modal -->
    <div id="trackOrderModal" class="modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <h3 style="color: var(--primary-color); margin: 0;">Order Tracking</h3>
                <button onclick="closeTrackModal()" style="border: none; background: none; font-size: 20px; color: #999; cursor: pointer;">&times;</button>
            </div>
            
            <div class="track-container">
                <div class="progress-bar-track" id="user-track-line"></div>
                <div class="validation-step-list">
                    <div class="validation-step" id="user-step-placed">
                        <div class="step-icon"><i class="fas fa-shopping-basket"></i></div>
                        <div class="step-label">Placed</div>
                    </div>
                    <div class="validation-step" id="user-step-transit">
                        <div class="step-icon"><i class="fas fa-shipping-fast"></i></div>
                        <div class="step-label">Shipped</div>
                    </div>
                    <div class="validation-step" id="user-step-completed">
                        <div class="step-icon"><i class="fas fa-check"></i></div>
                        <div class="step-label">Delivered</div>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 25px;">
                <p style="color: var(--text-light); font-size: 14px;">Order Status: <strong id="userTrackStatus" style="color: var(--primary-color);"></strong></p>
                <p style="font-size: 13px; color: #999; margin-top: 5px;">Order ID: #<span id="userTrackId"></span></p>
            </div>

            <button onclick="closeTrackModal()" style="width: 100%; padding: 12px; background: var(--primary-color); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 20px;">Close</button>
        </div>
    </div>

    <!-- Session Details Modal -->
    <div id="sessionModal" class="modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <h3 style="color: var(--primary-color); margin: 0;">Session Details</h3>
                <button onclick="closeSessionModal()" style="border: none; background: none; font-size: 20px; color: #999; cursor: pointer;">&times;</button>
            </div>
            
            <div style="margin-bottom: 25px;">
                <div style="display: flex; margin-bottom: 15px; align-items: center;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #777;">SESSION TYPE</span>
                        <h4 style="margin: 0; color: #333;" id="modalSessionType">Workout</h4>
                    </div>
                </div>

                <div style="display: flex; margin-bottom: 15px; align-items: center;">
                     <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--secondary-color); color: white; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #777;">TRAINER</span>
                        <h4 style="margin: 0; color: #333;" id="modalSessionTrainer">Trainer Name</h4>
                    </div>
                </div>

                 <div style="display: flex; margin-bottom: 15px; align-items: center;">
                     <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--accent-color); color: white; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                        <i class="far fa-clock"></i>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #777;">DATE & TIME</span>
                        <h4 style="margin: 0; color: #333;"><span id="modalSessionDate">May 12</span> at <span id="modalSessionTime">10:00 AM</span></h4>
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                     <span style="font-size: 12px; color: #777;">STATUS</span>
                     <p style="margin: 5px 0 0 0; color: var(--success-color); font-weight: 600;" id="modalSessionStatus">UPCOMING</p>
                </div>
            </div>

            <button onclick="closeSessionModal()" style="width: 100%; padding: 12px; background: var(--primary-color); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Close</button>
        </div>
    </div>

    <script>
        function showSessionDetails(session) {
            document.getElementById('modalSessionType').innerText = session.session_type;
            document.getElementById('modalSessionTrainer').innerText = session.trainer_first + ' ' + session.trainer_last;
            
            const d = new Date(session.session_date);
            const dateStr = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            document.getElementById('modalSessionDate').innerText = dateStr;
            
            // Format time manually or simplistic approach since we have time string
            // Assuming session.session_time is HH:mm:ss
            let timeParts = session.session_time.split(':');
            let h = parseInt(timeParts[0]);
            let m = timeParts[1];
            let ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            h = h ? h : 12; // the hour '0' should be '12'
            let timeStr = h + ':' + m + ' ' + ampm;
            
            document.getElementById('modalSessionTime').innerText = timeStr;
            document.getElementById('modalSessionStatus').innerText = session.status.toUpperCase();
            
            const modal = document.getElementById('sessionModal');
            modal.style.display = 'flex';
        }

        function closeSessionModal() {
            document.getElementById('sessionModal').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            console.log('Pro Dashboard Loaded');
            
            // User Data Key Prefix
            const USER_ID = "<?php echo $userId; ?>";
            const S_KEY = (key) => `${key}_${USER_ID}`;

            // Sync Stats from LocalStorage
            const savedCalories = localStorage.getItem(S_KEY('fitnova_calories'));
            const savedWorkouts = localStorage.getItem(S_KEY('fitnova_workouts'));
            const savedWeight = localStorage.getItem(S_KEY('fitnova_weight'));

            if (savedCalories) {
                document.getElementById('dashCalories').innerText = parseInt(savedCalories).toLocaleString();
            }
            if (savedWorkouts) {
                document.getElementById('dashWorkouts').innerText = savedWorkouts;
            }
            if (savedWeight) {
                document.getElementById('dashWeight').innerText = savedWeight + 'kg';
            }

            // Update Phase Progress (Goal: 5000 calories)
            const goal = 5000;
            const currentCal = parseInt(savedCalories) || 0;
            let pct = Math.round((currentCal / goal) * 100);
            if (pct > 100) pct = 100;

            const ring = document.getElementById('progressRing');
            const text = document.getElementById('progressText');
            const quote = document.getElementById('progressQuote');

            if (ring && text && quote) {
                // Update Text
                text.innerHTML = `${pct}% <span class="progress-label">On Track</span>`;
                
                // Update Gradient Ring
                ring.style.background = `conic-gradient(var(--success-color) ${pct}%, #f0f0f0 0)`;

                // Update Quote
                if (pct === 0) quote.innerText = "Your fitness journey begins today. Let's get started!";
                else if (pct < 25) quote.innerText = "Great start! Consistency is key. Keep it up!";
                else if (pct < 50) quote.innerText = "You're making real progress. Keep pushing!";
                else if (pct < 75) quote.innerText = "Over halfway there! You're crushing it.";
                else if (pct < 100) quote.innerText = "So close to your goal! Finish strong.";
                else quote.innerText = "Phase Goal Complete! Amazing work user!";
            }
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

        function openTrackModal(status, orderId) {
            document.getElementById('userTrackId').innerText = orderId;
            document.getElementById('userTrackStatus').innerText = status;
            
            const stepPlaced = document.getElementById('user-step-placed');
            const stepTransit = document.getElementById('user-step-transit');
            const stepCompleted = document.getElementById('user-step-completed');
            const trackLine = document.getElementById('user-track-line');
            const modal = document.getElementById('trackOrderModal');

            // Reset
            [stepPlaced, stepTransit, stepCompleted].forEach(el => {
                el.className = 'validation-step';
                el.querySelector('.step-icon').style.background = '#cbd5e1';
                el.querySelector('.step-icon').style.boxShadow = 'none';
            });
            trackLine.style.background = 'linear-gradient(to right, #10b981, #f59e0b)';
            trackLine.style.width = '0%';

            if (status === 'Cancelled') {
                trackLine.style.width = '0%';
                trackLine.style.background = '#ef4444';
                modal.style.display = 'flex';
                return;
            }

            // Helper
            const setStep = (el, state) => {
                if(state === 'completed') {
                    el.classList.add('step-completed');
                    el.querySelector('.step-icon').style.background = '#10b981';
                } else if(state === 'active') {
                    el.classList.add('step-active');
                    el.querySelector('.step-icon').style.background = '#f59e0b';
                    el.querySelector('.step-icon').style.boxShadow = '0 0 0 3px #fef3c7';
                }
            };

            if (status === 'Placed') {
                setStep(stepPlaced, 'active');
                trackLine.style.width = '10%';
            } else if (status === 'Shipped') {
                setStep(stepPlaced, 'completed');
                setStep(stepTransit, 'active');
                trackLine.style.width = '50%';
            } else if (status === 'Delivered') {
                setStep(stepPlaced, 'completed');
                setStep(stepTransit, 'completed');
                setStep(stepCompleted, 'completed');
                trackLine.style.width = '100%';
            }

            modal.style.display = 'flex';
        }

        function closeTrackModal() {
            document.getElementById('trackOrderModal').style.display = 'none';
        }

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
