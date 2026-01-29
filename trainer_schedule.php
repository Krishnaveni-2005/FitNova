<?php
session_start();
require "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$isTrainer = $_SESSION['user_role'] === 'trainer';
$isAdmin = $_SESSION['user_role'] === 'admin';

if (!$isTrainer && !$isAdmin) {
    header("Location: login.php");
    exit();
}

// Determine Trainer ID
if ($isAdmin) {
    if (!isset($_GET['trainer_id'])) {
        die("Error: Trainer ID is required for admin view.");
    }
    $trainerId = intval($_GET['trainer_id']);
    
    // Fetch Trainer Name for display
    $tSql = "SELECT first_name, last_name FROM users WHERE user_id = ?";
    $tStmt = $conn->prepare($tSql);
    $tStmt->bind_param("i", $trainerId);
    $tStmt->execute();
    $tRes = $tStmt->get_result();
    if ($tRes->num_rows === 0) {
        die("Error: Trainer not found.");
    }
    $tRow = $tRes->fetch_assoc();
    $trainerName = $tRow['first_name'] . ' ' . $tRow['last_name'];
    $trainerInitials = strtoupper(substr($tRow['first_name'], 0, 1) . substr($tRow['last_name'], 0, 1));
    $tStmt->close();
    
} else {
    $trainerId = $_SESSION['user_id'];
    
    // Check Account Status explicitly for trainers
    $statusSql = "SELECT account_status FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($statusSql);
    $stmt->bind_param("i", $trainerId);
    $stmt->execute();
    $resStatus = $stmt->get_result();
    if ($resStatus->num_rows > 0) {
        $userStatus = $resStatus->fetch_assoc()['account_status'];
        if ($userStatus === 'pending') {
            header("Location: trainer_pending.php");
            exit();
        }
        if ($userStatus === 'inactive' || $userStatus === 'rejected') {
            session_destroy();
            header("Location: login.php?error=account_inactive");
            exit();
        }
    }
    $stmt->close();
    
    $trainerName = $_SESSION['user_name'];
    $trainerInitials = strtoupper(substr($trainerName, 0, 1) . substr(explode(' ', $trainerName)[1] ?? '', 0, 1));
}

// Handle AJAX updates
// Handle AJAX updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Update Schedule
    if ($_POST['action'] === 'update_schedule') {
        $scheduleId = $_POST['schedule_id'];
        $clientName = $conn->real_escape_string($_POST['client_name']);
        $sessionTime = $_POST['session_time'];
        $sessionType = $conn->real_escape_string($_POST['session_type']);
        
        $updateSql = "UPDATE trainer_schedules SET client_name = ?, session_time = ?, session_type = ? WHERE schedule_id = ? AND trainer_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("sssii", $clientName, $sessionTime, $sessionType, $scheduleId, $trainerId);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        $stmt->close();
        exit();
    }
    
    // Add Schedule
    if ($_POST['action'] === 'add_schedule') {
        $clientName = $conn->real_escape_string($_POST['client_name']);
        $sessionTime = $_POST['session_time'];
        $sessionType = $conn->real_escape_string($_POST['session_type']);
        $today = date('Y-m-d');
        
        $insertSql = "INSERT INTO trainer_schedules (trainer_id, client_name, session_time, session_type, session_date, status) VALUES (?, ?, ?, ?, ?, 'upcoming')";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("issss", $trainerId, $clientName, $sessionTime, $sessionType, $today);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'id' => $stmt->insert_id]);
            
            // Notification System: Find user and notify
            // Try to match exact name first
            $findUserSql = "SELECT user_id FROM users WHERE CONCAT(first_name, ' ', last_name) = ? LIMIT 1";
            $fStmt = $conn->prepare($findUserSql);
            $fStmt->bind_param("s", $clientName);
            $fStmt->execute();
            $fRes = $fStmt->get_result();
            if ($fRes->num_rows > 0) {
                $targetUserId = $fRes->fetch_assoc()['user_id'];
                $trainerName = $_SESSION['user_name'];
                $notifMsg = "New Session Scheduled: $sessionType at " . date('h:i A', strtotime($sessionTime)) . " with Coach $trainerName.";
                
                $notifSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, 'session_scheduled', ?)";
                $nStmt = $conn->prepare($notifSql);
                $nStmt->bind_param("is", $targetUserId, $notifMsg);
                $nStmt->execute();
                $nStmt->close();
            }
            $fStmt->close();

        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        $stmt->close();
        exit();
    }
}
$today = date('Y-m-d');
$sql = "SELECT * FROM trainer_schedules WHERE trainer_id = ? AND session_date = ? ORDER BY session_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $trainerId, $today);
$stmt->execute();
$result = $stmt->get_result();
$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedule - FitNova Trainer</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles (Consistent) */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }

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
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .header-title h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
            color: #1e293b;
        }

        .header-title p {
            color: var(--text-light);
            margin-top: 5px;
        }

        .btn-toggle-edit {
            padding: 12px 24px;
            background: white;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-toggle-edit.active {
            background: var(--primary-color);
            color: white;
        }

        .btn-toggle-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
        }

        /* Schedule Container */
        .schedule-container {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }

        .schedule-table th {
            text-align: left;
            padding: 20px 24px;
            background: #f8fafc;
            color: var(--text-light);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--border-color);
        }

        .schedule-table td {
            padding: 24px;
            border-bottom: 1px solid #f1f5f9;
            transition: var(--transition);
        }

        .schedule-row:hover {
            background: #fcfdfe;
        }

        .time-cell {
            font-weight: 700;
            color: var(--primary-color);
            width: 150px;
        }

        .client-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .client-avatar-mini {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #eef2ff;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }

        .client-info-text strong {
            display: block;
            font-size: 15px;
            color: #1e293b;
        }

        .type-cell {
            color: var(--text-light);
            font-size: 14px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-upcoming { background: #fffbeb; color: #f59e0b; }
        .status-completed { background: #ecfdf5; color: #10b981; }

        /* Editable Styles */
        .editable-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            background: #f8fafc;
            display: none;
        }

        .editable-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .edit-mode .display-value { display: none; }
        .edit-mode .editable-input { display: block; }
        .edit-mode .save-btn { display: inline-flex; }

        .save-btn {
            display: none;
            padding: 6px 12px;
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            align-items: center;
            gap: 5px;
        }

        .save-btn:hover { background: #059669; }

        .add-row-btn {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            background: #f8fafc;
            border: 1px dashed var(--border-color);
            border-radius: 10px;
            color: var(--text-light);
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition);
        }

        .add-row-btn:hover {
            background: #f1f5f9;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
        }
        
        <?php if($isAdmin): ?>
        .sidebar { display: none !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 40px !important; }
        .btn-toggle-edit, .add-row-btn { display: none !important; }
        /* Center header for cleaner report view */
        .header-section { justify-content: center; text-align: center; flex-direction: column; gap: 10px; }
        .header-title h2 { font-size: 28px; }
        <?php endif; ?>
    </style>
</head>

<body>
    <?php if(!$isAdmin): ?>
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
            <a href="trainer_schedule.php" class="menu-item active">
                <i class="fas fa-calendar-alt"></i> Schedule
            </a>
            <a href="trainer_workouts.php" class="menu-item">
                <i class="fas fa-clipboard-list"></i> Workout Plans
            </a>
            <a href="trainer_diets.php" class="menu-item">
                <i class="fas fa-utensils"></i> Diet Plans
            </a>
            <a href="trainer_achievements.php" class="menu-item">
                <i class="fas fa-medal"></i> Achievements
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
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content" id="main-content">
        <div class="header-section">
            <div class="header-title">
                <h2><?php echo $isAdmin ? 'Schedule: ' . htmlspecialchars($trainerName) : "Today's Schedule"; ?></h2>
                <p><?php echo date('l, F j, Y'); ?></p>
            </div>
            <button class="btn-toggle-edit" id="toggleEditBtn">
                <i class="fas fa-edit"></i>
                <span>Enable Editing</span>
            </button>
        </div>

        <div class="schedule-container">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Client</th>
                        <th>Training Type</th>
                        <th>Status</th>
                        <th class="edit-actions-col" style="display:none;">Actions</th>
                    </tr>
                </thead>
                <tbody id="scheduleBody">
                    <?php foreach ($schedules as $s): 
                        $time = date('h:i A', strtotime($s['session_time']));
                        $initials = strtoupper(substr($s['client_name'], 0, 1) . substr(explode(' ', $s['client_name'])[1] ?? '', 0, 1));
                    ?>
                    <tr class="schedule-row" data-id="<?php echo $s['schedule_id']; ?>">
                        <td class="time-cell">
                            <span class="display-value"><?php echo $time; ?></span>
                            <input type="time" class="editable-input" value="<?php echo date('H:i', strtotime($s['session_time'])); ?>">
                        </td>
                        <td class="client-cell">
                            <div class="client-avatar-mini"><?php echo $initials; ?></div>
                            <div class="client-info-text">
                                <span class="display-value"><strong><?php echo htmlspecialchars($s['client_name']); ?></strong></span>
                                <input type="text" class="editable-input" style="font-weight: 700;" value="<?php echo htmlspecialchars($s['client_name']); ?>">
                            </div>
                        </td>
                        <td class="type-cell">
                            <span class="display-value"><?php echo htmlspecialchars($s['session_type']); ?></span>
                            <input type="text" class="editable-input" value="<?php echo htmlspecialchars($s['session_type']); ?>">
                        </td>
                        <td>
                            <div class="status-badge status-<?php echo $s['status']; ?>">
                                <i class="fas <?php echo $s['status'] === 'upcoming' ? 'fa-clock' : 'fa-check-circle'; ?>"></i>
                                <?php echo ucfirst($s['status']); ?>
                            </div>
                        </td>
                        <td class="edit-actions-col" style="display:none;">
                            <button class="save-btn" onclick="saveRow(this)">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button class="add-row-btn" style="display:none;" id="addRowBtn">
            <i class="fas fa-plus-circle"></i> Add New Session
        </button>
    </main>

    <!-- Add Session Modal -->
    <div id="addSessionModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:white; padding:30px; border-radius:12px; width:400px; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
            <h3 style="margin-bottom:20px; color:var(--primary-color);">Add New Session</h3>
            <form id="addSessionForm">
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:500; font-size:14px;">Client Name</label>
                    <input type="text" name="client_name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:500; font-size:14px;">Time</label>
                    <input type="time" name="session_time" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:5px; font-weight:500; font-size:14px;">Session Type</label>
                    <input type="text" name="session_type" placeholder="e.g. HIIT, Yoga" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeModal()" style="padding:10px 20px; border:1px solid #ddd; background:white; border-radius:8px; cursor:pointer;">Cancel</button>
                    <button type="submit" style="padding:10px 20px; background:var(--primary-color); color:white; border:none; border-radius:8px; cursor:pointer;">Add Session</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const toggleEditBtn = document.getElementById('toggleEditBtn');
        const mainContent = document.getElementById('main-content');
        const editActionsCols = document.querySelectorAll('.edit-actions-col');
        const addRowBtn = document.getElementById('addRowBtn');
        const addModal = document.getElementById('addSessionModal');

        toggleEditBtn.addEventListener('click', () => {
            const isActive = toggleEditBtn.classList.toggle('active');
            
            if (isActive) {
                toggleEditBtn.innerHTML = '<i class="fas fa-times"></i> <span>Disable Editing</span>';
                mainContent.classList.add('edit-mode');
                editActionsCols.forEach(col => col.style.display = 'table-cell');
                addRowBtn.style.display = 'flex';
            } else {
                toggleEditBtn.innerHTML = '<i class="fas fa-edit"></i> <span>Enable Editing</span>';
                mainContent.classList.remove('edit-mode');
                editActionsCols.forEach(col => col.style.display = 'none');
                addRowBtn.style.display = 'none';
            }
        });

        function saveRow(btn) {
            const row = btn.closest('tr');
            const id = row.dataset.id;
            const timeInput = row.querySelector('input[type="time"]');
            const nameInput = row.querySelectorAll('input[type="text"]')[0];
            const typeInput = row.querySelectorAll('input[type="text"]')[1];

            const formData = new FormData();
            formData.append('action', 'update_schedule');
            formData.append('schedule_id', id);
            formData.append('session_time', timeInput.value);
            formData.append('client_name', nameInput.value);
            formData.append('session_type', typeInput.value);

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            fetch('trainer_schedule.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update display values
                    row.querySelector('.time-cell .display-value').innerText = formatTime(timeInput.value);
                    row.querySelector('.client-info-text .display-value strong').innerText = nameInput.value;
                    row.querySelector('.type-cell .display-value').innerText = typeInput.value;
                    
                    // Visual feedback
                    btn.innerHTML = '<i class="fas fa-check"></i> Saved';
                    btn.style.background = '#10b981';
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-save"></i> Save';
                        btn.style.background = '';
                        btn.disabled = false;
                    }, 2000);
                } else {
                    alert('Error saving data: ' + data.message);
                    btn.innerHTML = '<i class="fas fa-save"></i> Save';
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.innerHTML = '<i class="fas fa-save"></i> Save';
                btn.disabled = false;
            });
        }

        function formatTime(timeStr) {
            const [hours, minutes] = timeStr.split(':');
            let h = parseInt(hours);
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            h = h ? h : 12; // the hour '0' should be '12'
            return `${h}:${minutes} ${ampm}`;
        }
        
        // Modal Logic
        addRowBtn.addEventListener('click', () => {
            addModal.style.display = 'flex';
        });
        
        function closeModal() {
            addModal.style.display = 'none';
        }
        
        document.getElementById('addSessionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_schedule');
            
            fetch('trainer_schedule.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload(); 
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        // Close modal on outside click
        addModal.addEventListener('click', (e) => {
            if (e.target === addModal) closeModal();
        });
    </script>
</body>

</html>
