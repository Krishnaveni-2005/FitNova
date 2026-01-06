<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'elite') {
    header('Location: login.php');
    exit();
}
require "db_connect.php";
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Elite Member';
$initials = strtoupper(substr($userName, 0, 1) . substr($userName, strpos($userName, ' ') + 1, 1));
if (strlen($initials) < 2) $initials = strtoupper(substr($userName, 0, 2));

// Fetch profile info
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
    $assignedWorkouts = [];
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
    $assignedDiets = [];
    error_log("Diet Prepare Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Dashboard - FitNova</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap"
        rel="stylesheet">
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
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        h1, h2, h3, h4 {
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
            font-size: 10px;
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
            font-weight: 700;
        }

        .sidebar-menu {
            padding: 20px 0;
            flex: 1;
            overflow-y: auto;
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

        .pro-badge-sidebar {
            font-size: 10px;
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: auto;
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
            font-size: 12px;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .welcome-text h1 {
            font-size: 32px;
            margin-bottom: 8px;
            color: var(--primary-color);
        }

        .welcome-text p {
            color: var(--text-light);
            font-size: 16px;
        }

        .btn-elite {
            background: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
        }

        .btn-elite:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(15, 44, 89, 0.2);
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
            position: relative;
            overflow: hidden;
            border-top: 4px solid var(--secondary-color);
        }

        .stat-icon {
            font-size: 24px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-color);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        /* Content Layout */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        .section-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 20px;
            color: var(--primary-color);
        }

        /* Concierge Card */
        .concierge-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1A3C6B 100%);
            color: white;
            padding: 25px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .concierge-img {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            border: 2px solid var(--secondary-color);
            object-fit: cover;
        }

        .concierge-info h4 {
            color: var(--secondary-color);
            margin-bottom: 5px;
        }

        .btn-call {
            background: var(--secondary-color);
            color: var(--primary-color);
            border: none;
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
        }

        .logout-btn {
            color: var(--text-light);
            transition: var(--transition);
        }

        .logout-btn:hover {
            color: var(--accent-color);
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="home.php" class="brand-logo">
                <i class="fas fa-dumbbell"></i> FitNova <span class="pro-badge-sidebar" style="background:var(--secondary-color); margin-left:5px;">ELITE</span>
            </a>
        </div>

        <nav class="sidebar-menu">
            <a href="eliteuser_dashboard.php" class="menu-item active">
                <i class="fas fa-home"></i> Premium Dashboard
            </a>
            <a href="free_workouts.php" class="menu-item">
                <i class="fas fa-dumbbell"></i> Elite Training
            </a>
            <a href="healthy_recipes.php" class="menu-item">
                <i class="fas fa-utensils"></i> Gourmet Diets
            </a>
            <a href="my_progress.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Advanced Analytics
            </a>
            <a href="client_profile_setup.php" class="menu-item">
                <i class="fas fa-user-circle"></i> Profile
            </a>
            <a href="fitshop.php" class="menu-item">
                <i class="fas fa-store"></i> Exclusive Boutique
            </a>
            <a href="subscription_plans.php" class="menu-item" style="color: var(--accent-color);">
                <i class="fas fa-crown"></i> Manage Subscription
            </a>
        </nav>

        <div class="user-profile-preview">
            <div class="user-avatar-sm"><?php echo $initials; ?></div>
            <div class="user-info-sm">
                <h4><?php echo htmlspecialchars($userName); ?></h4>
                <p>Elite Member</p>
            </div>
            <a href="logout.php" style="margin-left: auto; color: var(--text-light);"><i
                    class="fas fa-sign-out-alt fa-flip-horizontal"></i></a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="dashboard-header">
            <div class="welcome-text">
                <h1>Welcome, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?>.</h1>
                <p>Your dedicated fitness concierge is ready for your instructions.</p>
            </div>
            <div class="header-actions">
                <a href="#" class="btn-elite">
                    <i class="fas fa-phone-alt"></i> Direct Line
                </a>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value">Elite Tier</div>
                <div class="stat-label">Membership Status</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-weight"></i></div>
                <div class="stat-value"><?php echo $profile['weight_kg'] ?? '--'; ?> kg</div>
                <div class="stat-label">Precision Mass</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-heartbeat"></i></div>
                <div class="stat-value">Optimum</div>
                <div class="stat-label">Vitality Index</div>
            </div>
        </div>

        <div class="dashboard-layout">
            <div class="col-left">
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-user-tie"></i> Dedicated Concierge</h3>
                    </div>
                    <div class="concierge-card">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80" alt="Concierge" class="concierge-img">
                        <div class="concierge-info">
                            <h4>Master Coach Julian</h4>
                            <p>Global Fitness Strategist & Bio-Hacker</p>
                            <button class="btn-call"><i class="fas fa-video"></i> Start Private Consultation</button>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-star"></i> Bespoke Daily Protocol</h3>
                    </div>
                    <?php if (empty($assignedWorkouts) && empty($assignedDiets)): ?>
                        <p style="color: var(--text-light); padding: 20px; text-align: center;">Your concierge is preparing your personalized protocols.</p>
                    <?php else: ?>
                        <?php foreach($assignedWorkouts as $w): ?>
                        <div class="elite-feature">
                            <div class="feature-icon"><i class="fas fa-dumbbell"></i></div>
                            <div class="feature-text">
                                <h5><?php echo htmlspecialchars($w['plan_name']); ?></h5>
                                <p><?php echo $w['duration_weeks']; ?> weeks • <?php echo ucfirst($w['difficulty']); ?> Elite Tier</p>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php foreach($assignedDiets as $d): ?>
                        <div class="elite-feature">
                            <div class="feature-icon"><i class="fas fa-leaf"></i></div>
                            <div class="feature-text">
                                <h5><?php echo htmlspecialchars($d['plan_name']); ?></h5>
                                <p><?php echo $d['target_calories']; ?> kcal/day • Type: <?php echo ucfirst($d['diet_type']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-right">
                <div class="section-card">
                    <div class="section-title"><i class="fas fa-microscope"></i> Bio-Analytics Summary</div>
                    <div style="margin-top:20px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                            <span style="font-size:14px;">Metabolic Rate</span>
                            <span style="font-weight:700;">Optimal</span>
                        </div>
                        <div style="height:4px; background:#EEE; border-radius:2px; margin-bottom:20px;">
                            <div style="width:90%; height:100%; background:var(--secondary-color); border-radius:2px;"></div>
                        </div>
                        
                        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                            <span style="font-size:14px;">Sleep Coherence</span>
                            <span style="font-weight:700;">94%</span>
                        </div>
                        <div style="height:4px; background:#EEE; border-radius:2px; margin-bottom:20px;">
                            <div style="width:94%; height:100%; background:var(--secondary-color); border-radius:2px;"></div>
                        </div>
                        
                        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                            <span style="font-size:14px;">Recovery Score</span>
                            <span style="font-weight:700;">Highest</span>
                        </div>
                        <div style="height:4px; background:#EEE; border-radius:2px;">
                            <div style="width:98%; height:100%; background:var(--secondary-color); border-radius:2px;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="section-card" style="background: linear-gradient(to bottom, #FFF, #FDF8E6);">
                    <div class="section-title"><i class="fas fa-gift"></i> Elite Perks</div>
                    <p style="font-size:14px; color:var(--text-light); margin:15px 0;">You have 2 complimentary Spa Invitations available this month.</p>
                    <button style="width:100%; padding:12px; border:1px solid var(--secondary-color); background:transparent; border-radius:8px; font-weight:700; cursor:pointer;">Claim Reward</button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
