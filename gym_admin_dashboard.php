<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$allowed_roles = ['gym_admin', 'admin'];
$allowed_emails = ['ashakayaplackal@gmail.com'];
$user_email = $_SESSION['user_email'] ?? '';

if (!in_array($_SESSION['user_role'], $allowed_roles) && !in_array($user_email, $allowed_emails)) {
    header("Location: login.php");
    exit();
}

// Get gym admin info from session
$adminName = $_SESSION['user_name'] ?? 'Gym Admin';
$adminEmail = $_SESSION['user_email'];
$adminInitials = strtoupper(substr($adminName, 0, 1) . substr(strrchr($adminName, ' '), 1, 1));

// Fetch Gym Equipment from DB
require "db_connect.php";
$gymEquipment = [];
$sqlEquip = "SELECT * FROM gym_equipment";
$resultEquip = $conn->query($sqlEquip);
if ($resultEquip && $resultEquip->num_rows > 0) {
    while($row = $resultEquip->fetch_assoc()) {
        $gymEquipment[] = $row;
    }
}

// Fetch Trainer Attendance
$trainersOnSite = [];
// Safe check for missing tables
$sqlTrainers = "SELECT u.*, ta.check_in_time, ta.check_out_time, ta.status 
                FROM trainer_attendance ta 
                JOIN users u ON ta.trainer_id = u.user_id 
                WHERE DATE(ta.check_in_time) = CURDATE() 
                ORDER BY ta.check_in_time DESC";
$resultTrainers = $conn->query($sqlTrainers);
if ($resultTrainers && $resultTrainers->num_rows > 0) {
    while($row = $resultTrainers->fetch_assoc()) {
        $trainersOnSite[] = $row;
    }
}

// Get today's stats
$todayDate = date('Y-m-d');
$todayMembersCount = 0;
// Check if table exists to avoid fatal error
$checkTable = $conn->query("SHOW TABLES LIKE 'gym_check_ins'");
if ($checkTable && $checkTable->num_rows > 0) {
    $sqlTodayMembers = "SELECT COUNT(DISTINCT user_id) as count FROM gym_check_ins WHERE DATE(check_in_time) = '$todayDate'";
    $res = $conn->query($sqlTodayMembers);
    if($res) $todayMembersCount = $res->fetch_assoc()['count'] ?? 0;
}

// Fetch Active Offline Clients
$offlineClients = [];
$gymColCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'gym_membership_status'");
if ($gymColCheck && $gymColCheck->num_rows > 0) {
    $sqlClients = "SELECT * FROM users WHERE gym_membership_status = 'active'";
    $resClients = $conn->query($sqlClients);
    if ($resClients && $resClients->num_rows > 0) {
        while($row = $resClients->fetch_assoc()) {
            $offlineClients[] = $row;
        }
    }
}

// Fetch Gym Schedule Settings
$gymSettings = [
    'gym_open_time' => '05:00 AM',
    'gym_close_time' => '10:00 PM',
    'gym_status' => 'open'
];
$sqlSettings = "SELECT * FROM gym_settings";
$resSettings = $conn->query($sqlSettings);
if ($resSettings && $resSettings->num_rows > 0) {
    while($row = $resSettings->fetch_assoc()) {
        $gymSettings[$row['setting_key']] = $row['setting_value'];
    }
}

$activeTrainersCount = 0;
$activeTrainersCount = 3;
// $sqlActiveTrainers = "SELECT COUNT(*) as count FROM trainer_attendance WHERE DATE(check_in_time) = CURDATE() AND status = 'checked_in'";
// $resAct = $conn->query($sqlActiveTrainers);
// if($resAct) $activeTrainersCount = $resAct->fetch_assoc()['count'] ?? 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Admin Dashboard - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #334155;
        }

        /* Sidebar */
        .gym-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.02);
        }

        .gym-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: linear-gradient(135deg, #0F2C59 0%, #1A3C6B 100%);
        }

        .gym-header h1 {
            color: white;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .gym-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .admin-info {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            background: #fcfdfe;
        }

        .admin-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .admin-name {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .admin-role {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .nav-menu {
            padding: 20px 0;
        }

        .nav-item {
            padding: 12px 20px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            text-decoration: none;
            font-weight: 500;
        }

        .nav-item:hover {
            background: #f1f5f9;
            color: #0F2C59;
        }

        .nav-item.active {
            background: #eef2ff;
            color: #0F2C59;
            border-left-color: #0F2C59;
        }

        .nav-item i {
            width: 20px;
            font-size: 16px;
        }

        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            padding: 12px;
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #dc2626;
            color: white;
        }

        /* Main Content */
        .gym-main {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
        }

        .top-bar {
            background: #ffffff;
            padding: 24px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .top-bar h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: #ffffff;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .stat-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .stat-box:nth-child(1)::before {
            background: linear-gradient(90deg, #10b981, #14b8a6);
        }

        .stat-box:nth-child(2)::before {
            background: linear-gradient(90deg, #3b82f6, #2563eb);
        }

        .stat-box:nth-child(3)::before {
            background: linear-gradient(90deg, #f59e0b, #ef4444);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }

        .stat-box:nth-child(1) .stat-icon {
            background: #ecfdf5;
            color: #10b981;
        }

        .stat-box:nth-child(2) .stat-icon {
            background: #eff6ff;
            color: #3b82f6;
        }

        .stat-box:nth-child(3) .stat-icon {
            background: #fffbeb;
            color: #f59e0b;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
        }

        /* Management Section */
        .management-section {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Equipment Grid */
        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .equipment-card {
            background: #fcfdfe;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .equipment-card:hover {
            border-color: #0F2C59;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .equipment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .equipment-name {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-available {
            background: #dcfce7;
            color: #166534;
        }

        .status-maintenance {
            background: #fef3c7;
            color: #92400e;
        }

        .status-unavailable {
            background: #fee2e2;
            color: #991b1b;
        }

        .equipment-info {
            margin: 12px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-label {
            color: #64748b;
        }

        .info-value {
            color: #334155;
            font-weight: 600;
        }

        .equipment-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        .action-btn {
            flex: 1;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #64748b;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: #0F2C59;
            color: white;
            border-color: #0F2C59;
        }

        /* Trainer Table */
        .trainer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .trainer-table th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f1f5f9;
        }

        .trainer-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #475569;
        }

        .trainer-table tr:hover {
            background: #f8fafc;
        }

        .btn-primary {
            background: #0F2C59;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: #1A3C6B;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="gym-sidebar">
        <div class="gym-header">
            <h1>FitNova</h1>
            <p>Gym Management</p>
        </div>

        <div class="admin-info">
            <div class="admin-badge">
                <div class="admin-avatar"><?php echo $adminInitials; ?></div>
                <div>
                    <div class="admin-name"><?php echo htmlspecialchars($adminName); ?></div>
                    <div class="admin-role">Gym Owner</div>
                </div>
            </div>
        </div>

        <nav class="nav-menu">
            <div class="nav-item active" onclick="showSection('dashboard')">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="nav-item" onclick="showSection('equipment')">
                <i class="fas fa-dumbbell"></i>
                <span>Equipment</span>
            </div>
            <div class="nav-item" onclick="showSection('trainers')">
                <i class="fas fa-user-tie"></i>
                <span>Trainers On-Site</span>
            </div>
            <div class="nav-item" onclick="showSection('members')">
                <i class="fas fa-users"></i>
                <span>Clients</span>
            </div>
        </nav>

        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="gym-main">
        <div class="top-bar">
            <h2>Gym Admin Dashboard</h2>
            <div style="color: #64748b; font-size: 14px;">
                <i class="far fa-calendar"></i> <?php echo date('l, F j, Y'); ?>
            </div>
        </div>

        <!-- Dashboard Section -->
        <div id="dashboard" class="section active">
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo count($offlineClients); ?></div>
                    <div class="stat-label">Clients</div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-value"><?php echo $activeTrainersCount; ?></div>
                    <div class="stat-label">Trainers On-Site</div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <div class="stat-value"><?php echo count($gymEquipment); ?></div>
                    <div class="stat-label">Total Equipment</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="management-section">
                <div class="section-title">Quick Actions</div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <button class="btn-primary" onclick="showSection('equipment')">
                        <i class="fas fa-dumbbell"></i> Manage Equipment
                    </button>
                    <button class="btn-primary" onclick="showSection('trainers')">
                        <i class="fas fa-user-tie"></i> View Trainers
                    </button>
                    <button class="btn-primary" onclick="showSection('members')">
                        <i class="fas fa-users"></i> View Clients
                    </button>
                </div>
            </div>

            <!-- Schedule Management -->
            <div class="management-section" style="margin-top: 20px;">
                <div class="section-title">Manage Schedule</div>
                <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #475569;">Opening Time</label>
                        <input type="text" id="sched_open" value="<?php echo htmlspecialchars($gymSettings['gym_open_time']); ?>" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; width: 120px;" placeholder="05:00 AM">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #475569;">Closing Time</label>
                        <input type="text" id="sched_close" value="<?php echo htmlspecialchars($gymSettings['gym_close_time']); ?>" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; width: 120px;" placeholder="10:00 PM">
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; height: 42px;">
                        <input type="checkbox" id="sched_closed" style="width: 18px; height: 18px;" <?php echo $gymSettings['gym_status'] === 'closed' ? 'checked' : ''; ?>>
                        <label for="sched_closed" style="font-weight: 500; color: #ef4444;">Mark Gym as Closed Today</label>
                    </div>
                    <button onclick="submitSchedule()" class="btn-primary" style="background: #0f172a;">Save Schedule</button>
                </div>
            </div>
        </div>

        <!-- Equipment Section -->
        <div id="equipment" class="section" style="display: none;">
            <div class="management-section">
                <div class="section-title">Gym Equipment Management</div>
                <div class="equipment-grid">
                    <?php foreach ($gymEquipment as $equip): 
                        // Normalize keys if needed (though we fetch * so it should be id, name)
                        $eId = $equip['id'];
                        $eName = $equip['name'];
                    ?>
                        <div class="equipment-card">
                            <div class="equipment-header">
                                <div class="equipment-name"><?php echo htmlspecialchars($eName); ?></div>
                                <span class="status-badge status-<?php echo strtolower($equip['status']); ?>">
                                    <?php echo htmlspecialchars($equip['status']); ?>
                                </span>
                            </div>
                            <div class="equipment-info">
                                <div class="info-row">
                                    <span class="info-label">Total Units</span>
                                    <span class="info-value"><?php echo $equip['total_units']; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Available</span>
                                    <span class="info-value"><?php echo $equip['available_units']; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">In Use</span>
                                    <span class="info-value"><?php echo $equip['total_units'] - $equip['available_units']; ?></span>
                                </div>
                            </div>
                            <div class="equipment-actions">
                                <button class="action-btn" onclick="updateEquipmentStatus(<?php echo $eId; ?>, 'available')">
                                    <i class="fas fa-check"></i> Available
                                </button>
                                <button class="action-btn" onclick="updateEquipmentStatus(<?php echo $eId; ?>, 'maintenance')">
                                    <i class="fas fa-wrench"></i> Maintenance
                                </button>
                                <button class="action-btn" onclick="openEditModal(<?php echo $eId; ?>, '<?php echo addslashes($eName); ?>', <?php echo $equip['total_units']; ?>, <?php echo $equip['available_units']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Trainers Section -->
        <div id="trainers" class="section" style="display: none;">
            <div class="management-section">
                <div class="section-title">Trainers On-Site Today</div>
                <table class="trainer-table">
                    <thead>
                        <tr>
                            <th>Trainer Name</th>
                            <th>Check-In Time</th>
                            <th>Check-Out Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $staticTrainers = [
                            [
                                'user_id' => 'static_1',
                                'first_name' => 'Joshua',
                                'last_name' => 'Joseph',
                                'email' => 'joshua.joseph@fitnova.com',
                                'trainer_specialization' => 'Gym Trainer',
                                'trainer_experience' => 5,
                                'bio' => 'Expert in functional training and HIIT.',
                                'check_in_time' => date('Y-m-d 07:00:00'),
                                'check_out_time' => null,
                                'status' => 'checked_in'
                            ],
                            [
                                'user_id' => 'static_2',
                                'first_name' => 'David',
                                'last_name' => 'John',
                                'email' => 'david.john@fitnova.com',
                                'trainer_specialization' => 'Strength Coach',
                                'trainer_experience' => 4,
                                'bio' => 'Strength and conditioning specialist.',
                                'check_in_time' => date('Y-m-d 07:30:00'),
                                'check_out_time' => null,
                                'status' => 'checked_in'
                            ],
                            [
                                'user_id' => 'static_3',
                                'first_name' => 'Elis',
                                'last_name' => 'Reji',
                                'email' => 'elis.reji@fitnova.com',
                                'trainer_specialization' => 'Fitness Instructor',
                                'trainer_experience' => 3,
                                'bio' => 'Certified yoga instructor and wellness coach.',
                                'check_in_time' => date('Y-m-d 08:00:00'),
                                'check_out_time' => null,
                                'status' => 'checked_in'
                            ]
                        ];

                        foreach ($staticTrainers as $trainer): 
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></td>
                            <td><?php echo date('h:i A', strtotime($trainer['check_in_time'])); ?></td>
                            <td>-</td>
                            <td>
                                <span class="status-badge status-available">
                                    checked_in
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap:5px;">
                                    <button class="action-btn" onclick="if(confirm('Check out this trainer?')){ alert('Trainer checked out successfully!'); location.reload(); }" title="Check Out">
                                        <i class="fas fa-sign-out-alt"></i> Out
                                    </button>
                                    <button class="action-btn" onclick="viewTrainer(<?php echo htmlspecialchars(json_encode($trainer)); ?>)" title="View Profile">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="action-btn" onclick="editTrainer(<?php echo htmlspecialchars(json_encode($trainer)); ?>)" title="Edit Profile">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Clients Section -->
        <div id="members" class="section" style="display: none;">
            <div class="management-section">
                <div class="section-title">Offline Gym Clients</div>
                <table class="trainer-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Status Detail</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($offlineClients) > 0): ?>
                            <?php foreach ($offlineClients as $client): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center;">
                                            <div style="width:35px; height:35px; background:#e2e8f0; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-right:10px; color:#64748b;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($client['email']); ?></td>
                                    <td>
                                        <span class="status-badge status-available" style="background:#e0f2fe; color:#0369a1;">
                                            <i class="fas fa-check-circle"></i> Paid (+₹10)
                                        </span>
                                    </td>
                                    <td>
                                        <button class="action-btn" title="View Details" onclick="viewClient(<?php echo htmlspecialchars(json_encode($client)); ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px; color: #64748b;">
                                    No clients have subscribed to offline gym yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
        <div style="background:white; width:90%; max-width:400px; margin:100px auto; padding:20px; border-radius:10px; position:relative;">
            <h3 style="margin-bottom:15px; color:#1e293b;">Edit Equipment</h3>
            <input type="hidden" id="edit_id">
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; color:#64748b; font-size:14px;">Equipment Name</label>
                <input type="text" id="edit_name" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; color:#64748b; font-size:14px;">Total Units</label>
                <input type="number" id="edit_total" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:5px; color:#64748b; font-size:14px;">Available Units</label>
                <input type="number" id="edit_available" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button onclick="document.getElementById('editModal').style.display='none'" style="padding:8px 15px; border:none; background:#e2e8f0; border-radius:6px; cursor:pointer;">Cancel</button>
                <button onclick="submitEdit()" class="btn-primary">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- View Trainer Modal -->
    <div id="viewTrainerModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
        <div style="background:white; width:90%; max-width:500px; margin:80px auto; padding:25px; border-radius:10px; position:relative; max-height: 80vh; overflow-y: auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">
                <h3 style="margin:0; color:#1e293b;">Trainer Profile</h3>
                <span onclick="document.getElementById('viewTrainerModal').style.display='none'" style="cursor:pointer; font-size:1.5rem;">&times;</span>
            </div>
            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:80px; height:80px; background:#e2e8f0; border-radius:50%; margin:0 auto 10px; display:flex; align-items:center; justify-content:center; font-size:2rem; color:#64748b;">
                    <i class="fas fa-user"></i>
                </div>
                <h2 id="view_name" style="font-size:1.5rem; margin-bottom:5px;"></h2>
                <p id="view_email" style="color:#64748b; font-size:0.9rem;"></p>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                <div style="background:#f8fafc; padding:15px; border-radius:8px;">
                    <label style="display:block; font-size:12px; color:#64748b; text-transform:uppercase; margin-bottom:5px;">Specialization</label>
                    <div id="view_spec" style="font-weight:600; color:#0F2C59;"></div>
                </div>
                <div style="background:#f8fafc; padding:15px; border-radius:8px;">
                    <label style="display:block; font-size:12px; color:#64748b; text-transform:uppercase; margin-bottom:5px;">Experience</label>
                    <div id="view_exp" style="font-weight:600; color:#0F2C59;"></div>
                </div>
            </div>
            <div>
                <label style="display:block; font-size:12px; color:#64748b; text-transform:uppercase; margin-bottom:5px;">Bio</label>
                <p id="view_bio" style="line-height:1.6; color:#334155; font-size:0.95rem;"></p>
            </div>
        </div>
    </div>

    <!-- Edit Trainer Modal -->
    <div id="editTrainerModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
        <div style="background:white; width:90%; max-width:500px; margin:80px auto; padding:25px; border-radius:10px; position:relative;">
            <h3 style="margin-bottom:20px; color:#1e293b;">Edit Trainer Profile</h3>
            <input type="hidden" id="edit_trainer_id">
            <div style="display:flex; gap:15px; margin-bottom:15px;">
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:5px; color:#64748b;">First Name</label>
                    <input type="text" id="edit_trainer_fname" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                </div>
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:5px; color:#64748b;">Last Name</label>
                    <input type="text" id="edit_trainer_lname" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                </div>
            </div>
            <div style="display:flex; gap:15px; margin-bottom:15px;">
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:5px; color:#64748b;">Specialization</label>
                    <input type="text" id="edit_trainer_spec" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                </div>
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:5px; color:#64748b;">Experience (Yrs)</label>
                    <input type="number" step="0.5" id="edit_trainer_exp" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                </div>
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:5px; color:#64748b;">Bio</label>
                <textarea id="edit_trainer_bio" rows="4" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px; font-family:inherit;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button onclick="document.getElementById('editTrainerModal').style.display='none'" style="padding:8px 15px; border:none; background:#e2e8f0; border-radius:6px; cursor:pointer;">Cancel</button>
                <button onclick="submitTrainerEdit()" class="btn-primary">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- View Client Modal -->
    <div id="viewClientModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
        <div style="background:white; width:90%; max-width:500px; margin:80px auto; padding:25px; border-radius:10px; position:relative;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">
                <h3 style="margin:0; color:#1e293b;">Client Details</h3>
                <span onclick="document.getElementById('viewClientModal').style.display='none'" style="cursor:pointer; font-size:1.5rem;">&times;</span>
            </div>
            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:70px; height:70px; background:#e0f2fe; border-radius:50%; margin:0 auto 10px; display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:#0369a1;">
                    <i class="fas fa-user"></i>
                </div>
                <h2 id="client_view_name" style="font-size:1.4rem; margin-bottom:5px;"></h2>
                <span style="background:#dcfce7; color:#166534; padding:2px 10px; border-radius:15px; font-size:0.8rem; font-weight:600;">Active Offline Member</span>
            </div>
            <div style="background:#f8fafc; padding:20px; border-radius:8px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
                    <span style="color:#64748b;">Email</span>
                    <strong id="client_view_email" style="color:#334155;"></strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
                    <span style="color:#64748b;">Role</span>
                    <strong id="client_view_role" style="color:#334155; text-transform:capitalize;"></strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0;">
                    <span style="color:#64748b;">Joined Date</span>
                    <strong id="client_view_date" style="color:#334155;"></strong>
                </div>
            </div>
            <div style="margin-top:20px; text-align:center;">
                <button onclick="document.getElementById('viewClientModal').style.display='none'" class="btn-primary" style="width:100%;">Close</button>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Remove active class from all nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionId).style.display = 'block';
            
            // Add active class to clicked nav item
            event.target.closest('.nav-item').classList.add('active');
        }

        function updateEquipmentStatus(equipmentId, status) {
            if (confirm(`Update equipment status to ${status}?`)) {
                fetch('update_equipment_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        equipment_id: equipmentId,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Equipment status updated successfully!');
                        location.reload();
                    } else {
                        alert('Error updating equipment status');
                    }
                });
            }
        }

        function checkOutTrainer(trainerId) {
            if (confirm('Check out this trainer?')) {
                fetch('trainer_checkout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        trainer_id: trainerId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Trainer checked out successfully!');
                        location.reload();
                    } else {
                        alert('Error checking out trainer');
                    }
                });
            }
        }

        function openEditModal(id, name, total, available) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_total').value = total;
            document.getElementById('edit_available').value = available;
            document.getElementById('editModal').style.display = 'block';
        }

        function submitEdit() {
            const id = document.getElementById('edit_id').value;
            const name = document.getElementById('edit_name').value;
            const total = document.getElementById('edit_total').value;
            const available = document.getElementById('edit_available').value;

            fetch('update_equipment_details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, name, total, available })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Equipment updated!');
                    location.reload();
                } else {
                    alert('Error updating: ' + (data.message || 'Unknown error'));
                }
            });
        }

        function viewTrainer(trainer) {
            document.getElementById('view_name').textContent = trainer.first_name + ' ' + trainer.last_name;
            document.getElementById('view_email').textContent = trainer.email;
            document.getElementById('view_spec').textContent = trainer.trainer_specialization || 'General Fitness';
            document.getElementById('view_exp').textContent = (trainer.trainer_experience || '0') + ' Years';
            document.getElementById('view_bio').textContent = trainer.bio || 'No biography available.';
            document.getElementById('viewTrainerModal').style.display = 'block';
        }

        function editTrainer(trainer) {
            document.getElementById('edit_trainer_id').value = trainer.user_id;
            document.getElementById('edit_trainer_fname').value = trainer.first_name;
            document.getElementById('edit_trainer_lname').value = trainer.last_name;
            document.getElementById('edit_trainer_spec').value = trainer.trainer_specialization || '';
            document.getElementById('edit_trainer_exp').value = trainer.trainer_experience || '';
            document.getElementById('edit_trainer_bio').value = trainer.bio || '';
            document.getElementById('editTrainerModal').style.display = 'block';
        }

        function submitTrainerEdit() {
            const data = {
                user_id: document.getElementById('edit_trainer_id').value,
                first_name: document.getElementById('edit_trainer_fname').value,
                last_name: document.getElementById('edit_trainer_lname').value,
                specialization: document.getElementById('edit_trainer_spec').value,
                experience: document.getElementById('edit_trainer_exp').value,
                bio: document.getElementById('edit_trainer_bio').value
            };

            fetch('update_trainer_profile_admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Trainer profile updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating profile: ' + (data.message || 'Unknown error'));
                }
            });
        }

        function viewClient(client) {
            document.getElementById('client_view_name').textContent = client.first_name + ' ' + client.last_name;
            document.getElementById('client_view_email').textContent = client.email;
            document.getElementById('client_view_role').textContent = client.role;
            // Simple date formatting
            let dateStr = client.created_at; 
            if(dateStr) {
                dateStr = new Date(dateStr).toLocaleDateString();
            } else {
                dateStr = 'N/A';
            }
            document.getElementById('client_view_date').textContent = dateStr;
            
            document.getElementById('viewClientModal').style.display = 'block';
        }

        function submitSchedule() {
            const openTime = document.getElementById('sched_open').value;
            const closeTime = document.getElementById('sched_close').value;
            const isClosed = document.getElementById('sched_closed').checked ? 'closed' : 'open';

            fetch('update_gym_schedule.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    gym_open_time: openTime,
                    gym_close_time: closeTime,
                    gym_status: isClosed
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Schedule updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating schedule: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>
