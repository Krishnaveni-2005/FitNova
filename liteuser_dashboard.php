<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require "db_connect.php";

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

// Fetch Assigned Workouts
$workoutSql = "SELECT * FROM trainer_workouts WHERE user_id = ? ORDER BY created_at DESC LIMIT 3";
$stmt = $conn->prepare($workoutSql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $assignedWorkouts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $assignedWorkouts = []; 
}

// Fetch Assigned Diets
$dietSql = "SELECT * FROM trainer_diet_plans WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($dietSql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $assignedDiets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
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
            <a href="trainers.php" class="menu-item">
                <i class="fas fa-users"></i> Trainers
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
                 <!-- Find Trainer Removed for Lite -->
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
                <div><i class="fas fa-check-double" style="color: var(--success-color);"></i> Workouts</div>
                <div class="stat-value">0</div>
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

                <!-- Assigned Plans -->
                <div class="section-card">
                    <h3 class="section-title">Your Workouts</h3>
                    <?php if (empty($assignedWorkouts)): ?>
                        <p style="color: var(--text-light);">No workout plans assigned yet.</p>
                    <?php else: ?>
                        <?php foreach($assignedWorkouts as $w): ?>
                            <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                <h4 style="margin:0; font-size: 16px;"><?php echo htmlspecialchars($w['plan_name']); ?></h4>
                                <span style="font-size: 13px; color: var(--text-light);"><?php echo $w['duration_weeks']; ?> Weeks • <?php echo ucfirst($w['difficulty']); ?></span>
                                <a href="view_my_workout.php?plan_id=<?php echo $w['workout_id']; ?>" style="display: block; margin-top: 5px; font-size: 13px; color: var(--primary-color);">View Plan</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
</body>
</html>
