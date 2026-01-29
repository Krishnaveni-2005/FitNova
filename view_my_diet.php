<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch All Diet Plans with Trainer Info
// Fetch Plans
$sql = "SELECT d.*, t.first_name as t_first, t.last_name as t_last 
        FROM trainer_diet_plans d 
        LEFT JOIN users t ON d.trainer_id = t.user_id";

$params = [];
$types = "";

if (isset($_GET['view_personal']) && isset($_GET['trainer_id'])) {
    // Show Trainer's Personal Plans
    $sql .= " WHERE d.user_id = d.trainer_id AND d.trainer_id = ?";
    $params[] = $_GET['trainer_id'];
    $types = "i";
} else {
    // Show Client's Assigned Plans
    $sql .= " WHERE d.user_id = ?";
    $params = [$userId];
    $types = "i";

    if (isset($_GET['trainer_id'])) {
        $sql .= " AND d.trainer_id = ?";
        $params[] = $_GET['trainer_id'];
        $types .= "i";
    }
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
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
        body { font-family: 'Outfit', sans-serif; background: var(--bg-color); color: var(--text-color); margin: 0; display: flex; flex-direction: column; min-height: 100vh; }
        
        .main-content { flex: 1; padding: 40px 20px; max-width: 1000px; margin: 0 auto; width: 100%; box-sizing: border-box; }

        .back-btn { display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: #64748b; font-weight: 500; margin-bottom: 20px; transition: 0.3s; }
        .back-btn:hover { color: var(--primary-color); transform: translateX(-5px); }
        
        .plan-selector { 
            background: white; 
            padding: 15px 25px; 
            border-radius: 50px; 
            display: inline-flex; 
            align-items: center; 
            gap: 15px; 
            border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            transition: 0.3s;
        }
        .plan-select-input { border: none; outline: none; background: transparent; font-family: inherit; font-weight: 600; color: var(--primary-color); cursor: pointer; min-width: 200px; }
        
        .plan-header { 
            background: white; 
            border-radius: 20px; 
            padding: 40px; 
            margin-bottom: 40px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            text-align: center; 
            position: relative; 
            overflow: hidden; 
        }
        .plan-header::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 5px; background: linear-gradient(90deg, var(--success-color), var(--primary-color));
        }

        .plan-title { font-size: 36px; color: var(--primary-color); margin: 0 0 10px 0; letter-spacing: -1px; }
        .trainer-info { color: #64748b; font-size: 15px; margin-top: 5px; font-weight: 500; }

        .plan-meta { display: flex; justify-content: center; gap: 15px; margin-top: 25px; flex-wrap: wrap; }
        .meta-badge { background: #ecfdf5; color: var(--success-color); padding: 8px 20px; border-radius: 30px; font-weight: 600; font-size: 14px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 8px; }

        .meal-grid { display: grid; gap: 30px; }
        .meal-card { 
            background: white; 
            border-radius: 20px; 
            overflow: hidden; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            border: 1px solid #f1f5f9;
            transition: transform 0.3s;
        }
        .meal-card:hover { transform: translateY(-5px); }

        .meal-header { 
            background: var(--primary-color); 
            color: white; 
            padding: 20px 30px; 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            font-weight: 700; 
            font-size: 18px; 
        }
        .meal-header.lunch { background: #3b82f6; }
        .meal-header.dinner { background: #6366f1; }
        .meal-header.snacks { background: #DAC0A3; color: #0F2C59; }
        
        .meal-content { 
            padding: 30px; 
            line-height: 1.8; 
            color: #334155; 
            white-space: pre-wrap; 
            font-family: 'Inter', sans-serif;
            font-size: 15px;
        }
        
        /* Empty State */
        .empty-state { 
            text-align: center; 
            padding: 80px 20px; 
            color: #64748b; 
            background: white; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            max-width: 600px;
            margin: 40px auto;
        }
        .empty-icon-container {
            width: 100px; height: 100px; background: #fdf2f8; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;
        }
        .btn-action {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 25px;
            transition: 0.3s;
        }
        .btn-action:hover { background: #0a1f40; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="main-content">
        
        <!-- Multiple Plans Selector -->
        <?php if (count($allPlans) > 1): ?>
        <div style="text-align: right;">
            <div class="plan-selector">
                <i class="fas fa-history" style="color: var(--primary-color);"></i>
                <span style="font-weight: 600; font-size: 14px; color: #64748b;">Select Plan:</span>
                <select class="plan-select-input" onchange="window.location.href='view_my_diet.php?plan_id='+this.value">
                    <?php foreach($allPlans as $p): ?>
                        <option value="<?php echo $p['diet_id']; ?>" <?php echo ($activePlan && $activePlan['diet_id'] == $p['diet_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['plan_name']); ?> 
                            (<?php echo date('M d, Y', strtotime($p['created_at'])); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activePlan): ?>
            <div class="plan-header">
                <div style="margin-bottom: 20px;">
                    <i class="fas fa-apple-alt" style="font-size: 48px; color: var(--success-color); background: #f0fdf4; padding: 20px; border-radius: 50%;"></i>
                </div>

                <h1 class="plan-title"><?php echo htmlspecialchars($activePlan['plan_name']); ?></h1>
                
                <div class="trainer-info">
                    <i class="fas fa-user-circle"></i> Assigned by Coach <?php echo htmlspecialchars($activePlan['t_first'] . ' ' . $activePlan['t_last']); ?> 
                    on <?php echo date('F d, Y', strtotime($activePlan['created_at'])); ?>
                </div>

                <div class="plan-meta" style="margin-top: 20px;">
                    <div class="meta-badge"><i class="fas fa-bullseye"></i> <?php echo htmlspecialchars($activePlan['diet_type']); ?></div>
                    <div class="meta-badge" style="color: var(--primary-color); background: #eff6ff; border-color: #dbeafe;">
                        <i class="fas fa-fire-alt"></i> <?php echo $activePlan['target_calories']; ?> kcal/day
                    </div>
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
                        <div class="meal-content" style="text-align: center; color: #94a3b8;">
                            <i class="fas fa-info-circle"></i> No specific meal details added yet.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon-container">
                    <i class="fas fa-utensils" style="font-size: 40px; color: var(--accent-color);"></i>
                </div>
                <h2 style="color: var(--primary-color); margin-bottom: 10px;">No Diet Plan Assigned</h2>
                <p>Your trainer hasn't assigned a diet plan yet. <br>Nutrition is key! Reach out to your trainer.</p>
                <a href="my_trainers.php" class="btn-action">View My Trainers</a>
                 <div style="margin-top: 15px;">
                    <a href="home.php" style="color: #64748b; font-size: 14px; text-decoration: none;">Back to Home</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
