<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';
$initials = strtoupper(substr($userName, 0, 1) . substr($userName, strpos($userName, ' ') + 1, 1));
if (strlen($initials) < 2) $initials = strtoupper(substr($userName, 0, 2));

require "db_connect.php";
$userId = $_SESSION['user_id'];

// Sync Role
$checkRole = $conn->query("SELECT role FROM users WHERE user_id = $userId")->fetch_assoc();
if ($checkRole && $checkRole['role'] !== 'free') {
    $_SESSION['user_role'] = $checkRole['role'];
    if ($checkRole['role'] === 'lite') { header("Location: liteuser_dashboard.php"); exit(); }
    if ($checkRole['role'] === 'pro') { header("Location: prouser_dashboard.php"); exit(); }
    if ($checkRole['role'] === 'trainer') { header("Location: trainer_dashboard.php"); exit(); }
    if ($checkRole['role'] === 'admin') { header("Location: admin_dashboard.php"); exit(); }
}
$profileCheck = $conn->query("SELECT weight_kg FROM client_profiles WHERE user_id = $userId");
$hasProfile = ($profileCheck->num_rows > 0);
$profileWeight = "0kg"; // default
if ($hasProfile) {
    $pData = $profileCheck->fetch_assoc();
    $profileWeight = $pData['weight_kg'] . "kg";
}

// Fetch Common Resources (added by trainers but not specific to any client)
// Fetch Common Resources (added by trainers but not specific to any client)
$commonWorkoutsSql = "SELECT * FROM trainer_workouts WHERE client_name = 'Personal Template' OR client_name = '' OR client_name IS NULL LIMIT 2";
$cwResult = $conn->query($commonWorkoutsSql);
$commonWorkouts = $cwResult ? $cwResult->fetch_all(MYSQLI_ASSOC) : [];

$commonDietsSql = "SELECT * FROM trainer_diet_plans WHERE client_name = 'Personal Template' OR client_name = '' OR client_name IS NULL LIMIT 1";
$cdResult = $conn->query($commonDietsSql);
$commonDiets = $cdResult ? $cdResult->fetch_all(MYSQLI_ASSOC) : [];

// Fetch User Full Name for Schedule Matching
$uFnStmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
$uFnStmt->bind_param("i", $userId);
$uFnStmt->execute();
$uFnRes = $uFnStmt->get_result()->fetch_assoc();
$dbFullName = $uFnRes['first_name'] . ' ' . $uFnRes['last_name'];
$firstNameOnly = explode(' ', trim($uFnRes['first_name']))[0];
$uFnStmt->close();

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
    <title>User Dashboard - FitNova</title>
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
            border-radius: 50%;
            background-color: var(--secondary-color);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .user-info-sm h4 {
            font-size: 14px;
            color: var(--primary-color);
            margin-bottom: 2px;
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

        .view-all {
            color: var(--accent-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        /* Workout List */
        .workout-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
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

        .workout-info {
            flex: 1;
        }

        .workout-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 4px;
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
            background: conic-gradient(var(--accent-color) 0%, #f0f0f0 0);
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
                <i class="fas fa-dumbbell"></i> FitNova <span style="font-size: 10px; background: var(--secondary-color); color: var(--primary-color); padding: 2px 6px; border-radius: 4px; margin-left: 5px; font-weight: 700;">FREE</span>
            </a>
        </div>

        <nav class="sidebar-menu">
            <a href="freeuser_dashboard.php" class="menu-item active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="fitness_nutrition.php?view=workouts" class="menu-item">
                <i class="fas fa-dumbbell"></i> Free Workouts
            </a>
            <a href="fitness_nutrition.php?view=nutrition" class="menu-item">
                <i class="fas fa-carrot"></i> Healthy Recipes
            </a>
            <a href="my_progress.php" class="menu-item">
                <i class="fas fa-chart-line"></i> My Progress
            </a>
            <a href="client_profile_setup.php" class="menu-item">
                <i class="fas fa-user-circle"></i> Profile
            </a>

            <a href="fitshop.php" class="menu-item">
                <i class="fas fa-store"></i> FitShop
            </a>
            <a href="subscription_plans.php" class="menu-item" style="color: var(--accent-color);">
                <i class="fas fa-crown"></i> Upgrade to Pro
            </a>
        </nav>

        <div class="user-profile-preview">
            <div class="user-avatar-sm"><?php echo $initials; ?></div>
            <div class="user-info-sm">
                <h4><?php echo htmlspecialchars($userName); ?></h4>
                <p>Free Member</p>
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
                <h1>Welcome back, <?php echo htmlspecialchars($userName); ?>! 👋</h1>
                <p>Ready to crush your goals today?</p>
            </div>
            <div class="header-actions">

            </div>
        </header>

        <?php if (!$hasProfile): ?>
        <div class="section-card" style="background: #fff9db; border: 1px solid #ffe066; color: #856404; display: flex; align-items: center; justify-content: space-between; padding: 20px; border-radius: 12px; margin-bottom: 30px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-user-circle" style="font-size: 24px;"></i>
                <div>
                    <h4 style="margin-bottom: 5px;">Incomplete Profile</h4>
                    <p style="font-size: 14px; opacity: 0.9;">Please complete your fitness assessment to get personalized workout and diet plans.</p>
                </div>
            </div>
            <a href="client_profile_setup.php" class="btn-primary" style="background: #f08c00; color: white;">Setup Profile</a>
        </div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fas fa-fire"></i></div>
                <div class="stat-value" id="dashCalories">0</div>
                <div class="stat-label">Calories Burned</div>
            </div>
            <div class="stat-card gold">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-value" id="dashTime">0h</div>
                <div class="stat-label">Workout Time</div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                <div class="stat-value" id="dashWorkouts">0</div>
                <div class="stat-label">Completed Workouts</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-weight"></i></div>
                <div class="stat-value" id="dashWeight"><?php echo $profileWeight; ?></div>
                <div class="stat-label">Current Weight</div>
            </div>
        </div>

        <div class="dashboard-layout">
            <!-- Left Column: Recommendations -->
            <div class="col-left">
                <!-- Upgrade Banner for Free Members -->
                <div class="section-card"
                    style="background: linear-gradient(135deg, #1A3C6B 0%, #0F2C59 100%); color: white; display: flex; align-items: center; justify-content: space-between; padding: 20px;">
                    <div>
                        <h3 style="margin-bottom: 5px; font-size: 1.1rem; color: white;">Unlock Your Full Potential</h3>
                        <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 0; color: rgba(255,255,255,0.8);">Get
                            a personal trainer & custom meal plans.</p>
                    </div>
                    <button onclick="window.location.href='subscription_plans.php'"
                        style="background: var(--accent-color); color: white; border: none; padding: 8px 16px; border-radius: 50px; font-weight: 600; cursor: pointer; white-space: nowrap;">Go
                        Premium</button>
                </div>

                <?php
                // Fetch Upcoming live sessions aimed at this user
                $scheduleSql = "SELECT ts.*, t.first_name as trainer_first, t.last_name as trainer_last 
                                FROM trainer_schedules ts
                                JOIN users t ON ts.trainer_id = t.user_id
                                WHERE (
                                    ts.client_name = ? 
                                    OR ts.client_name = ? 
                                    OR ts.client_name LIKE CONCAT(?, '%')
                                    OR ? LIKE CONCAT(ts.client_name, '%')
                                )
                                AND ts.status = 'upcoming'
                                AND ts.session_date >= CURDATE()
                                ORDER BY ts.session_date ASC, ts.session_time ASC
                                LIMIT 3";
                $stmt = $conn->prepare($scheduleSql);
                $mySchedules = [];
                if ($stmt) {
                    $stmt->bind_param("ssss", $dbFullName, $userName, $firstNameOnly, $dbFullName);
                    $stmt->execute();
                    $mySchedules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();
                }
                ?>
                
                <?php if (!empty($mySchedules)): ?>
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Upcoming Sessions</h3>
                    </div>
                    <?php foreach ($mySchedules as $session): 
                        $sDate = date('M d', strtotime($session['session_date']));
                        $dayName = date('D', strtotime($session['session_date']));
                        $sTime = date('h:i A', strtotime($session['session_time']));
                    ?>
                    <div class="workout-item">
                         <div class="workout-img" style="background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; flex-direction: column; width: 60px; height: 60px;">
                            <span style="font-size: 10px; opacity: 0.8;"><?php echo $dayName; ?></span>
                            <span style="font-size: 16px;"><?php echo date('d', strtotime($session['session_date'])); ?></span>
                         </div>
                         <div class="workout-info">
                             <h4 class="workout-name">
                                 <?php echo htmlspecialchars($session['session_type']); ?>
                             </h4>
                             <div class="workout-meta">
                                 <span><i class="far fa-clock"></i> <?php echo $sTime; ?></span>
                                 <span><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($session['trainer_first']); ?></span>
                             </div>
                         </div>
                         <button class="btn-action" onclick='showSessionDetails(<?php echo json_encode($session); ?>)'>Details</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div id="offline-gym-section" class="section-card" style="border-left: 5px solid var(--accent-color);">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-building" style="margin-right: 10px;"></i>Offline Gym Access</h3>
                        <?php if ($gymStatus === 'active'): ?>
                            <span style="background: rgba(40, 167, 69, 0.1); color: var(--success-color); padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 700;">Active Member</span>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 250px;">
                            <?php if ($gymStatus === 'active'): ?>
                                <h4 style="font-size: 1.1rem; color: var(--primary-color); margin-bottom: 10px;">Your Access Pass</h4>
                                <p style="color: var(--text-light); margin-bottom: 15px; font-size: 0.9rem;">
                                    Show this QR code at the reception desk to check in.
                                </p>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=FitNova-User-<?php echo $userId; ?>-Access" alt="Gym Access QR" style="border-radius: 8px; border: 1px solid #eee;">
                                    <div>
                                        <ul style="list-style: none; color: var(--text-light); font-size: 0.85rem; padding:0;">
                                            <li style="margin-bottom: 5px;"><i class="fas fa-check-circle" style="color: var(--success-color); margin-right: 5px;"></i> Gym Floor</li>
                                            <li style="margin-bottom: 5px;"><i class="fas fa-check-circle" style="color: var(--success-color); margin-right: 5px;"></i> Showers</li>
                                            <li style="margin-bottom: 5px;"><i class="fas fa-check-circle" style="color: var(--success-color); margin-right: 5px;"></i> Lockers</li>
                                        </ul>
                                    </div>
                                </div>
                            <?php else: ?>
                                <h4 style="font-size: 1.1rem; color: var(--primary-color); margin-bottom: 10px;">Train at Our Gyms</h4>
                                <p style="color: var(--text-light); margin-bottom: 15px; font-size: 0.9rem;">
                                    Get full access to all FitNova locations, premium equipment, and on-site trainers.
                                </p>
                                <div style="display: flex; gap: 15px; margin-bottom: 20px; font-size: 0.9rem;">
                                    <div style="display: flex; align-items: center; gap: 5px; color: var(--text-color);">
                                        <i class="fas fa-map-marker-alt" style="color: var(--accent-color);"></i> All Locations
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 5px; color: var(--text-color);">
                                        <i class="fas fa-clock" style="color: var(--accent-color);"></i> 24/7
                                    </div>
                                </div>
                                <button onclick="subscribeGym()" class="btn-primary" style="background: var(--accent-color); cursor:pointer; width:100%; justify-content:center;">
                                    Get Access for ₹10
                                </button>
                            <?php endif; ?>
                        </div>
                        <div style="flex: 1; min-width: 250px;">
                            <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Gym Interior" style="width: 100%; border-radius: 8px; height: 180px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Progress -->
            <div class="col-right">
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">Weekly Goal</h3>
                    </div>
                    <div class="progress-container">
                        <div class="progress-circle">
                            <div class="progress-value">
                                0%
                                <span class="progress-label">Completed</span>
                            </div>
                        </div>
                        <p style="color: var(--text-light); font-size: 14px; margin-bottom: 20px;">Track your weekly calorie burn & daily habits!</p>

                        <div class="daily-goals-list">
                            <div class="goal-item">
                                <span><i class="fas fa-fire" style="color: var(--accent-color); margin-right: 8px;"></i>Weekly Burn</span>
                                <strong id="goalCalories">0 / 2,000</strong>
                            </div>
                            <div class="goal-item">
                                <span><i class="fas fa-tint" style="color: #3498DB; margin-right: 8px;"></i>Water (Today)</span>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <strong id="dashWater">0L / 3L</strong>
                                    <button onclick="addWater()" style="background: #3498DB; color: white; border: none; width: 20px; height: 20px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px;"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="goal-item">
                                <span><i class="fas fa-bed" style="color: #9B59B6; margin-right: 8px;"></i>Sleep (Last Night)</span>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <strong id="dashSleep">0h / 8h</strong>
                                    <button onclick="addSleep()" style="background: #9B59B6; color: white; border: none; width: 20px; height: 20px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px;"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card"
                    style="background: linear-gradient(135deg, var(--primary-color) 0%, #1A3C6B 100%); color: white;">
                    <div class="section-header">
                        <h3 class="section-title" style="color: white;">Pro Tip</h3>
                        <i class="fas fa-lightbulb" style="color: var(--secondary-color);"></i>
                    </div>
                    <p style="font-size: 14px; opacity: 0.9; line-height: 1.6;">Consistency is key! Try to maintain a
                        regular workout schedule, even if it's just 20 minutes a day. Small steps lead to big changes.
                    </p>
                </div>
            </div>
        </div>
    </main>

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
            
            // Format time manually
            let timeParts = session.session_time.split(':');
            let h = parseInt(timeParts[0]);
            let m = timeParts[1];
            let ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            h = h ? h : 12; 
            let timeStr = h + ':' + m + ' ' + ampm;
            
            document.getElementById('modalSessionTime').innerText = timeStr;
            document.getElementById('modalSessionStatus').innerText = session.status.toUpperCase();
            
            const modal = document.getElementById('sessionModal');
            modal.style.display = 'flex';
        }

        function closeSessionModal() {
            document.getElementById('sessionModal').style.display = 'none';
        }

        // Simple script to handle mobile sidebar toggle if we add a hamburger later

    </script>
    <!-- Upgrade / Subscription Modal -->
    <div id="subscriptionModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header-img">
                <i class="fas fa-crown"></i>
            </div>
            <h2>Unlock Full Access</h2>
            <p>You are currently on the <strong>Free Plan</strong>.</p>
            <p class="highlight-text">Upgrade to <strong>Lite</strong> or <strong>Pro</strong> to access Personal Trainers, Custom Diet Plans, and more!</p>
            
            <div class="modal-features">
                <div class="feature-item"><i class="fas fa-check-circle"></i> Personal Trainer Access</div>
                <div class="feature-item"><i class="fas fa-check-circle"></i> Customized Workout Plans</div>
                <div class="feature-item"><i class="fas fa-check-circle"></i> Advanced Progress Tracking</div>
            </div>

            <div class="modal-actions">
                <a href="subscription_plans.php" class="btn-upgrade-modal">Choose a Plan</a>
                <button class="btn-close-modal" onclick="closeSubscriptionModal()">Maybe Later</button>
            </div>
        </div>
    </div>

    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
            animation: fadeIn 0.4s ease;
        }
        
        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 24px;
            width: 90%;
            max-width: 500px;
            text-align: center;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.4s ease;
        }
        
        .modal-header-img {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -60px auto 20px;
            box-shadow: 0 10px 20px rgba(253, 185, 49, 0.3);
            font-size: 32px;
            color: #fff;
            border: 4px solid #fff;
        }
        
        .modal-content h2 {
            color: var(--primary-color);
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 800;
        }
        
        .modal-content p {
            color: #666;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .highlight-text {
            color: var(--text-color);
            font-weight: 500;
        }
        
        .modal-features {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: left;
        }
        
        .feature-item {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary-color);
            font-weight: 500;
            font-size: 14px;
        }
        
        .feature-item i {
            color: var(--success-color);
        }
        
        .feature-item:last-child { margin-bottom: 0; }
        
        .modal-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .btn-upgrade-modal {
            background: var(--accent-color);
            color: white;
            padding: 14px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.2s;
            box-shadow: 0 4px 15px rgba(230, 57, 70, 0.3);
        }
        
        .btn-upgrade-modal:hover {
            transform: translateY(-2px);
            background: #d62828;
        }
        
        .btn-close-modal {
            background: transparent;
            border: none;
            color: #999;
            font-weight: 600;
            cursor: pointer;
            padding: 10px;
            font-size: 14px;
        }
        
        .btn-close-modal:hover {
            color: #666;
        }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>

    <script>
        // Check if modal has been shown recently to avoid annoyance (Optional: remove sessionStorage check to show ALWAYS)
        // For now, showing it based on URL or just on load as requested "pop an message here"
        
        document.addEventListener('DOMContentLoaded', () => {
             // Sync stats from LocalStorage
             const userId = "<?php echo $_SESSION['user_id']; ?>";
             const S_KEY = (k) => `${k}_${userId}`;
             
             const logs = JSON.parse(localStorage.getItem(S_KEY('fitnova_logs')) || '[]');
             const weight = localStorage.getItem(S_KEY('fitnova_weight')) || 0;

             // Calculate Totals
             const totalCalories = logs.reduce((sum, log) => sum + parseInt(log.calories || 0), 0);
             const totalWorkouts = logs.length;
             const totalTimeMinutes = logs.reduce((sum, log) => sum + parseInt(log.duration || 0), 0);
             const totalTimeHours = (totalTimeMinutes / 60).toFixed(1);

             // Calculate Weekly Progress
             const today = new Date();
             const startOfWeek = new Date(today);
             startOfWeek.setDate(today.getDate() - today.getDay() + 1); // Monday
             startOfWeek.setHours(0,0,0,0);
             
             const weeklyCals = logs.reduce((sum, log) => {
                 let logDate;
                 if(log.timestamp) {
                     logDate = new Date(log.timestamp);
                 } else {
                     logDate = new Date(log.date + ', ' + today.getFullYear()); 
                 }
                 
                 // If invalid date (e.g. parsing failed), skip
                 if(isNaN(logDate.getTime())) return sum;

                 if (logDate >= startOfWeek) return sum + parseInt(log.calories);
                 return sum;
             }, 0);
             
             const targetCals = 2000;
             const percent = Math.min(100, Math.round((weeklyCals / targetCals) * 100));

             // Update DOM Elements
             const elCals = document.getElementById('dashCalories');
             const elTime = document.getElementById('dashTime');
             const elWorkouts = document.getElementById('dashWorkouts');
             const elWeight = document.getElementById('dashWeight');
             
             if(elCals) elCals.innerText = totalCalories.toLocaleString();
             if(elTime) elTime.innerText = totalTimeHours + 'h';
             if(elWorkouts) elWorkouts.innerText = totalWorkouts;
             if(elWeight) elWeight.innerText = weight + 'kg';

             // Weekly Goal (Calories)
             const elProgVal = document.querySelector('.progress-value');
             const elProgCircle = document.querySelector('.progress-circle');
             const elGoalCalories = document.getElementById('goalCalories');

             if(elProgVal && elProgCircle) {
                 elProgVal.innerHTML = `${percent}% <span class="progress-label">Completed</span>`;
                 elProgCircle.style.background = `conic-gradient(var(--accent-color) ${percent}%, #f0f0f0 0)`;
             }
             if(elGoalCalories) {
                  elGoalCalories.innerText = `${weeklyCals.toLocaleString()} / ${targetCals.toLocaleString()}`;
             }

             // Water & Sleep Logic (Daily Habits)
             const todayStr = new Date().toLocaleDateString();
             const lastDate = localStorage.getItem(S_KEY('fitnova_last_date'));
             
             if(lastDate !== todayStr) {
                 localStorage.setItem(S_KEY('fitnova_last_date'), todayStr);
                 localStorage.setItem(S_KEY('fitnova_water'), 0);
                 localStorage.setItem(S_KEY('fitnova_sleep'), 0);
             }

             let water = parseFloat(localStorage.getItem(S_KEY('fitnova_water')) || 0);
             let sleep = parseFloat(localStorage.getItem(S_KEY('fitnova_sleep')) || 0);
             
             const updateHabits = () => {
                 const elWater = document.getElementById('dashWater');
                 const elSleep = document.getElementById('dashSleep');
                 if(elWater) elWater.innerText = `${water.toFixed(1).replace('.0','')}L / 3L`;
                 if(elSleep) elSleep.innerText = `${sleep.toFixed(1).replace('.0','')}h / 8h`;
             };
             updateHabits();

             window.addWater = () => {
                 water = Math.min(5, water + 0.25); // +250ml
                 localStorage.setItem(S_KEY('fitnova_water'), water);
                 updateHabits();
             };
             
             window.addSleep = () => {
                 sleep = Math.min(12, sleep + 0.5); // +30min
                 localStorage.setItem(S_KEY('fitnova_sleep'), sleep);
                 updateHabits();
             };

             // Modal Logic
             const modal = document.getElementById('subscriptionModal');
             if(modal) {
                 setTimeout(() => {
                    modal.style.display = 'flex';
                 }, 500); 
             }
        });

        function closeSubscriptionModal() {
            const modal = document.getElementById('subscriptionModal');
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.display = 'none';
                modal.style.opacity = '1';
            }, 300);
        }
    </script>
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


