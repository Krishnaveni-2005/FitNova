<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch All Diet Plans with Trainer Info
$sql = "SELECT d.*, t.first_name as t_first, t.last_name as t_last 
        FROM trainer_diet_plans d 
        LEFT JOIN users t ON d.trainer_id = t.user_id 
        WHERE d.user_id = ? 
        ORDER BY d.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$allPlans = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Determine Active Plan
$activePlan = null;
if (!empty($allPlans)) {
    if (isset($_GET['plan_id'])) {
        foreach ($allPlans as $p) {
            if ($p['diet_id'] == $_GET['plan_id']) {
                $activePlan = $p;
                break;
            }
        }
    }
    // Default to latest if not found or not set
    if (!$activePlan) {
        $activePlan = $allPlans[0];
    }
}

// Parse Meal Details
$mealData = ['breakfast'=>'', 'lunch'=>'', 'dinner'=>'', 'snacks'=>''];
if ($activePlan && !empty($activePlan['meal_details'])) {
    $decoded = json_decode($activePlan['meal_details'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $mealData = array_merge($mealData, $decoded);
    } else {
        $mealData['breakfast'] = $activePlan['meal_details'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Diet Plan - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #0F2C59; --secondary-color: #DAC0A3; --bg-color: #F8F9FA; --text-color: #333; --success-color: #10b981; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-color); color: var(--text-color); margin: 0; padding: 40px; }
        
        .container { max-width: 800px; margin: 0 auto; }
        .back-btn { display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: #64748b; font-weight: 500; margin-bottom: 20px; }
        .back-btn:hover { color: var(--primary-color); }
        
        .plan-selector { margin-bottom: 20px; background: white; padding: 15px 20px; border-radius: 12px; display: flex; align-items: center; gap: 15px; border: 1px solid #e2e8f0; }
        .plan-select-input { flex: 1; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; font-family: inherit; }
        
        .plan-header { background: white; border-radius: 15px; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); text-align: center; border: 1px solid #e2e8f0; }
        .plan-title { font-family: 'Outfit', sans-serif; font-size: 28px; color: var(--primary-color); margin: 0 0 10px 0; }
        .plan-meta { display: flex; justify-content: center; gap: 20px; color: #64748b; font-size: 14px; flex-wrap: wrap; }
        .meta-badge { background: #eef2ff; color: var(--primary-color); padding: 4px 10px; border-radius: 20px; font-weight: 600; }
        
        .meal-grid { display: grid; gap: 25px; }
        .meal-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        .meal-header { background: #0F2C59; color: white; padding: 15px 20px; display: flex; align-items: center; gap: 10px; font-weight: 600; font-family: 'Outfit', sans-serif; }
        .meal-header.lunch { background: #0F2C59; opacity: 0.9; }
        .meal-header.dinner { background: #0F2C59; opacity: 0.8; }
        .meal-header.snacks { background: #DAC0A3; color: #0F2C59; }
        
        .meal-content { padding: 25px; line-height: 1.7; color: #334155; white-space: pre-wrap; }
        
        .empty-state { text-align: center; padding: 50px; color: #94a3b8; }
        .trainer-info { color: #64748b; font-size: 13px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="prouser_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        
        <!-- Multiple Plans Selector -->
        <?php if (count($allPlans) > 1): ?>
        <div class="plan-selector">
            <i class="fas fa-history" style="color: var(--primary-color);"></i>
            <span style="font-weight: 600; font-size: 14px;">Select Plan:</span>
            <select class="plan-select-input" onchange="window.location.href='view_my_diet.php?plan_id='+this.value">
                <?php foreach($allPlans as $p): ?>
                    <option value="<?php echo $p['diet_id']; ?>" <?php echo ($activePlan && $activePlan['diet_id'] == $p['diet_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['plan_name']); ?> 
                        (<?php echo date('M d, Y', strtotime($p['created_at'])); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <?php if ($activePlan): ?>
            <div class="plan-header">
                <i class="fas fa-apple-alt" style="font-size: 40px; color: var(--success-color); margin-bottom: 15px;"></i>
                <h1 class="plan-title"><?php echo htmlspecialchars($activePlan['plan_name']); ?></h1>
                
                <div class="trainer-info">
                    Assigned by Coach <?php echo htmlspecialchars($activePlan['t_first'] . ' ' . $activePlan['t_last']); ?> 
                    on <?php echo date('F d, Y', strtotime($activePlan['created_at'])); ?>
                </div>

                <div class="plan-meta" style="margin-top: 15px;">
                    <span><i class="fas fa-bullseye"></i> <?php echo htmlspecialchars($activePlan['diet_type']); ?></span>
                    <span class="meta-badge"><?php echo $activePlan['target_calories']; ?> kcal/day</span>
                </div>
            </div>
            
            <div class="meal-grid">
                <?php if(!empty($mealData['breakfast'])): ?>
                <div class="meal-card">
                    <div class="meal-header"><i class="fas fa-coffee"></i> Breakfast</div>
                    <div class="meal-content"><?php echo htmlspecialchars($mealData['breakfast']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($mealData['lunch'])): ?>
                <div class="meal-card">
                    <div class="meal-header lunch"><i class="fas fa-utensils"></i> Lunch</div>
                    <div class="meal-content"><?php echo htmlspecialchars($mealData['lunch']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($mealData['dinner'])): ?>
                <div class="meal-card">
                    <div class="meal-header dinner"><i class="fas fa-moon"></i> Dinner</div>
                    <div class="meal-content"><?php echo htmlspecialchars($mealData['dinner']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($mealData['snacks'])): ?>
                <div class="meal-card">
                    <div class="meal-header snacks"><i class="fas fa-cookie-bite"></i> Snacks & Supplements</div>
                    <div class="meal-content"><?php echo htmlspecialchars($mealData['snacks']); ?></div>
                </div>
                <?php endif; ?>

                <?php if(empty($mealData['breakfast']) && empty($mealData['lunch']) && empty($mealData['dinner'])): ?>
                     <div class="meal-card">
                        <div class="meal-content">No specific meal details added yet.</div>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-utensils" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                <h2>No Plan Assigned</h2>
                <p>Your trainer hasn't assigned a diet plan yet.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
