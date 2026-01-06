<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$trainerId = $_SESSION['user_id'];
$trainerName = $_SESSION['user_name'];
$trainerInitials = strtoupper(substr($trainerName, 0, 1) . substr(explode(' ', $trainerName)[1] ?? '', 0, 1));

// Handle AJAX updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_workout') {
    $workoutId = $_POST['workout_id'];
    $planName = $conn->real_escape_string($_POST['plan_name']);
    $clientName = $conn->real_escape_string($_POST['client_name']);
    $weeks = (int)$_POST['duration_weeks'];
    $exercises = $conn->real_escape_string($_POST['exercises']);
    
    $updateSql = "UPDATE trainer_workouts SET plan_name = ?, client_name = ?, duration_weeks = ?, exercises = ? WHERE workout_id = ? AND trainer_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssisii", $planName, $clientName, $weeks, $exercises, $workoutId, $trainerId);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    $stmt->close();
    exit();
}

// Fetch Workout Plans
$sql = "SELECT * FROM trainer_workouts WHERE trainer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $trainerId);
$stmt->execute();
$result = $stmt->get_result();
$workouts = [];
while ($row = $result->fetch_assoc()) {
    $workouts[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Plans - FitNova Trainer</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
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

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-color); display: flex; min-height: 100vh; }

        /* Sidebar (Shared) */
        .sidebar { width: 260px; background-color: var(--sidebar-bg); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 1000; }
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
        .sidebar-info { padding: 20px; border-bottom: 1px solid var(--border-color); }
        .user-badge { display: flex; align-items: center; gap: 12px; padding: 12px; background: #ffffff; border-radius: 10px; border: 1px solid var(--border-color); }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--secondary-color) 0%, #ef4444 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; }
        .sidebar-menu {
            padding: 20px 0;
            flex: 1;
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

        .user-profile-preview {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
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
            font-size: 11px;
            color: var(--text-light);
            text-transform: uppercase;
        }

        /* Main Content */
        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header-title h2 { font-family: 'Outfit', sans-serif; font-size: 32px; color: #1e293b; }
        .header-title p { color: var(--text-light); margin-top: 5px; }

        .btn-action { padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: var(--transition); border: none; display: flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--primary-color); color: white; }
        .btn-outline { background: white; border: 1px solid var(--border-color); color: var(--text-light); }
        .btn-outline:hover { border-color: var(--primary-color); color: var(--primary-color); }

        .workout-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .workout-card { background: white; border-radius: 20px; border: 1px solid var(--border-color); padding: 30px; box-shadow: var(--shadow); transition: var(--transition); position: relative; }
        .workout-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.1); }

        .workout-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .difficulty-badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .difficulty-beginner { background: #ecfdf5; color: #10b981; }
        .difficulty-intermediate { background: #fffbeb; color: #f59e0b; }
        .difficulty-advanced { background: #fef2f2; color: #ef4444; }

        .workout-card h3 { font-family: 'Outfit', sans-serif; font-size: 20px; color: #1e293b; margin-bottom: 5px; }
        .client-name-tag { font-size: 14px; color: var(--text-light); display: flex; align-items: center; gap: 5px; margin-bottom: 20px; }

        .workout-stats { display: flex; gap: 20px; margin-bottom: 25px; background: #f8fafc; padding: 15px; border-radius: 12px; }
        .workout-stats div { flex: 1; }
        .workout-stats span { display: block; font-size: 11px; color: var(--text-light); text-transform: uppercase; margin-bottom: 4px; }
        .workout-stats strong { font-size: 14px; color: #1e293b; }

        .exercise-list { margin-bottom: 25px; }
        .exercise-list p { font-size: 14px; color: #475569; line-height: 1.6; }

        /* Editable Mode */
        .editable-input, .editable-textarea { width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; background: #f8fafc; margin-bottom: 10px; display: none; }
        .editable-textarea { min-height: 100px; resize: vertical; }
        .edit-mode .display-val { display: none; }
        .edit-mode .editable-input, .edit-mode .editable-textarea { display: block; }
        .edit-mode .save-btn { display: flex; }
        .edit-mode .edit-btn { display: none; }

        .card-actions { display: flex; gap: 10px; }
        .save-btn { background: var(--success-color); color: white; display: none; }

        @media (max-width: 1024px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="home.php" class="brand-logo">
                <i class="fas fa-dumbbell"></i> FitNova <span>TRAINER</span>
            </a>
        </div>

        <nav class="sidebar-menu">
            <a href="trainer_dashboard.php" class="menu-item">
                <i class="fas fa-home"></i> Overview
            </a>
            <a href="trainer_clients.php" class="menu-item">
                <i class="fas fa-users"></i> My Clients
            </a>
            <a href="trainer_schedule.php" class="menu-item">
                <i class="fas fa-calendar-alt"></i> Schedule
            </a>
            <a href="trainer_workouts.php" class="menu-item active">
                <i class="fas fa-clipboard-list"></i> Workout Plans
            </a>
            <a href="trainer_diets.php" class="menu-item">
                <i class="fas fa-utensils"></i> Diet Plans
            </a>
            <a href="trainer_performance.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Performance
            </a>
            <a href="trainer_messages.php" class="menu-item">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="client_profile_setup.php" class="menu-item">
                <i class="fas fa-user-circle"></i> Profile
            </a>
        </nav>

        <div class="user-profile-preview">
            <div class="user-avatar-sm"><?php echo $trainerInitials; ?></div>
            <div class="user-info-sm">
                <h4><?php echo htmlspecialchars($trainerName); ?></h4>
                <p>Expert Trainer</p>
            </div>
            <a href="logout.php" style="margin-left: auto; color: var(--text-light);"><i
                    class="fas fa-sign-out-alt fa-flip-horizontal"></i></a>
        </div>
    </aside>

    <main class="main-content">
        <div class="header-section">
            <div class="header-title"><h2>My Personal Workouts</h2><p>Manage your own training routines and fitness templates</p></div>
            <button class="btn-action btn-primary"><i class="fas fa-plus"></i> Create New Routine</button>
        </div>

        <div class="workout-grid">
            <?php foreach ($workouts as $w): ?>
            <div class="workout-card" data-id="<?php echo $w['workout_id']; ?>">
                <div class="workout-header">
                    <span class="difficulty-badge difficulty-<?php echo $w['difficulty']; ?>"><?php echo $w['difficulty']; ?></span>
                    <i class="fas fa-ellipsis-v" style="color: var(--text-light); cursor: pointer;"></i>
                </div>
                
                <h3 class="display-val"><?php echo htmlspecialchars($w['plan_name']); ?></h3>
                <input type="text" class="editable-input plan-name-input" value="<?php echo htmlspecialchars($w['plan_name']); ?>">

                <p class="client-name-tag">
                    <i class="fas fa-clock"></i>
                    <span>Personal Template</span>
                </p>

                <div class="workout-stats">
                    <div><span>Duration</span><strong><span class="display-val"><?php echo $w['duration_weeks']; ?></span> Weeks</strong>
                        <input type="number" class="editable-input duration-input" value="<?php echo $w['duration_weeks']; ?>">
                    </div>
                    <div><span>Training Frequency</span><strong>4 Days/Week</strong></div>
                </div>

                <div class="exercise-list">
                    <strong>Routine Details:</strong>
                    <p class="display-val"><?php echo nl2br(htmlspecialchars($w['exercises'])); ?></p>
                    <textarea class="editable-textarea exercises-input"><?php echo htmlspecialchars($w['exercises']); ?></textarea>
                </div>

                <div class="card-actions">
                    <button class="btn-action btn-outline edit-btn" onclick="toggleEdit(this)"><i class="fas fa-pen"></i> Edit Routine</button>
                    <button class="btn-action save-btn" onclick="saveWorkout(this)"><i class="fas fa-save"></i> Save Changes</button>
                    <button class="btn-action btn-outline" style="color: var(--primary-color)">Log Performance</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        function toggleEdit(btn) {
            const card = btn.closest('.workout-card');
            card.classList.toggle('edit-mode');
            if (card.classList.contains('edit-mode')) {
                btn.innerHTML = '<i class="fas fa-times"></i> Cancel';
            } else {
                btn.innerHTML = '<i class="fas fa-pen"></i> Edit Plan';
            }
        }

        function saveWorkout(btn) {
            const card = btn.closest('.workout-card');
            const id = card.dataset.id;
            const planName = card.querySelector('.plan-name-input').value;
            const clientName = card.querySelector('.client-name-input').value;
            const duration = card.querySelector('.duration-input').value;
            const exercises = card.querySelector('.exercises-input').value;

            const formData = new FormData();
            formData.append('action', 'update_workout');
            formData.append('workout_id', id);
            formData.append('plan_name', planName);
            formData.append('client_name', clientName);
            formData.append('duration_weeks', duration);
            formData.append('exercises', exercises);

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            fetch('trainer_workouts.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update displays
                    card.querySelector('h3.display-val').innerText = planName;
                    card.querySelector('.client-name-tag .display-val').innerText = clientName;
                    card.querySelector('.workout-stats .display-val').innerText = duration;
                    card.querySelector('.exercise-list p.display-val').innerText = exercises;
                    
                    card.classList.remove('edit-mode');
                    btn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                    btn.disabled = false;
                    
                    const editBtn = card.querySelector('.edit-btn');
                    editBtn.innerHTML = '<i class="fas fa-pen"></i> Edit Plan';
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                }
            });
        }
    </script>
</body>
</html>
