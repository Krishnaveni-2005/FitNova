<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Strict Access: Only Free Users can view this Leaderboard
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'free') {
    // Redirect based on role if possible, or just deny access
    if ($_SESSION['user_role'] === 'lite') header('Location: liteuser_dashboard.php');
    elseif ($_SESSION['user_role'] === 'pro') header('Location: prouser_dashboard.php');
    elseif ($_SESSION['user_role'] === 'elite') header('Location: eliteuser_dashboard.php');
    elseif ($_SESSION['user_role'] === 'trainer') header('Location: trainer_dashboard.php');
    elseif ($_SESSION['user_role'] === 'admin') header('Location: admin_dashboard.php');
    else header('Location: login.php');
    exit();
}

require "db_connect.php";
require_once "gamification_helper.php";

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Get Leaderboard Data
$leaderboard = getLeaderboard(20); // Top 20

// Find My Rank
$myRank = '-';
$rankSql = "SELECT rank FROM (
                SELECT user_id, RANK() OVER (ORDER BY total_points DESC) as rank 
                FROM user_gamification_stats s 
                JOIN users u ON s.user_id = u.user_id 
                WHERE u.role = 'free'
            ) as rankings WHERE user_id = $userId";

$res = $conn->query($rankSql);
if ($res && $res->num_rows > 0) {
    $myRank = $res->fetch_assoc()['rank'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --secondary-color: #DAC0A3;
            --accent-color: #E63946;
            --bg-color: #F8F9FA;
            --text-color: #333;
        }
        body { margin: 0; font-family: 'Inter', sans-serif; background: var(--bg-color); color: var(--text-color); }
        
        .container { max-width: 800px; margin: 40px auto; padding: 20px; }
        
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { font-family: 'Outfit', sans-serif; font-size: 36px; color: var(--primary-color); margin-bottom: 10px; }
        .header p { color: #666; }
        
        .rank-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            transition: transform 0.2s;
            border-left: 5px solid transparent;
        }
        
        .rank-card:hover { transform: translateX(5px); }
        
        .rank-card.top-1 { border-left-color: #FFD700; background: linear-gradient(to right, #fff, #fffae6); }
        .rank-card.top-2 { border-left-color: #C0C0C0; }
        .rank-card.top-3 { border-left-color: #CD7F32; }
        .rank-card.me { border: 2px solid var(--primary-color); background: #eef2ff; }
        
        .rank-info { display: flex; align-items: center; gap: 20px; }
        .rank-num { 
            font-family: 'Outfit', sans-serif; 
            font-size: 24px; 
            font-weight: 700; 
            width: 40px; 
            text-align: center;
            color: var(--text-color);
        }
        
        .user-avatar {
            width: 50px; 
            height: 50px; 
            background: var(--secondary-color); 
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        
        .user-details h3 { margin: 0; font-size: 16px; font-weight: 600; }
        .user-details span { font-size: 12px; color: #777; }
        
        .points-badge {
            background: var(--primary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            min-width: 80px;
            text-align: center;
        }
        
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
        }
        .back-btn:hover { color: var(--primary-color); }
        
        .streak-flame { color: #e67e22; margin-left: 5px; }
    </style>
</head>
<body>

<div class="container">
    <a href="freeuser_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    
    <div class="header">
        <h1><i class="fas fa-trophy" style="color: #FFD700;"></i> Leaderboard</h1>
        <p>Top dedicated members of the FitNova community</p>
    </div>
    
    <!-- My Rank Summary -->
    <div style="background: var(--primary-color); color: white; padding: 20px; border-radius: 12px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="margin: 0;">Your Ranking</h3>
            <p style="margin: 5px 0 0 0; opacity: 0.8;">Keep pushing to reach the top!</p>
        </div>
        <div style="font-size: 32px; font-weight: 700; font-family: 'Outfit';">
            #<?php echo $myRank; ?>
        </div>
    </div>
    
    <div class="leaderboard-list">
        <?php 
        $rank = 1;
        foreach ($leaderboard as $user): 
            $isMe = ($user['user_id'] == $userId);
            $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
            $rankClass = '';
            if ($rank == 1) $rankClass = 'top-1';
            elseif ($rank == 2) $rankClass = 'top-2';
            elseif ($rank == 3) $rankClass = 'top-3';
            if ($isMe) $rankClass .= ' me';
        ?>
            <div class="rank-card <?php echo $rankClass; ?>">
                <div class="rank-info">
                    <div class="rank-num">
                        <?php 
                        if ($rank == 1) echo '<i class="fas fa-crown" style="color: #FFD700;"></i>';
                        elseif ($rank == 2) echo '<i class="fas fa-medal" style="color: #C0C0C0;"></i>';
                        elseif ($rank == 3) echo '<i class="fas fa-medal" style="color: #CD7F32;"></i>';
                        else echo $rank;
                        ?>
                    </div>
                    <div class="user-avatar">
                        <?php echo $initials; ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> <?php if($isMe) echo '(You)'; ?></h3>
                        <span><i class="fas fa-fire streak-flame"></i> <?php echo $user['current_streak']; ?> day streak</span>
                    </div>
                </div>
                <div class="points-badge">
                    <?php echo number_format($user['total_points']); ?> pts
                </div>
            </div>
        <?php 
        $rank++;
        endforeach; 
        ?>
        
        <?php if (empty($leaderboard)): ?>
            <p style="text-align: center; color: #777;">Be the first to join the leaderboard!</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
