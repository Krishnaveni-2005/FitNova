<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch All Workout Plans
$sql = "SELECT w.*, t.first_name as t_first, t.last_name as t_last 
        FROM trainer_workouts w 
        LEFT JOIN users t ON w.trainer_id = t.user_id 
        WHERE w.user_id = ? 
        ORDER BY w.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$allPlans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Determine Active Plan
$activePlan = null;
if (!empty($allPlans)) {
    if (isset($_GET['plan_id'])) {
        foreach ($allPlans as $p) {
            if ($p['workout_id'] == $_GET['plan_id']) {
                $activePlan = $p;
                break;
            }
        }
    }
    if (!$activePlan) $activePlan = $allPlans[0];
}

// Parse Levels
$levels = ['level_1'=>'', 'level_2'=>'', 'level_3'=>''];
if ($activePlan && !empty($activePlan['exercises'])) {
    $decoded = json_decode($activePlan['exercises'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $levels = array_merge($levels, $decoded);
    } else {
        $levels['level_1'] = $activePlan['exercises'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Workouts - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #0F2C59; --accent-color: #E63946; --bg-color: #F8F9FA; --text-color: #333; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-color); color: var(--text-color); margin: 0; padding: 40px; }
        
        .container { max-width: 95%; margin: 0 auto; }
        .back-btn { display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: #64748b; font-weight: 500; margin-bottom: 20px; }
        .back-btn:hover { color: var(--primary-color); }
        
        .plan-selector { margin-bottom: 20px; background: white; padding: 15px 20px; border-radius: 12px; display: flex; align-items: center; gap: 15px; border: 1px solid #e2e8f0; max-width: 600px; }
        .plan-select-input { flex: 1; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; font-family: inherit; }

        .plan-header { background: white; border-radius: 15px; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); text-align: center; border: 1px solid #e2e8f0; }
        .plan-title { font-family: 'Outfit', sans-serif; font-size: 28px; color: var(--primary-color); margin: 0 0 10px 0; }
        .trainer-info { color: #64748b; font-size: 13px; margin-top: 5px; }

        .plan-meta { display: flex; justify-content: center; gap: 20px; margin-top: 20px; }
        .meta-pill { background: #f1f5f9; padding: 5px 15px; border-radius: 20px; font-size: 13px; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px; }

        .levels-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
        .level-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; transition: transform 0.2s; }
        .level-card:hover { transform: translateY(-5px); }
        
        .level-header { padding: 20px; color: white; font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 18px; display: flex; align-items: center; gap: 10px; }
        .level-content { padding: 25px; color: #334155; line-height: 1.7; white-space: pre-wrap; font-size: 14px; }
        
        .empty-state { text-align: center; padding: 50px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <a href="prouser_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        
        <!-- Selector -->
        <?php if (count($allPlans) > 1): ?>
        <div class="plan-selector">
            <i class="fas fa-layer-group" style="color: var(--primary-color);"></i>
            <span style="font-weight: 600; font-size: 14px;">Select Routine:</span>
            <select class="plan-select-input" onchange="window.location.href='view_my_workout.php?plan_id='+this.value">
                <?php foreach($allPlans as $p): ?>
                    <option value="<?php echo $p['workout_id']; ?>" <?php echo ($activePlan && $activePlan['workout_id'] == $p['workout_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['plan_name']); ?> 
                        (<?php echo date('M d', strtotime($p['created_at'])); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <?php if ($activePlan): ?>
            <div class="plan-header">
                <i class="fas fa-dumbbell" style="font-size: 40px; color: var(--accent-color); margin-bottom: 15px;"></i>
                <h1 class="plan-title"><?php echo htmlspecialchars($activePlan['plan_name']); ?></h1>
                
                <div class="trainer-info">
                    Designed by Coach <?php echo htmlspecialchars($activePlan['t_first'] . ' ' . $activePlan['t_last']); ?>
                </div>

                <div class="plan-meta">
                    <div class="meta-pill"><i class="fas fa-signal"></i> <?php echo htmlspecialchars($activePlan['difficulty']); ?></div>
                    <div class="meta-pill"><i class="far fa-calendar-alt"></i> <?php echo $activePlan['duration_weeks']; ?> Weeks</div>
                </div>
            </div>
            
            <div class="levels-grid">
                <?php if(!empty($levels['level_1'])): ?>
                <div class="level-card">
                    <div class="level-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-seedling"></i> Beginner
                    </div>
                    <div class="level-content"><?php echo htmlspecialchars($levels['level_1']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($levels['level_2'])): ?>
                <div class="level-card">
                    <div class="level-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-fire"></i> Intermediate
                    </div>
                    <div class="level-content"><?php echo htmlspecialchars($levels['level_2']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($levels['level_3'])): ?>
                <div class="level-card">
                    <div class="level-header" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                        <i class="fas fa-burn"></i> Advanced
                    </div>
                    <div class="level-content"><?php echo htmlspecialchars($levels['level_3']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if(empty($levels['level_1']) && empty($levels['level_2']) && empty($levels['level_3'])): ?>
                     <div class="level-card" style="grid-column: 1 / -1; text-align: center;">
                        <div class="level-header" style="justify-content: center; background: #94a3b8;">Details</div>
                        <div class="level-content">No specific level details added to this plan.</div>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-running" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                <h2>No Workout Routine Assigned</h2>
                <p>Your trainer hasn't assigned a workout plan yet.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
