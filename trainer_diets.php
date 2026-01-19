<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$trainerId = $_SESSION['user_id'];
$trainerName = $_SESSION['user_name'] ?? 'Trainer';
$trainerInitials = strtoupper(substr($trainerName, 0, 1) . substr(explode(' ', $trainerName)[1] ?? '', 0, 1));

// Determine Mode
$assignTo = isset($_GET['assign_to']) ? intval($_GET['assign_to']) : 0;
$client = null;
$currentDiet = null;
$mealData = ['breakfast'=>'', 'lunch'=>'', 'dinner'=>'', 'snacks'=>''];

if ($assignTo) {
    // Verify Client
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email FROM users WHERE user_id = ? AND assigned_trainer_id = ?");
    $stmt->bind_param("ii", $assignTo, $trainerId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $client = $res->fetch_assoc();
        
        // Fetch Existing Plan
        $dStmt = $conn->prepare("SELECT * FROM trainer_diet_plans WHERE user_id = ? AND trainer_id = ? LIMIT 1");
        $dStmt->bind_param("ii", $assignTo, $trainerId);
        $dStmt->execute();
        $dRes = $dStmt->get_result();
        if ($dRes->num_rows > 0) {
            $currentDiet = $dRes->fetch_assoc();
            // Parse Meals
            if (!empty($currentDiet['meal_details'])) {
                $decoded = json_decode($currentDiet['meal_details'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $mealData = array_merge($mealData, $decoded);
                } else {
                    $mealData['breakfast'] = $currentDiet['meal_details']; // Fallback
                }
            }
        }
        $dStmt->close();
    } else {
        die("Client not found or not assigned to you.");
    }
    $stmt->close();
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delId = intval($_GET['delete_id']);
    // Ensure it belongs to this trainer and is a personal plan (user_id = trainer_id)
    $stmt = $conn->prepare("DELETE FROM trainer_diet_plans WHERE diet_id = ? AND trainer_id = ? AND user_id = ?");
    $stmt->bind_param("iii", $delId, $trainerId, $trainerId);
    if ($stmt->execute()) {
        header("Location: trainer_diets.php?msg=deleted");
        exit();
    }
    $stmt->close();
}

// Handle Save/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Common fields
    $planName = $conn->real_escape_string($_POST['plan_name']);
    $dietType = $conn->real_escape_string($_POST['diet_type']);
    $calories = (int)$_POST['target_calories'];
    
    // Encode Meals
    $meals = [
        'breakfast' => $_POST['meal_breakfast'] ?? '',
        'lunch' => $_POST['meal_lunch'] ?? '',
        'dinner' => $_POST['meal_dinner'] ?? '',
        'snacks' => $_POST['meal_snacks'] ?? ''
    ];
    $details = json_encode($meals);

    if ($_POST['action'] === 'save_diet' && isset($_POST['client_id'])) {
        // Assign to Client
        $clientId = (int)$_POST['client_id'];
        $clientName = $conn->real_escape_string($_POST['client_name_str']);
        
        // Check if updating or creating
        $check = $conn->prepare("SELECT diet_id FROM trainer_diet_plans WHERE user_id = ? AND trainer_id = ?");
        $check->bind_param("ii", $clientId, $trainerId);
        $check->execute();
        $cRes = $check->get_result();
        $exists = $cRes->num_rows > 0;
        $existingId = $exists ? $cRes->fetch_assoc()['diet_id'] : 0;
        $check->close();
        
        if ($exists) {
            $sql = "UPDATE trainer_diet_plans SET plan_name=?, diet_type=?, target_calories=?, meal_details=? WHERE diet_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisi", $planName, $dietType, $calories, $details, $existingId);
        } else {
            $sql = "INSERT INTO trainer_diet_plans (trainer_id, user_id, client_name, plan_name, diet_type, target_calories, meal_details) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssis", $trainerId, $clientId, $clientName, $planName, $dietType, $calories, $details);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Diet plan assigned successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        $stmt->close();
        exit();

    } elseif ($_POST['action'] === 'save_personal_diet') {
        // Save Personal/Showcase Plan
        // user_id = trainer_id
        $clientId = $trainerId;
        $dietId = isset($_POST['diet_id']) ? intval($_POST['diet_id']) : 0;
        // Use a distinct client name to identify
        $clientName = "Personal Showcase"; 

        if ($dietId > 0) {
            // Update
            $sql = "UPDATE trainer_diet_plans SET plan_name=?, diet_type=?, target_calories=?, meal_details=? WHERE diet_id=? AND trainer_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisii", $planName, $dietType, $calories, $details, $dietId, $trainerId);
        } else {
            // New
            $sql = "INSERT INTO trainer_diet_plans (trainer_id, user_id, client_name, plan_name, diet_type, target_calories, meal_details) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssis", $trainerId, $clientId, $clientName, $planName, $dietType, $calories, $details);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Personal diet saved successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        $stmt->close();
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diet Plans - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #0F2C59; --secondary-color: #DAC0A3; --bg-color: #F8F9FA; --text-color: #333; --border-color: #E9ECEF; --sidebar-bg: #fff; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-color); color: var(--text-color); display: flex; min-height: 100vh; margin: 0; }
        
        .sidebar { width: 260px; background: var(--sidebar-bg); border-right: 1px solid var(--border-color); position: fixed; height: 100vh; z-index: 100; display: flex; flex-direction: column; }
        .brand-logo { padding: 30px; font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 24px; color: var(--primary-color); text-decoration: none; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border-color); }
        .sidebar-menu { padding: 20px 0; flex: 1; }
        .menu-item { display: flex; align-items: center; gap: 12px; padding: 12px 30px; color: #6C757D; text-decoration: none; font-weight: 500; }
        .menu-item:hover, .menu-item.active { color: var(--primary-color); background: rgba(15, 44, 89, 0.05); border-left: 3px solid var(--primary-color); }
        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-title h2 { font-family: 'Outfit', sans-serif; font-size: 28px; color: #1e293b; margin-bottom: 5px; }
        .header-title p { color: #64748b; }
        
        .card { background: white; border-radius: 15px; border: 1px solid var(--border-color); padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 500; color: #334155; }
        .form-control { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; transition: 0.2s; }
        .form-control:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(15, 44, 89, 0.1); }
        
        .btn-primary { background: var(--primary-color); color: white; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary:hover { background: #0a1f40; }
        
        .back-link { color: #64748b; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px; }
        .back-link:hover { color: var(--primary-color); }
        
        .meal-section { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px; }
        .meal-box { background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #f1f5f9; }
        .meal-title { font-weight: 600; color: var(--primary-color); margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        textarea.meal-input { min-height: 100px; resize: vertical; border-color: #e2e8f0; }
        
        .client-badge { background: #eef2ff; color: var(--primary-color); padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-left: 10px; }

        /* Sidebar Profile Style to Match Requirements */
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
        }
        .user-info h4 {
            font-size: 15px;
            margin: 0;
            color: #333;
            font-weight: 600;
        }
        .user-info p {
            font-size: 11px;
            margin: 0;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .logout-btn {
            margin-left: auto;
            color: #64748b;
            text-decoration: none;
            font-size: 16px;
            transition: 0.2s;
        }
        .logout-btn:hover {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <a href="home.php" class="brand-logo"><i class="fas fa-dumbbell"></i> FitNova</a>
        <nav class="sidebar-menu">
            <a href="trainer_dashboard.php" class="menu-item"><i class="fas fa-home"></i> Overview</a>
            <a href="trainer_clients.php" class="menu-item"><i class="fas fa-users"></i> My Clients</a>
            <a href="trainer_schedule.php" class="menu-item"><i class="fas fa-calendar-alt"></i> Schedule</a>
            <a href="trainer_workouts.php" class="menu-item"><i class="fas fa-clipboard-list"></i> Workout Plans</a>
            <a href="trainer_diets.php" class="menu-item active">
                <i class="fas fa-utensils"></i> Diet Plans
            </a>
            <a href="trainer_achievements.php" class="menu-item">
                <i class="fas fa-medal"></i> Achievements
            </a>
            <a href="trainer_performance.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Performance
            </a>
            <a href="trainer_messages.php" class="menu-item"><i class="fas fa-envelope"></i> Messages</a>
            <a href="client_profile_setup.php" class="menu-item"><i class="fas fa-user-circle"></i> Profile</a>
        </nav>
        
        <div class="user-profile-preview">
            <div class="user-avatar-sm"><?php echo $trainerInitials; ?></div>
            <div class="user-info">
                <h4><?php echo htmlspecialchars($trainerName); ?></h4>
                <p>Expert Trainer</p>
            </div>
            <a href="logout.php" class="logout-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <?php if ($assignTo): ?>
            <!-- ... Client Assignment View (Existing) ... -->
            <a href="trainer_clients.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Clients</a>
            
            <div class="header-section" style="justify-content: center; text-align: center;">
                <div class="header-title">
                    <h2>Assign Diet Plan <span class="client-badge"><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></span></h2>
                    <p>Customize specific meal requirements and nutrition goals.</p>
                </div>
            </div>

            <div class="card">
                <form id="dietForm" onsubmit="saveDiet(event, 'save_diet')">
                    <input type="hidden" name="client_id" value="<?php echo $client['user_id']; ?>">
                    <input type="hidden" name="client_name_str" value="<?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Plan Name</label>
                        <input type="text" name="plan_name" class="form-control" placeholder="e.g. Weight Loss Phase 1" 
                               value="<?php echo $currentDiet ? htmlspecialchars($currentDiet['plan_name']) : 'Custom personalized Diet'; ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Diet Type / Philosophy</label>
                            <select name="diet_type" class="form-control">
                                <option value="Standard" <?php if($currentDiet && $currentDiet['diet_type'] == 'Standard') echo 'selected'; ?>>Standard Balanced</option>
                                <option value="Keto" <?php if($currentDiet && $currentDiet['diet_type'] == 'Keto') echo 'selected'; ?>>Keto / Low Carb</option>
                                <option value="Vegan" <?php if($currentDiet && $currentDiet['diet_type'] == 'Vegan') echo 'selected'; ?>>Vegan</option>
                                <option value="High Protein" <?php if($currentDiet && $currentDiet['diet_type'] == 'High Protein') echo 'selected'; ?>>High Protein</option>
                                <option value="Intermittent Fasting" <?php if($currentDiet && $currentDiet['diet_type'] == 'Intermittent Fasting') echo 'selected'; ?>>Intermittent Fasting</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Daily Calorie Target (kcal)</label>
                            <input type="number" name="target_calories" class="form-control" placeholder="e.g. 2000" 
                                   value="<?php echo $currentDiet ? htmlspecialchars($currentDiet['target_calories']) : '2000'; ?>" required>
                        </div>
                    </div>

                    <h4 style="margin-top: 30px; margin-bottom: 20px; border-bottom: 1px solid #e9ecef; padding-bottom: 10px; color: #1e293b;">Daily Meal Breakdown</h4>
                    
                    <div class="meal-section">
                        <div class="meal-box">
                            <div class="meal-title"><i class="fas fa-coffee"></i> Breakfast</div>
                            <textarea name="meal_breakfast" class="form-control meal-input" placeholder="e.g. Oatmeal with berries, Black coffee..."><?php echo htmlspecialchars($mealData['breakfast']); ?></textarea>
                        </div>
                        <div class="meal-box">
                            <div class="meal-title"><i class="fas fa-utensils"></i> Lunch</div>
                            <textarea name="meal_lunch" class="form-control meal-input" placeholder="e.g. Grilled chicken breast, Quinoa salad..."><?php echo htmlspecialchars($mealData['lunch']); ?></textarea>
                        </div>
                        <div class="meal-box">
                            <div class="meal-title"><i class="fas fa-moon"></i> Dinner</div>
                            <textarea name="meal_dinner" class="form-control meal-input" placeholder="e.g. Salmon with asparagus, Brown rice..."><?php echo htmlspecialchars($mealData['dinner']); ?></textarea>
                        </div>
                        <div class="meal-box">
                            <div class="meal-title"><i class="fas fa-apple-alt"></i> Snacks / Supplements</div>
                            <textarea name="meal_snacks" class="form-control meal-input" placeholder="e.g. Protein shake, Almonds, Greek yogurt..."><?php echo htmlspecialchars($mealData['snacks']); ?></textarea>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 30px;">
                        <button type="submit" class="btn-primary" id="saveBtn">
                            <i class="fas fa-save"></i> <?php echo $currentDiet ? 'Update Plan' : 'Assign Plan'; ?>
                        </button>
                    </div>
                </form>
            </div>

        <?php elseif(isset($_GET['create_new']) || isset($_GET['edit_id'])): ?>
            <!-- ... Create/Edit Personal Diet Mode ... -->
            <?php
            $editMode = false;
            $pPlan = null;
            $pData = ['breakfast'=>'', 'lunch'=>'', 'dinner'=>'', 'snacks'=>''];

            if (isset($_GET['edit_id'])) {
                $editId = intval($_GET['edit_id']);
                $stmt = $conn->prepare("SELECT * FROM trainer_diet_plans WHERE diet_id=? AND trainer_id=? AND user_id=?");
                $stmt->bind_param("iii", $editId, $trainerId, $trainerId);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows > 0) {
                    $editMode = true;
                    $pPlan = $res->fetch_assoc();
                    if (!empty($pPlan['meal_details'])) {
                        $decoded = json_decode($pPlan['meal_details'], true);
                        if (is_array($decoded)) $pData = array_merge($pData, $decoded);
                        else $pData['breakfast'] = $pPlan['meal_details'];
                    }
                }
                $stmt->close();
            }
            ?>
            <a href="trainer_diets.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Plans</a>
            <div class="header-section">
                <div class="header-title">
                    <h2><?php echo $editMode ? 'Edit' : 'Create'; ?> Personal Diet Plan</h2>
                    <p>Share your personal nutrition strategy with your clients.</p>
                </div>
            </div>

            <div class="card">
                <form id="dietFormPersonal" onsubmit="saveDiet(event, 'save_personal_diet')">
                     <?php if($editMode): ?>
                        <input type="hidden" name="diet_id" value="<?php echo $pPlan['diet_id']; ?>">
                     <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Plan Name</label>
                        <input type="text" name="plan_name" class="form-control" placeholder="e.g. My Maintenance Diet" 
                               value="<?php echo $pPlan ? htmlspecialchars($pPlan['plan_name']) : ''; ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Diet Type / Philosophy</label>
                            <select name="diet_type" class="form-control">
                                <option value="Standard" <?php if($pPlan && $pPlan['diet_type'] == 'Standard') echo 'selected'; ?>>Standard Balanced</option>
                                <option value="Keto" <?php if($pPlan && $pPlan['diet_type'] == 'Keto') echo 'selected'; ?>>Keto / Low Carb</option>
                                <option value="Vegan" <?php if($pPlan && $pPlan['diet_type'] == 'Vegan') echo 'selected'; ?>>Vegan</option>
                                <option value="High Protein" <?php if($pPlan && $pPlan['diet_type'] == 'High Protein') echo 'selected'; ?>>High Protein</option>
                                <option value="Intermittent Fasting" <?php if($pPlan && $pPlan['diet_type'] == 'Intermittent Fasting') echo 'selected'; ?>>Intermittent Fasting</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Daily Calorie Target (kcal)</label>
                            <input type="number" name="target_calories" class="form-control" placeholder="e.g. 2500" 
                                   value="<?php echo $pPlan ? htmlspecialchars($pPlan['target_calories']) : '2000'; ?>" required>
                        </div>
                    </div>

                    <h4 style="margin-top: 30px; margin-bottom: 20px; border-bottom: 1px solid #e9ecef; padding-bottom: 10px; color: #1e293b;">Typical Daily Meals</h4>
                    
                    <div class="meal-section">
                        <div class="meal-box">
                            <div class="meal-title"><i class="fas fa-coffee"></i> Breakfast</div>
                            <textarea name="meal_breakfast" class="form-control meal-input" placeholder="e.g. Eggs, Avocado toast..."><?php echo htmlspecialchars($pData['breakfast']); ?></textarea>
                        </div>
                        <div class="meal-box">
                            <div class="meal-title"><i class="fas fa-utensils"></i> Lunch</div>
                            <textarea name="meal_lunch" class="form-control meal-input" placeholder="e.g. Chicken salad, brown rice..."><?php echo htmlspecialchars($pData['lunch']); ?></textarea>
                        </div>
                        <div class="meal-box">
                            <div class="meal-title"><i class="fas fa-moon"></i> Dinner</div>
                            <textarea name="meal_dinner" class="form-control meal-input" placeholder="e.g. Fish, steamed veggies..."><?php echo htmlspecialchars($pData['dinner']); ?></textarea>
                        </div>
                        <div class="meal-box">
                            <div class="meal-title"><i class="fas fa-apple-alt"></i> Snacks / Supplements</div>
                            <textarea name="meal_snacks" class="form-control meal-input" placeholder="e.g. Whey protein, nuts..."><?php echo htmlspecialchars($pData['snacks']); ?></textarea>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 30px;">
                        <button type="submit" class="btn-primary" id="saveBtnPersonal">
                            <i class="fas fa-save"></i> Save Plan
                        </button>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <!-- ... List of Personal Plans ... -->
            <div class="header-section">
                <div class="header-title">
                    <h2>My Diet Plans</h2>
                    <p>Manage the nutritional plans you follow. Clients can view these for inspiration.</p>
                </div>
                <a href="trainer_diets.php?create_new=1" class="btn-primary">
                    <i class="fas fa-plus"></i> Add New Plan
                </a>
            </div>
            
            <?php
            // Fetch PERSONAL plans (user_id = trainer_id)
            $plansSql = "SELECT * FROM trainer_diet_plans WHERE trainer_id = ? AND user_id = ? ORDER BY created_at DESC";
            $pStmt = $conn->prepare($plansSql);
            $pStmt->bind_param("ii", $trainerId, $trainerId);
            $pStmt->execute();
            $myPlans = $pStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $pStmt->close();
            ?>

            <?php if (count($myPlans) > 0): ?>
                <div class="card" style="padding: 0; overflow: hidden; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead style="background: #f8fafc; color: var(--primary-color); border-bottom: 2px solid #e2e8f0;">
                                <tr>
                                    <th style="padding: 16px 24px;">Plan Name</th>
                                    <th style="padding: 16px 24px;">Type</th>
                                    <th style="padding: 16px 24px;">Calories</th>
                                    <th style="padding: 16px 24px; text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($myPlans as $plan): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 16px 24px; font-weight: 600; color: var(--primary-color);">
                                        <?php echo htmlspecialchars($plan['plan_name']); ?>
                                        <div style="font-size: 12px; color: #888; font-weight: 400;">Created: <?php echo date('M d, Y', strtotime($plan['created_at'])); ?></div>
                                    </td>
                                    <td style="padding: 16px 24px;">
                                        <span style="background: #f0fdf4; color: #166534; padding: 4px 10px; border-radius: 15px; font-size: 12px; font-weight: 600;">
                                            <?php echo htmlspecialchars($plan['diet_type']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px 24px; color: #666;">
                                        <?php echo htmlspecialchars($plan['target_calories']); ?> kcal
                                    </td>
                                    <td style="padding: 16px 24px; text-align: right;">
                                        <a href="trainer_diets.php?edit_id=<?php echo $plan['diet_id']; ?>" style="color: var(--primary-color); margin-right: 15px;"><i class="fas fa-edit"></i></a>
                                        <a href="trainer_diets.php?delete_id=<?php echo $plan['diet_id']; ?>" onclick="return confirm('Delete this plan?')" style="color: #ef4444;"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                </div>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 50px;">
                    <i class="fas fa-apple-alt" style="font-size: 48px; color: var(--secondary-color); margin-bottom: 20px;"></i>
                    <h3>No Personal Diet Plans Yet</h3>
                    <p style="color: #64748b; margin-bottom: 20px;">Add the nutritional strategies you follow so your clients can learn from you.</p>
                    <a href="trainer_diets.php?create_new=1" class="btn-primary">Create Your First Plan</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function saveDiet(e, actionType) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            formData.append('action', actionType);

            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            fetch('trainer_diets.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#0F2C59'
                    }).then(() => {
                         if (actionType === 'save_diet') {
                            window.location.reload(); 
                         } else {
                            window.location.href = 'trainer_diets.php';
                         }
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
                btn.innerHTML = originalText;
                btn.disabled = false;
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
