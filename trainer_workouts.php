<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$trainerId = $_SESSION['user_id'];

// Determine Mode
$assignTo = isset($_GET['assign_to']) ? intval($_GET['assign_to']) : 0;
$client = null;
$currentPlan = null;
$workoutData = ['level_1'=>'', 'level_2'=>'', 'level_3'=>''];

if ($assignTo) {
    // Verify Client
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email FROM users WHERE user_id = ? AND assigned_trainer_id = ?");
    $stmt->bind_param("ii", $assignTo, $trainerId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $client = $res->fetch_assoc();
        
        // Fetch Existing Plan
        $dStmt = $conn->prepare("SELECT * FROM trainer_workouts WHERE user_id = ? AND trainer_id = ? LIMIT 1");
        $dStmt->bind_param("ii", $assignTo, $trainerId);
        $dStmt->execute();
        $dRes = $dStmt->get_result();
        if ($dRes->num_rows > 0) {
            $currentPlan = $dRes->fetch_assoc();
            // Parse Levels
            if (!empty($currentPlan['exercises'])) {
                $decoded = json_decode($currentPlan['exercises'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $workoutData = array_merge($workoutData, $decoded);
                } else {
                    $workoutData['level_1'] = $currentPlan['exercises']; // Fallback
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
    $stmt = $conn->prepare("DELETE FROM trainer_workouts WHERE workout_id = ? AND trainer_id = ? AND user_id = ?");
    $stmt->bind_param("iii", $delId, $trainerId, $trainerId);
    if ($stmt->execute()) {
        header("Location: trainer_workouts.php?msg=deleted");
        exit();
    }
    $stmt->close();
}

// Handle Save/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $planName = $conn->real_escape_string($_POST['plan_name']);
    $difficulty = $conn->real_escape_string($_POST['difficulty']);
    // Default duration removed or set to a standard value if column is required (e.g., 4)
    // For now we will just use a default of 4 in the background if the DB requires it, or remove it.
    // Assuming DB column still exists, let's just default it to avoid breaking changes or remove if we did an ALTER.
    // The user asked to remove it, implies visual removal. I will default it to 4 to prevent SQL errors if column is NOT NULL.
    $weeks = 4; 
    $freq = isset($_POST['days_per_week']) ? (int)$_POST['days_per_week'] : 3;
    
    // Encode Levels
    $levels = [
        'level_1' => $_POST['level_1'] ?? '',
        'level_2' => $_POST['level_2'] ?? '',
        'level_3' => $_POST['level_3'] ?? ''
    ];
    $details = json_encode($levels);

    if ($_POST['action'] === 'save_workout' && isset($_POST['client_id'])) {
        // Assign to Client
        $clientId = (int)$_POST['client_id'];
        $clientName = $conn->real_escape_string($_POST['client_name_str']);
        
        $check = $conn->prepare("SELECT workout_id FROM trainer_workouts WHERE user_id = ? AND trainer_id = ?");
        $check->bind_param("ii", $clientId, $trainerId);
        $check->execute();
        $cRes = $check->get_result();
        $exists = $cRes->num_rows > 0;
        $existingId = $exists ? $cRes->fetch_assoc()['workout_id'] : 0;
        $check->close();
        
        if ($exists) {
            $sql = "UPDATE trainer_workouts SET plan_name=?, difficulty=?, days_per_week=?, exercises=? WHERE workout_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisi", $planName, $difficulty, $freq, $details, $existingId);
        } else {
            $sql = "INSERT INTO trainer_workouts (trainer_id, user_id, client_name, plan_name, difficulty, duration_weeks, days_per_week, exercises) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssiis", $trainerId, $clientId, $clientName, $planName, $difficulty, $weeks, $freq, $details);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Client plan updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        $stmt->close();
        exit();

    } elseif ($_POST['action'] === 'save_personal_plan') {
        // Save Personal/Showcase Plan
        // user_id = trainer_id
        $clientId = $trainerId;
        $workoutId = isset($_POST['workout_id']) ? intval($_POST['workout_id']) : 0;
        // Use a distinct client name to identify
        $clientName = "Personal Showcase"; 

        if ($workoutId > 0) {
            // Update
            $sql = "UPDATE trainer_workouts SET plan_name=?, difficulty=?, days_per_week=?, exercises=? WHERE workout_id=? AND trainer_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisii", $planName, $difficulty, $freq, $details, $workoutId, $trainerId);
        } else {
            // New
            $sql = "INSERT INTO trainer_workouts (trainer_id, user_id, client_name, plan_name, difficulty, duration_weeks, days_per_week, exercises) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssiis", $trainerId, $clientId, $clientName, $planName, $difficulty, $weeks, $freq, $details);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Personal routine saved successfully']);
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
    <title>Workout Plans - FitNova</title>
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
        
        .card { background: white; border-radius: 15px; border: 1px solid var(--border-color); padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 500; color: #334155; }
        .form-control { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; transition: 0.2s; }
        
        .btn-primary { background: var(--primary-color); color: white; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block;}
        .btn-outline { background: transparent; color: var(--primary-color); border: 2px solid var(--primary-color); padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; }
        
        .level-section { margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
        .level-box { background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #f1f5f9; margin-bottom: 20px; }
        .level-title { font-weight: 700; color: var(--primary-color); margin-bottom: 15px; display: flex; align-items: center; gap: 10px; font-size: 16px; }
        textarea.meal-input { min-height: 120px; resize: vertical; border-color: #e2e8f0; width: 100%; font-family: inherit; padding: 10px; border-radius: 6px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <a href="home.php" class="brand-logo"><i class="fas fa-dumbbell"></i> FitNova</a>
        <nav class="sidebar-menu">
            <a href="trainer_dashboard.php" class="menu-item"><i class="fas fa-home"></i> Overview</a>
            <a href="trainer_clients.php" class="menu-item"><i class="fas fa-users"></i> My Clients</a>
            <a href="trainer_schedule.php" class="menu-item"><i class="fas fa-calendar-alt"></i> Schedule</a>
            <a href="trainer_workouts.php" class="menu-item active"><i class="fas fa-clipboard-list"></i> Workout Plans</a>
            <a href="trainer_diets.php" class="menu-item"><i class="fas fa-utensils"></i> Diet Plans</a>
            <a href="trainer_achievements.php" class="menu-item"><i class="fas fa-medal"></i> Achievements</a>
            <a href="trainer_performance.php" class="menu-item"><i class="fas fa-chart-line"></i> Performance</a>
            <a href="trainer_messages.php" class="menu-item"><i class="fas fa-envelope"></i> Messages</a>
            <a href="client_profile_setup.php" class="menu-item"><i class="fas fa-user-circle"></i> Profile</a>
        </nav>
        <div class="user-profile-preview" style="padding: 20px; border-top: 1px solid #E9ECEF; display: flex; align-items: center; gap: 12px; margin-top: auto; background: #fff;">
            <div style="width: 40px; height: 40px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                <?php echo $trainerInitials; ?>
            </div>
            <div>
                <h4 style="font-size:15px; margin:0; color:#333; font-weight:600;"><?php echo htmlspecialchars($trainerName); ?></h4>
                <p style="font-size:11px; margin:0; color:#64748b; text-transform:uppercase; font-weight:600; letter-spacing:0.5px;">Expert Trainer</p>
            </div>
            <a href="logout.php" title="Logout" style="margin-left: auto; color: #64748b; text-decoration: none; font-size: 16px; transition: 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#64748b'">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <?php if ($assignTo): ?>
            <!-- ... Client Assignment View (Keep Existing) ... -->
            <a href="trainer_clients.php" style="color: #64748b; text-decoration: none; margin-bottom: 20px; display: inline-block;">
                <i class="fas fa-arrow-left"></i> Back to Clients
            </a>
            
            <div class="header-section" style="justify-content: center; text-align: center;">
                <div class="header-title">
                    <h2>Assign Workout Plan <span style="font-size: 0.6em; background: #eef2ff; padding: 5px 12px; border-radius: 20px; color: var(--primary-color); vertical-align: middle;">
                        <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>
                    </span></h2>
                    <p>Customize training levels and routines.</p>
                </div>
            </div>

            <!-- Fetch Client Profile Logic -->
            <?php
                // Fetch Profile Data
                $profSql = "SELECT * FROM client_profiles WHERE user_id = ?";
                $pStmt = $conn->prepare($profSql);
                $pStmt->bind_param("i", $assignTo);
                $pStmt->execute();
                $profRes = $pStmt->get_result();
                $clientProfile = $profRes->fetch_assoc();
                $pStmt->close();
                
                // Calculate Age
                $age = 'N/A';
                if ($clientProfile && !empty($clientProfile['dob'])) {
                    $dob = new DateTime($clientProfile['dob']);
                    $now = new DateTime();
                    $age = $now->diff($dob)->y . ' yrs';
                }
            ?>

            <!-- Client Profile Snapshot -->
            <div class="card" style="margin-bottom: 25px; border-left: 5px solid var(--secondary-color);">
                <?php if ($clientProfile): ?>
                    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; color: var(--primary-color); border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        <i class="fas fa-id-card-alt"></i> Client Snapshot
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; row-gap: 25px;">
                        <!-- Row 1 -->
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px;">Basic Stats</div>
                            <div style="font-weight: 700; font-size: 15px; color: #333;">
                                <?php echo $age; ?> <span style="color: #ccc;">|</span>
                                <?php echo htmlspecialchars(ucfirst($clientProfile['gender'] ?? '-')); ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px;">Body Composition</div>
                            <div style="font-weight: 700; font-size: 15px; color: #333;">
                                <?php echo $clientProfile['height_cm'] ? $clientProfile['height_cm'] . ' cm' : '-'; ?> <span style="color: #ccc;">|</span>
                                <?php echo $clientProfile['weight_kg'] ? $clientProfile['weight_kg'] . ' kg' : '-'; ?>
                            </div>
                        </div>
                        <div>
                             <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px;">Target Weight</div>
                             <div style="font-weight: 700; font-size: 15px; color: var(--success-color);">
                                <i class="fas fa-bullseye" style="font-size: 12px;"></i>
                                <?php echo $clientProfile['target_weight_kg'] ? $clientProfile['target_weight_kg'] . ' kg' : 'Not Set'; ?>
                             </div>
                        </div>
                        <div>
                             <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px;">Primary Goal</div>
                             <div style="font-weight: 700; font-size: 14px; color: var(--primary-color);">
                                <i class="fas fa-flag" style="font-size: 12px; color: var(--accent-color);"></i>
                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $clientProfile['primary_goal'] ?? 'Not Set'))); ?>
                             </div>
                        </div>
                        
                        <!-- Row 2 -->
                        <div>
                             <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px;">Availability</div>
                             <div style="font-weight: 600; font-size: 14px; color: #333;">
                                <i class="far fa-calendar-check"></i> <?php echo $clientProfile['workout_days_per_week'] ? $clientProfile['workout_days_per_week'] . ' Days/Week' : '-'; ?>
                             </div>
                        </div>
                        <div>
                             <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px;">Equipment</div>
                             <div style="font-weight: 600; font-size: 14px; color: #333;">
                                <i class="fas fa-dumbbell"></i> <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $clientProfile['equipment_access'] ?? '-'))); ?>
                             </div>
                        </div>
                         <div style="grid-column: span 2;">
                             <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px;">Activity Level</div>
                             <div style="font-weight: 600; font-size: 14px; color: var(--primary-color);">
                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $clientProfile['activity_level'] ?? 'Unknown'))); ?>
                             </div>
                        </div>

                        <!-- Row 3: Health Alerts spanning full width if needed -->
                        <div style="grid-column: 1 / -1; border-top: 1px dashed #e2e8f0; padding-top: 15px; margin-top: 5px;">
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 6px;">Health & Injuries Alert</div>
                            <div style="font-weight: 500; font-size: 14px; color: #ef4444;">
                                <?php 
                                    $alerts = [];
                                    if (!empty($clientProfile['injuries']) && strtolower($clientProfile['injuries']) !== 'none' && strtolower($clientProfile['injuries']) !== 'nothing') {
                                        $alerts[] = '<span style="background: #fef2f2; padding: 5px 10px; border-radius: 6px; border: 1px solid #fecaca;"><i class="fas fa-ambulance"></i> <strong>Injury:</strong> ' . htmlspecialchars($clientProfile['injuries']) . '</span>';
                                    }
                                    if (!empty($clientProfile['medical_conditions']) && strtolower($clientProfile['medical_conditions']) !== 'none' && strtolower($clientProfile['medical_conditions']) !== 'nothing') {
                                        $alerts[] = '<span style="background: #fff7ed; padding: 5px 10px; border-radius: 6px; border: 1px solid #fed7aa; color: #c2410c;"><i class="fas fa-heartbeat"></i> <strong>Condition:</strong> ' . htmlspecialchars($clientProfile['medical_conditions']) . '</span>';
                                    }
                                    
                                    if (empty($alerts)) {
                                        echo '<span style="color: #10b981; background: #f0fdf4; padding: 5px 10px; border-radius: 6px; border: 1px solid #bbf7d0;"><i class="fas fa-check-circle"></i> No reported injuries or conditions</span>';
                                    } else {
                                        echo '<div style="display: flex; flex-wrap: wrap; gap: 10px;">' . implode('', $alerts) . '</div>';
                                    }
                                ?>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div style="text-align: center; padding: 20px; color: #64748b;">
                        <i class="fas fa-user-clock" style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px;"></i>
                        <p style="margin: 0;">This client has not set up their profile yet.</p>
                        <p style="font-size: 12px; margin-top: 5px;">Ask them to complete the "Profile Setup" in their dashboard.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <form id="workoutFormClient" onsubmit="saveWorkout(event, 'save_workout')">
                    <input type="hidden" name="client_id" value="<?php echo $client['user_id']; ?>">
                    <input type="hidden" name="client_name_str" value="<?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Plan Name</label>
                        <input type="text" name="plan_name" class="form-control" placeholder="e.g. Strength Phase 1" 
                               value="<?php echo $currentPlan ? htmlspecialchars($currentPlan['plan_name']) : 'Personalized Workout Plan'; ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Difficulty / Goal</label>
                            <select name="difficulty" class="form-control">
                                <option value="Beginner" <?php if($currentPlan && $currentPlan['difficulty'] == 'Beginner') echo 'selected'; ?>>Beginner / Weight Loss</option>
                                <option value="Intermediate" <?php if($currentPlan && $currentPlan['difficulty'] == 'Intermediate') echo 'selected'; ?>>Intermediate / Toning</option>
                                <option value="Advanced" <?php if($currentPlan && $currentPlan['difficulty'] == 'Advanced') echo 'selected'; ?>>Advanced / Muscle Gain</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Frequency (Days/Week)</label>
                            <!-- Auto-fill with client availability if new plan, otherwise use existing -->
                            <?php 
                                $defaultFreq = $clientProfile['workout_days_per_week'] ?? 3; 
                                $freqVal = $currentPlan ? ($currentPlan['days_per_week'] ?? $defaultFreq) : $defaultFreq;
                            ?>
                            <input type="number" name="days_per_week" class="form-control" placeholder="e.g. 3" 
                                   value="<?php echo htmlspecialchars($freqVal); ?>" min="1" max="7" required>
                        </div>
                    </div>

                    <div class="level-section">
                        <div class="level-box" style="border-left: 5px solid #10b981;">
                            <div class="level-title"><i class="fas fa-seedling"></i> Beginner Level</div>
                            <textarea name="level_1" class="meal-input" placeholder="List exercises, sets, reps for beginners..."><?php echo htmlspecialchars($workoutData['level_1']); ?></textarea>
                        </div>
                        <div class="level-box" style="border-left: 5px solid #f59e0b;">
                            <div class="level-title"><i class="fas fa-fire"></i> Intermediate Level</div>
                            <textarea name="level_2" class="meal-input" placeholder="Progression exercises..."><?php echo htmlspecialchars($workoutData['level_2']); ?></textarea>
                        </div>
                        <div class="level-box" style="border-left: 5px solid #ef4444;">
                            <div class="level-title"><i class="fas fa-dumbbell"></i> Advanced Level</div>
                            <textarea name="level_3" class="meal-input" placeholder="Advanced techniques..."><?php echo htmlspecialchars($workoutData['level_3']); ?></textarea>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 30px;">
                        <button type="submit" class="btn-primary" id="saveBtnClient">
                            <i class="fas fa-save"></i> <?php echo $currentPlan ? 'Update Plan' : 'Assign Plan'; ?>
                        </button>
                    </div>
                </form>
            </div>

        <?php elseif(isset($_GET['create_new']) || isset($_GET['edit_id'])): ?>
            <!-- ... Create/Edit Personal Plan Mode ... -->
            <?php
            $editMode = false;
            $pPlan = null;
            $pData = ['level_1'=>'', 'level_2'=>'', 'level_3'=>''];

            if (isset($_GET['edit_id'])) {
                $editId = intval($_GET['edit_id']);
                $stmt = $conn->prepare("SELECT * FROM trainer_workouts WHERE workout_id=? AND trainer_id=? AND user_id=?");
                $stmt->bind_param("iii", $editId, $trainerId, $trainerId);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows > 0) {
                    $editMode = true;
                    $pPlan = $res->fetch_assoc();
                    if (!empty($pPlan['exercises'])) {
                        $dec = json_decode($pPlan['exercises'], true);
                        if (is_array($dec)) $pData = array_merge($pData, $dec);
                        else $pData['level_1'] = $pPlan['exercises'];
                    }
                }
                $stmt->close();
            }
            ?>
            <a href="trainer_workouts.php" style="color: #64748b; text-decoration: none; margin-bottom: 20px; display: inline-block;">
                <i class="fas fa-arrow-left"></i> Back to Plans
            </a>
            <div class="header-section">
                <div class="header-title">
                    <h2><?php echo $editMode ? 'Edit' : 'Create'; ?> Personal Routine</h2>
                    <p>Share your personal workout strategy with your clients.</p>
                </div>
            </div>

            <div class="card">
                <form id="workoutFormPersonal" onsubmit="saveWorkout(event, 'save_personal_plan')">
                     <?php if($editMode): ?>
                        <input type="hidden" name="workout_id" value="<?php echo $pPlan['workout_id']; ?>">
                     <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Routine Name</label>
                        <input type="text" name="plan_name" class="form-control" placeholder="e.g. My Morning Shred" 
                               value="<?php echo $pPlan ? htmlspecialchars($pPlan['plan_name']) : ''; ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Target / Focus</label>
                            <select name="difficulty" class="form-control">
                                <option value="Beginner" <?php if($pPlan && $pPlan['difficulty'] == 'Beginner') echo 'selected'; ?>>Beginner Friendly</option>
                                <option value="Intermediate" <?php if($pPlan && $pPlan['difficulty'] == 'Intermediate') echo 'selected'; ?>>Intermediate Intensity</option>
                                <option value="Advanced" <?php if($pPlan && $pPlan['difficulty'] == 'Advanced') echo 'selected'; ?>>High Performance (Advanced)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Frequency (Days/Week)</label>
                            <input type="number" name="days_per_week" class="form-control" placeholder="e.g. 5" 
                                   value="<?php echo $pPlan ? ($pPlan['days_per_week'] ?? 5) : '5'; ?>" min="1" max="7" required>
                        </div>
                    </div>

                    <div class="level-section">
                         <div class="level-box">
                            <div class="level-title"><i class="fas fa-list-ul"></i> Routine Details</div>
                            <p style="font-size: 13px; color: #666; margin-bottom: 10px;">Describe the exercises, sets, and reps. You can break it down by levels if you want to offer scaling options.</p>
                            
                            <label class="form-label" style="font-size: 13px;">Main Routine / Level 1</label>
                            <textarea name="level_1" class="meal-input" placeholder="e.g. 3x10 Squats, 3x12 Pushups..."><?php echo htmlspecialchars($pData['level_1']); ?></textarea>
                            
                            <label class="form-label" style="font-size: 13px; margin-top: 15px;">Progression / Level 2 (Optional)</label>
                            <textarea name="level_2" class="meal-input" placeholder="e.g. Add weight, reduce rest..."><?php echo htmlspecialchars($pData['level_2']); ?></textarea>
                            
                            <label class="form-label" style="font-size: 13px; margin-top: 15px;">Advanced / Level 3 (Optional)</label>
                            <textarea name="level_3" class="meal-input" placeholder="Exclude if not applicable"><?php echo htmlspecialchars($pData['level_3']); ?></textarea>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 30px;">
                        <button type="submit" class="btn-primary" id="saveBtnPersonal">
                            <i class="fas fa-save"></i> Save Routine
                        </button>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <!-- ... List of Personal Plans ... -->
            <div class="header-section">
                <div class="header-title">
                    <h2>My Workout Routines</h2>
                    <p>Manage the plans you follow. Clients can view these for inspiration.</p>
                </div>
                <a href="trainer_workouts.php?create_new=1" class="btn-primary">
                    <i class="fas fa-plus"></i> Add New Routine
                </a>
            </div>
            
            <?php
            // Fetch PERSONAL plans (user_id = trainer_id)
            $plansSql = "SELECT * FROM trainer_workouts WHERE trainer_id = ? AND user_id = ? ORDER BY created_at DESC";
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
                                    <th style="padding: 16px 24px;">Routine Name</th>
                                    <th style="padding: 16px 24px;">Focus</th>
                                    <th style="padding: 16px 24px;">Duration</th>
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
                                        <span style="background: #eef2ff; color: #4338ca; padding: 4px 10px; border-radius: 15px; font-size: 12px; font-weight: 600;">
                                            <?php echo htmlspecialchars($plan['difficulty']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px 24px; color: #666;">
                                        <?php echo htmlspecialchars($plan['duration_weeks']); ?> Weeks
                                    </td>
                                    <td style="padding: 16px 24px; text-align: right;">
                                        <a href="trainer_workouts.php?edit_id=<?php echo $plan['workout_id']; ?>" style="color: var(--primary-color); margin-right: 15px;"><i class="fas fa-edit"></i></a>
                                        <a href="trainer_workouts.php?delete_id=<?php echo $plan['workout_id']; ?>" onclick="return confirm('Delete this routine?')" style="color: #ef4444;"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                </div>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 50px;">
                    <i class="fas fa-running" style="font-size: 48px; color: var(--secondary-color); margin-bottom: 20px;"></i>
                    <h3>No Personal Routines Yet</h3>
                    <p style="color: #64748b; margin-bottom: 20px;">Add the workout plans you follow so your clients can see what drives your success.</p>
                    <a href="trainer_workouts.php?create_new=1" class="btn-primary">Create Your First Routine</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function saveWorkout(e, actionType) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            formData.append('action', actionType);

            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            fetch('trainer_workouts.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#0F2C59'
                    }).then(() => {
                        // Reload or redirect based on action
                         if (actionType === 'save_workout') {
                            // Stay or go back? Usually stay or reload.
                            window.location.reload(); 
                         } else {
                            window.location.href = 'trainer_workouts.php';
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
