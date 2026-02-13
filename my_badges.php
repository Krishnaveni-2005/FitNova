<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Access: Free, Lite, Pro, Elite Users, Trainers
$allowedRoles = ['free', 'lite', 'pro', 'elite', 'trainer'];
if (isset($_SESSION['user_role']) && !in_array($_SESSION['user_role'], $allowedRoles)) {
    // Redirect logic for unauthorized roles
    if ($_SESSION['user_role'] === 'admin') header('Location: admin_dashboard.php');
    else header('Location: login.php');
    exit();
}

require 'db_connect.php';
require 'gamification_helper.php';

$userId = $_SESSION['user_id'];
$userStats = getUserStats($userId);

// Fetch badges based on Role
$isTrainer = ($_SESSION['user_role'] === 'trainer');

// Fetch Trainer Stats for Progress Calculation
$tStats = [];
if ($isTrainer) {
    $tsRes = $conn->query("SELECT 
        (SELECT COUNT(*) FROM users WHERE assigned_trainer_id = $userId AND assignment_status = 'approved') as clients,
        (SELECT COUNT(*) FROM trainer_schedules WHERE trainer_id = $userId) as sessions
    ");
    if ($tsRes) $tStats = $tsRes->fetch_assoc();
}

$roleFilter = $isTrainer ? "target_role = 'trainer'" : "(target_role = 'client' OR target_role = 'all')";

// Fetch ALL badges
$res = $conn->query("SELECT * FROM gamification_badges WHERE $roleFilter ORDER BY criteria_value ASC");
$allBadges = $res->fetch_all(MYSQLI_ASSOC);

// Fetch Earned Badges
$userBadges = getUserBadges($userId);
$earnedIds = array_column($userBadges, 'badge_id');

// Build a map of earned details
$earnedMap = [];
foreach ($userBadges as $ub) {
    $earnedMap[$ub['badge_id']] = $ub['earned_at'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Badges - FitNova</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0F2C59 0%, #1a3c6b 100%);
            --accent-gradient: linear-gradient(135deg, #E63946 0%, #ff6b6b 100%);
            --card-bg: #ffffff;
            --bg-color: #cfd4da; /* Distinctly darker grey */
            --text-dark: #1a1a1a;
            --text-light: #6c757d;
        }

        body { 
            font-family: 'Outfit', sans-serif; 
            background-color: var(--bg-color); 
            color: var(--text-dark); 
            margin: 0; 
            padding: 0;
            min-height: 100vh;
        }
        
        .container { 
            max-width: 95%; 
            margin: 0 auto; 
            padding: 40px 20px; 
        }

        /* Page Header Styling - Banner Style */
        .page-header { 
            text-align: center; 
            margin-bottom: 30px; 
            margin-top: 10px;
            padding: 50px 20px;
            background: var(--primary-gradient);
            border-radius: 25px;
            color: white;
            box-shadow: 0 15px 40px rgba(15, 44, 89, 0.2);
            position: relative;
            overflow: hidden;
        }

        /* Decorative Circles */
        .page-header::before {
            content: '';
            position: absolute;
            top: -60px;
            left: -60px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .page-header::after {
            content: '';
            position: absolute;
            bottom: -40px;
            right: -40px;
            width: 180px;
            height: 180px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        
        .page-header h1 { 
            font-size: 3rem; 
            font-weight: 800;
            background: none;
            -webkit-text-fill-color: white;
            color: white;
            margin-bottom: 15px;
            letter-spacing: -1px;
            position: relative;
            z-index: 2;
        }
        
        .page-header p { 
            color: rgba(255, 255, 255, 0.85); 
            font-family: 'Inter', sans-serif;
            font-size: 1.1rem; 
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .stats-pill {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 12px 30px;
            border-radius: 50px;
            margin-top: 30px;
            font-weight: 700;
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
            position: relative;
            z-index: 2;
        }

        .stats-pill span { color: #ff9f43; font-size: 1.1rem; }

        /* Grid Layout */
        .badges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
            gap: 15px;
        }
        
        /* Badge Card */
        .badge-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            border: 1px solid rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            min-height: 160px; /* Slightly larger height */
        }
        
        .badge-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        
        /* Locked vs Unlocked */
        .badge-card.locked {
            filter: grayscale(100%);
            opacity: 0.6;
            background: #f8f9fa;
        }
        
        .badge-card.unlocked {
            border-bottom: 4px solid; 
        }

        /* Icon Container */
        .badge-icon-wrapper {
            position: relative;
            margin-bottom: 10px;
        }
        
        .badge-icon {
            width: 50px; /* Medium icon */
            height: 50px;
            border-radius: 50%;
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: 1.4rem; 
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background: #bdc3c7; 
            transition: transform 0.3s ease;
        }
        
        .badge-card:hover .badge-icon {
            transform: scale(1.1) rotate(5deg);
        }

        /* Lock Overlay */
        .lock-badge {
            position: absolute;
            bottom: -3px;
            right: -3px;
            background: #34495e;
            color: white;
            width: 20px; 
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 2;
        }

        /* Typography */
        .badge-title { 
            font-weight: 700; 
            font-size: 0.85rem; 
            margin-bottom: 6px; 
            color: var(--text-dark);
            line-height: 1.3;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Limit to 2 lines */
            -webkit-box-orient: vertical;
        }
        
        .status-text {
            font-size: 0.65rem; 
            font-weight: 600; 
            color: #27ae60;
            background: rgba(39, 174, 96, 0.1);
            padding: 4px 10px;
            border-radius: 8px;
            display: inline-block;
            white-space: nowrap; 
            margin-top: 8px !important;
        }

        .badge-card.locked .status-text {
            color: var(--text-light);
            background: rgba(0,0,0,0.05);
        }

        /* Progress Bar */
        .progress-container {
            width: 100%;
            margin-top: 8px;
        }
        
        .progress-bar {
            height: 5px; 
            background: #e9ecef; 
            border-radius: 10px; 
            overflow: hidden; 
            width: 100%;
        }
        
        .progress-fill {
            height: 100%; 
            background: linear-gradient(90deg, #3498DB, #2ECC71); 
            border-radius: 10px;
        }

        /* Back Button */
        .btn-back {
            position: absolute;
            top: 40px;
            left: 20px;
            display: inline-flex; 
            align-items: center; 
            gap: 10px; 
            text-decoration: none; 
            color: var(--text-light); 
            font-weight: 600; 
            padding: 10px 20px;
            background: white;
            border-radius: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        .btn-back:hover { 
            color: var(--text-dark); 
            transform: translateX(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .btn-back { position: static; margin-bottom: 20px; display: inline-flex; }
            .header h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>

<!-- Include standard Header -->
<?php include 'header.php'; ?>

<div class="container">
    
    <div class="page-header">
        <h1>Badge Collection</h1>
        <p>Unlock achievements as you progress on your fitness journey!</p>
        <div class="stats-pill">
            <span><?php echo count($earnedIds); ?></span> / <?php echo count($allBadges); ?> Unlocked
        </div>
    </div>
    
    <div class="badges-grid">
        <?php foreach ($allBadges as $badge): 
            $isUnlocked = in_array($badge['badge_id'], $earnedIds);
            
            // Calculate Progress for Locked Badges
            $progress = 0;
            $statusText = "Locked";
            
            if (!$isUnlocked) {
                if ($badge['criteria_type'] === 'workout_milestone') {
                    $current = $userStats['completed_workouts'] ?? 0;
                    $target = $badge['criteria_value'];
                    $progress = min(100, ($current / $target) * 100);
                    $statusText = "$current / $target Workouts";
                } elseif ($badge['criteria_type'] === 'streak') {
                    $current = $userStats['current_streak'] ?? 0;
                    $target = $badge['criteria_value'];
                    $progress = min(100, ($current / $target) * 100);
                    $statusText = "$current / $target Day Streak";
                } elseif ($badge['criteria_type'] === 'calories_milestone') {
                    $current = $userStats['total_calories'] ?? 0;
                    $target = $badge['criteria_value'];
                    $progress = min(100, ($current / $target) * 100);
                    $statusText = number_format($current) . " / " . number_format($target) . " kcal";
                } elseif ($badge['criteria_type'] === 'client_count') {
                    $current = $tStats['clients'] ?? 0;
                    $target = $badge['criteria_value'];
                    $progress = min(100, ($current / max(1, $target)) * 100);
                    $statusText = "$current / $target Clients";
                } elseif ($badge['criteria_type'] === 'session_count') {
                    $current = $tStats['sessions'] ?? 0;
                    $target = $badge['criteria_value'];
                    $progress = min(100, ($current / max(1, $target)) * 100);
                    $statusText = "$current / $target Sessions";
                }
            } else {
                $progress = 100;
                $dateEarned = date('M d, Y', strtotime($earnedMap[$badge['badge_id']]));
                $statusText = "Earned on $dateEarned";
            }
            
            // Dynamic Color Logic
            $cardBorderColor = $isUnlocked ? $badge['color'] : 'transparent';
        ?>
            <div class="badge-card <?php echo $isUnlocked ? 'unlocked' : 'locked'; ?>" 
                 style="<?php if($isUnlocked) echo 'border-bottom-color: ' . $badge['color']; ?>">
                
                <div class="badge-icon-wrapper">
                    <div class="badge-icon" style="<?php if($isUnlocked || $badge['color']) echo 'background: ' . $badge['color']; ?>">
                        <i class="<?php echo $badge['icon_class']; ?>"></i>
                    </div>
                    <?php if(!$isUnlocked): ?>
                        <div class="lock-badge"><i class="fas fa-lock"></i></div>
                    <?php endif; ?>
                </div>
                
                <div class="badge-title"><?php echo htmlspecialchars($badge['name']); ?></div>
                
                <div class="status-container" style="width: 100%; margin-top: auto;">
                    <?php if(!$isUnlocked): ?>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $progress; ?>%; background: <?php echo $badge['color']; ?>;"></div>
                        </div>
                    <?php endif; ?>
                    
                    <span class="status-text">
                        <?php echo $statusText; ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
