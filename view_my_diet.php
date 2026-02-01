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
    <title>My Diet Plans - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #0F2C59; --accent-color: #E63946; --bg-color: #F8F9FA; --text-color: #333; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg-color); color: var(--text-color); margin: 0; display: flex; flex-direction: column; min-height: 100vh; }
        
        .main-content { flex: 1; padding: 20px 20px 220px 20px; max-width: 95%; margin: 0 auto; width: 100%; box-sizing: border-box; }

        .page-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a3c70 100%);
            border-radius: 20px;
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(15, 44, 89, 0.15);
        }
        .page-hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%),
                        radial-gradient(circle at 90% 80%, rgba(255,255,255,0.05) 0%, transparent 20%);
            pointer-events: none;
        }
        .hero-text h1 { font-size: 1.8rem; margin: 0 0 8px 0; font-weight: 800; letter-spacing: -0.5px; }
        .hero-text p { font-size: 0.95rem; opacity: 0.9; max-width: 550px; margin: 0; line-height: 1.5; color: #e2e8f0; }
        .hero-decoration { font-size: 6rem; opacity: 0.08; transform: rotate(-15deg); position: absolute; right: 40px; bottom: -25px; }

        .plans-container { display: flex; flex-direction: column; gap: 25px; }
        
        .plan-accordion-item {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.03);
            transition: all 0.3s ease;
            position: relative;
        }
        .plan-accordion-item::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 6px;
            background: var(--primary-color);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .plan-accordion-item:hover {
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            transform: translateY(-5px);
        }
        .plan-accordion-item:hover::before {
            opacity: 1;
        }

        .plan-accordion-header {
            padding: 25px 35px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            user-select: none;
            transition: background 0.2s;
        }
        
        .header-info { display: flex; flex-direction: column; gap: 8px; }
        .plan-main-info { display: flex; align-items: baseline; gap: 15px; flex-wrap: wrap; }
        .plan-name { margin: 0; font-size: 1.35rem; color: var(--text-color); font-weight: 700; letter-spacing: -0.5px; }
        .assigned-date { font-size: 0.85rem; color: #94a3b8; font-weight: 500; background: #f8fafc; padding: 4px 10px; border-radius: 6px; }
        .trainer-name { font-size: 0.95rem; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 8px; }

        .header-meta { display: flex; align-items: center; gap: 15px; }
        .badge { padding: 6px 16px; border-radius: 30px; font-size: 0.85rem; font-weight: 600; letter-spacing: 0.3px; text-transform: uppercase; }
        .type-badge { background: #e0f2fe; color: #0284c7; }
        .cal-badge { background: #fee2e2; color: #dc2626; }
        
        .arrow-icon { 
            width: 35px; height: 35px; 
            display: flex; align-items: center; justify-content: center;
            background: #f1f5f9; border-radius: 50%;
            color: #64748b; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            font-size: 1rem;
        }
        .plan-accordion-header:hover .arrow-icon { background: var(--primary-color); color: white; }
        .plan-accordion-header.active .arrow-icon { transform: rotate(180deg); background: var(--primary-color); color: white; }

        .plan-accordion-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s cubic-bezier(0, 1, 0, 1);
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
        }
        .plan-accordion-body .meal-grid {
            padding: 30px 35px;
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 20px;
        }

        .meal-card { 
            background: white; 
            border-radius: 15px; 
            overflow: hidden; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.04); 
            transition: transform 0.3s;
            border: 1px solid #f1f5f9;
        }
        .meal-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        
        .meal-header { 
            padding: 15px 20px; 
            color: white; 
            font-weight: 700; 
            font-size: 16px; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .meal-content { 
            padding: 20px; 
            color: #334155; 
            line-height: 1.6; 
            white-space: pre-wrap; 
            font-family: 'Inter', sans-serif; 
            font-size: 14px; 
        }

        /* Empty State */
        .empty-state { 
            text-align: center; 
            padding: 60px 20px; 
            color: #64748b; 
            background: white; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            max-width: 500px;
            margin: 30px auto;
        }
        .empty-icon-container {
            width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;
        }
        .btn-action {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: 0.3s;
            font-size: 0.9rem;
        }
        .btn-action:hover { background: #0a1f40; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

        @media (max-width: 768px) {
            .plan-accordion-header { flex-direction: column; align-items: flex-start; gap: 20px; padding: 20px; }
            .header-meta { width: 100%; justify-content: space-between; }
            .plan-main-info { flex-direction: column; gap: 5px; }
            .page-hero { padding: 30px; flex-direction: column; text-align: center; gap: 20px; }
            .hero-decoration { display: none; }
            .hero-text p { margin: 0 auto; }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="main-content">
        
        <!-- Page Hero -->
        <div class="page-hero">
            <div class="hero-text">
                <h1>My Diet Plans</h1>
                <p>Track your nutrition with personalized meal plans designed by your coach to fuel your fitness journey.</p>
            </div>
            <div class="hero-decoration">
                <i class="fas fa-apple-alt"></i>
            </div>
        </div>

        <?php if (!empty($allPlans)): ?>
            <div class="plans-container">
                <?php foreach ($allPlans as $plan): 
                    // Parse Meal Details
                    $pMeals = ['breakfast'=>'', 'lunch'=>'', 'dinner'=>'', 'snacks'=>''];
                    if (!empty($plan['meal_details'])) {
                        $decoded = json_decode($plan['meal_details'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $pMeals = array_merge($pMeals, $decoded);
                        } else {
                            $pMeals['breakfast'] = $plan['meal_details'];
                        }
                    }
                ?>
                <div class="plan-accordion-item">
                    <div class="plan-accordion-header" onclick="togglePlan(this)">
                        <div class="header-info">
                            <div class="plan-main-info">
                                <h2 class="plan-name"><?php echo htmlspecialchars($plan['plan_name']); ?></h2>
                                <span class="assigned-date"><i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($plan['created_at'])); ?></span>
                            </div>
                            <div class="trainer-name">
                                <i class="fas fa-user-circle"></i> Designed by Coach <?php echo htmlspecialchars($plan['t_first'] . ' ' . $plan['t_last']); ?>
                            </div>
                        </div>
                        <div class="header-meta">
                            <span class="badge type-badge"><?php echo htmlspecialchars($plan['diet_type'] ?? 'Standard'); ?></span>
                            <span class="badge cal-badge"><i class="fas fa-fire-alt"></i> <?php echo $plan['target_calories']; ?> kcal</span>
                            <i class="fas fa-chevron-down arrow-icon"></i>
                        </div>
                    </div>
                    
                    <div class="plan-accordion-body">
                        <div class="meal-grid">
                            <?php if(!empty($pMeals['breakfast'])): ?>
                            <div class="meal-card">
                                <div class="meal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="fas fa-coffee"></i> Breakfast
                                </div>
                                <div class="meal-content"><?php echo htmlspecialchars($pMeals['breakfast']); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($pMeals['lunch'])): ?>
                            <div class="meal-card">
                                <div class="meal-header" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                    <i class="fas fa-utensils"></i> Lunch
                                </div>
                                <div class="meal-content"><?php echo htmlspecialchars($pMeals['lunch']); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($pMeals['dinner'])): ?>
                            <div class="meal-card">
                                <div class="meal-header" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                                    <i class="fas fa-moon"></i> Dinner
                                </div>
                                <div class="meal-content"><?php echo htmlspecialchars($pMeals['dinner']); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($pMeals['snacks'])): ?>
                            <div class="meal-card">
                                <div class="meal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                    <i class="fas fa-cookie-bite"></i> Snacks
                                </div>
                                <div class="meal-content"><?php echo htmlspecialchars($pMeals['snacks']); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if(empty($pMeals['breakfast']) && empty($pMeals['lunch']) && empty($pMeals['dinner']) && empty($pMeals['snacks'])): ?>
                                 <div class="meal-card" style="grid-column: 1 / -1; text-align: center;">
                                    <div class="meal-header" style="justify-content: center; background: #94a3b8;">
                                        <i class="fas fa-info-circle"></i> Plan Details
                                    </div>
                                    <div class="meal-content">No specific meal details have been added to this plan yet.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon-container">
                    <i class="fas fa-utensils" style="font-size: 40px; color: var(--primary-color);"></i>
                </div>
                <h2 style="color: var(--primary-color); margin-bottom: 10px;">No Diet Plan Assigned</h2>
                <p>It looks like you don't have a nutrition plan yet. <br>Connect with a trainer to get a personalized diet plan.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function togglePlan(header) {
            header.classList.toggle('active');
            const body = header.nextElementSibling;
            
            if (body.style.maxHeight) {
                body.style.maxHeight = null;
            } else {
                body.style.maxHeight = body.scrollHeight + "px";
            }
        }
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>
