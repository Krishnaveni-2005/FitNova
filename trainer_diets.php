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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_diet') {
    $dietId = $_POST['diet_id'];
    $planName = $conn->real_escape_string($_POST['plan_name']);
    $clientName = $conn->real_escape_string($_POST['client_name']);
    $calories = (int)$_POST['target_calories'];
    $dietType = $conn->real_escape_string($_POST['diet_type']);
    $mealDetails = $conn->real_escape_string($_POST['meal_details']);
    
    $updateSql = "UPDATE trainer_diet_plans SET plan_name = ?, client_name = ?, target_calories = ?, diet_type = ?, meal_details = ? WHERE diet_id = ? AND trainer_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssissii", $planName, $clientName, $calories, $dietType, $mealDetails, $dietId, $trainerId);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    $stmt->close();
    exit();
}

// Fetch Diet Plans
$sql = "SELECT * FROM trainer_diet_plans WHERE trainer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $trainerId);
$stmt->execute();
$result = $stmt->get_result();
$diets = [];
while ($row = $result->fetch_assoc()) {
    $diets[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diet Plans - FitNova Trainer</title>
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

        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header-title h2 { font-family: 'Outfit', sans-serif; font-size: 32px; color: #1e293b; }
        .header-title p { color: var(--text-light); margin-top: 5px; }

        .btn-action { padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: var(--transition); border: none; display: flex; align-items: center; gap: 8px; }
        .btn-secondary { background: var(--secondary-color); color: white; }
        .btn-outline { background: white; border: 1px solid var(--border-color); color: var(--text-light); }
        .btn-outline:hover { border-color: var(--primary-color); color: var(--primary-color); }

        .diet-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 30px; }
        .diet-card { background: white; border-radius: 20px; border: 1px solid var(--border-color); padding: 30px; box-shadow: var(--shadow); transition: var(--transition); position: relative; }
        .diet-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1); }

        .diet-header { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
        .diet-icon { width: 50px; height: 50px; border-radius: 12px; background: #fffbeb; color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .diet-header-text h3 { font-family: 'Outfit', sans-serif; font-size: 22px; color: #1e293b; }
        .diet-header-text p { font-size: 14px; color: var(--text-light); }

        .calorie-gauge { background: #f1f5f9; height: 12px; border-radius: 6px; position: relative; margin: 30px 0 15px; }
        .calorie-fill { background: linear-gradient(90deg, #f59e0b, #ef4444); height: 100%; border-radius: 6px; width: 75%; }
        .calorie-label { display: flex; justify-content: space-between; font-size: 12px; color: var(--text-light); font-weight: 600; }

        .diet-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .meta-item span { display: block; font-size: 11px; color: var(--text-light); text-transform: uppercase; margin-bottom: 5px; }
        .meta-item strong { display: block; font-size: 15px; color: #1e293b; }

        .meal-list { background: #fcfdfe; border: 1px solid #f1f5f9; border-radius: 15px; padding: 20px; margin-bottom: 25px; }
        .meal-list h4 { font-size: 14px; color: #1e293b; margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
        .meal-list p { font-size: 14px; color: #475569; line-height: 1.6; }

        .editable-input, .editable-textarea { width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; background: #f8fafc; margin-bottom: 10px; display: none; }
        .editable-textarea { min-height: 120px; }
        .edit-mode .display-val { display: none; }
        .edit-mode .editable-input, .edit-mode .editable-textarea { display: block; }
        .edit-mode .save-btn { display: flex; }
        .edit-mode .edit-btn { display: none; }

        .card-actions { display: flex; gap: 12px; }
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
            <a href="trainer_workouts.php" class="menu-item">
                <i class="fas fa-clipboard-list"></i> Workout Plans
            </a>
            <a href="trainer_diets.php" class="menu-item active">
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
            <div class="header-title"><h2>My Personal Diet</h2><p>Track your nutrition and meal preparation guides</p></div>
            <button class="btn-action btn-secondary"><i class="fas fa-carrot"></i> Create New Guide</button>
        </div>

        <div class="diet-grid">
            <?php foreach ($diets as $d): ?>
            <div class="diet-card" data-id="<?php echo $d['diet_id']; ?>">
                <div class="diet-header">
                    <div class="diet-icon"><i class="fas fa-seedling"></i></div>
                    <div class="diet-header-text">
                        <h3 class="display-val"><?php echo htmlspecialchars($d['plan_name']); ?></h3>
                        <input type="text" class="editable-input plan-name-input" value="<?php echo htmlspecialchars($d['plan_name']); ?>">
                        
                        <p><i class="fas fa-check-circle"></i> Active Personal Plan</p>
                    </div>
                </div>

                <div class="diet-meta">
                    <div class="meta-item">
                        <span>Philosophy</span>
                        <strong class="display-val"><?php echo htmlspecialchars($d['diet_type']); ?></strong>
                        <input type="text" class="editable-input type-input" value="<?php echo htmlspecialchars($d['diet_type']); ?>">
                    </div>
                    <div class="meta-item">
                        <span>Calorie Goal</span>
                        <strong class="display-val"><?php echo $d['target_calories']; ?> kcal</strong>
                        <input type="number" class="editable-input calories-input" value="<?php echo $d['target_calories']; ?>">
                    </div>
                </div>

                <div class="calorie-gauge"><div class="calorie-fill"></div></div>
                <div class="calorie-label"><span>Current Consumption</span><span><?php echo $d['target_calories']; ?> kcal goal</span></div>

                 <div class="meal-list" style="margin-top: 25px;">
                    <h4><i class="fas fa-utensils"></i> My Meal Plan</h4>
                    <p class="display-val"><?php echo nl2br(htmlspecialchars($d['meal_details'])); ?></p>
                    <textarea class="editable-textarea details-input"><?php echo htmlspecialchars($d['meal_details']); ?></textarea>
                </div>

                <div class="card-actions">
                    <button class="btn-action btn-outline edit-btn" onclick="toggleEdit(this)"><i class="fas fa-edit"></i> Modify Personal Plan</button>
                    <button class="btn-action save-btn" onclick="saveDiet(this)"><i class="fas fa-check"></i> Update Personal Log</button>
                    <button class="btn-action btn-outline" style="color: var(--secondary-color)">Macros Overview</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        function toggleEdit(btn) {
            const card = btn.closest('.diet-card');
            card.classList.toggle('edit-mode');
            if (card.classList.contains('edit-mode')) {
                btn.innerHTML = '<i class="fas fa-times"></i> Dismiss';
            } else {
                btn.innerHTML = '<i class="fas fa-edit"></i> Modify Plan';
            }
        }

        function saveDiet(btn) {
            const card = btn.closest('.diet-card');
            const id = card.dataset.id;
            const planName = card.querySelector('.plan-name-input').value;
            const clientName = card.querySelector('.client-name-input').value;
            const type = card.querySelector('.type-input').value;
            const calories = card.querySelector('.calories-input').value;
            const details = card.querySelector('.details-input').value;

            const formData = new FormData();
            formData.append('action', 'update_diet');
            formData.append('diet_id', id);
            formData.append('plan_name', planName);
            formData.append('client_name', clientName);
            formData.append('diet_type', type);
            formData.append('target_calories', calories);
            formData.append('meal_details', details);

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            btn.disabled = true;

            fetch('trainer_diets.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    card.querySelector('h3.display-val').innerText = planName;
                    card.querySelector('.diet-header-text p.display-val').innerHTML = '<i class="fas fa-user-circle"></i> ' + clientName;
                    card.querySelector('.meta-item strong.display-val').innerText = type;
                    card.querySelector('.meta-item:last-child strong.display-val').innerText = calories + ' kcal';
                    card.querySelector('.meal-list p.display-val').innerText = details;
                    card.querySelector('.calorie-label span:last-child').innerText = calories + ' kcal limit';
                    
                    card.classList.remove('edit-mode');
                    btn.innerHTML = '<i class="fas fa-check"></i> Update Plan';
                    btn.disabled = false;
                    
                    const editBtn = card.querySelector('.edit-btn');
                    editBtn.innerHTML = '<i class="fas fa-edit"></i> Modify Plan';
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> Update Plan';
                }
            });
        }
    </script>
</body>
</html>
