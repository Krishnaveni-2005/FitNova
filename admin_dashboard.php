<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Additional security: Only allow the specific admin email
if ($_SESSION['user_email'] !== 'krishnavenirnair2005@gmail.com') {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get admin info from session
$adminName = $_SESSION['user_name'] ?? 'Admin';
$adminEmail = $_SESSION['user_email'];
$adminInitials = strtoupper(substr($adminName, 0, 1) . substr(strrchr($adminName, ' '), 1, 1));

// Fetch Pending Trainers
require "db_connect.php";
$pendingTrainers = [];
$sql = "SELECT * FROM users WHERE role = 'trainer' AND account_status = 'pending' ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pendingTrainers[] = $row;
    }
}

// Fetch Active Trainers
$activeTrainers = [];
$sqlActive = "SELECT * FROM users WHERE role = 'trainer' AND account_status = 'active' ORDER BY created_at DESC";
$resultActive = $conn->query($sqlActive);
if ($resultActive->num_rows > 0) {
    while($row = $resultActive->fetch_assoc()) {
        $activeTrainers[] = $row;
    }
}

// Fetch Clients (Free, Pro, Elite)
$clients = [];
$sqlClients = "SELECT * FROM users WHERE role IN ('free', 'pro', 'elite') AND account_status = 'active' ORDER BY created_at DESC";
$resultClients = $conn->query($sqlClients);
if ($resultClients->num_rows > 0) {
    while($row = $resultClients->fetch_assoc()) {
        $clients[] = $row;
    }
}

// Fetch All Products
$products = [];
$sqlProducts = "SELECT * FROM products ORDER BY name ASC";
$resultProducts = $conn->query($sqlProducts);
if ($resultProducts && $resultProducts->num_rows > 0) {
    while($row = $resultProducts->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch Today's Classes (Trainer Schedules)
$todaysClasses = [];
$todayDate = date('Y-m-d');
$sqlClasses = "SELECT ts.*, u.first_name as trainer_first, u.last_name as trainer_last 
               FROM trainer_schedules ts 
               JOIN users u ON ts.trainer_id = u.user_id 
               WHERE ts.session_date = '$todayDate' 
               ORDER BY ts.session_time ASC";
$resultClasses = $conn->query($sqlClasses);
if ($resultClasses && $resultClasses->num_rows > 0) {
    while($row = $resultClasses->fetch_assoc()) {
        $todaysClasses[] = $row;
    }
}

// Fetch System Overview Stats
$sqlTotalUsers = "SELECT COUNT(*) as count FROM users";
$totalUsersCount = $conn->query($sqlTotalUsers)->fetch_assoc()['count'];

$activeTrainersCount = count($activeTrainers); // Already fetched above

// Fetch Recent Activity (New Users)
$recentActivities = [];
$sqlActivity = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$resultActivity = $conn->query($sqlActivity);
if ($resultActivity->num_rows > 0) {
    while($row = $resultActivity->fetch_assoc()) {
        $recentActivities[] = $row;
    }
}

// Fetch Gym Equipment from DB
$gymEquipment = [];
$sqlEquip = "SELECT * FROM gym_equipment";
$resultEquip = $conn->query($sqlEquip);
if ($resultEquip->num_rows > 0) {
    while($row = $resultEquip->fetch_assoc()) {
        $gymEquipment[] = $row;
    }
}

// Fetch Shop Orders
$shopOrders = [];
$sqlOrders = "SELECT o.*, u.first_name, u.last_name, u.email,
              (SELECT COUNT(*) FROM shop_order_items WHERE order_id = o.order_id) as item_count
              FROM shop_orders o
              JOIN users u ON o.user_id = u.user_id
              ORDER BY o.order_date DESC";
$resultOrders = $conn->query($sqlOrders);
if ($resultOrders && $resultOrders->num_rows > 0) {
    while($row = $resultOrders->fetch_assoc()) {
        $shopOrders[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Panel - FitNova</title>
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

        /* Bright Admin Sidebar */
        .admin-sidebar {
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

        .admin-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: linear-gradient(135deg, #0F2C59 0%, #1A3C6B 100%);
        }

        .admin-header h1 {
            color: white;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .admin-header p {
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
        }

        .logout-btn:hover {
            background: #dc2626;
            color: white;
        }

        /* Main Admin Content */
        .admin-main {
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

        .top-bar-actions {
            display: flex;
            gap: 12px;
        }

        .admin-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0F2C59;
            color: white;
        }

        .btn-primary:hover {
            background: #1A3C6B;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        /* Admin Stats Grid */
        .admin-stats {
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
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
        }

        .stat-box:nth-child(2)::before {
            background: linear-gradient(90deg, #ec4899, #f43f5e);
        }

        .stat-box:nth-child(3)::before {
            background: linear-gradient(90deg, #10b981, #14b8a6);
        }

        .stat-box:nth-child(4)::before {
            background: linear-gradient(90deg, #f59e0b, #ef4444);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .stat-box:nth-child(1) .stat-icon {
            background: #eef2ff;
            color: #4f46e5;
        }

        .stat-box:nth-child(2) .stat-icon {
            background: #fdf2f8;
            color: #ec4899;
        }

        .stat-box:nth-child(3) .stat-icon {
            background: #ecfdf5;
            color: #10b981;
        }

        .stat-box:nth-child(4) .stat-icon {
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

        .stat-trend {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        .trend-up {
            color: #059669;
        }

        .trend-down {
            color: #dc2626;
        }

        /* Management Sections */
        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Admin Grid Cards */
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }

        .admin-card {
            background: #fcfdfe;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .admin-card:hover {
            border-color: #0F2C59;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .card-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .card-subtitle {
            font-size: 13px;
            color: #64748b;
        }

        .card-info {
            margin: 16px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        .info-label {
            color: #64748b;
        }

        .info-value {
            color: #334155;
            font-weight: 600;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
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
            background: #f8fafc;
            color: #4f46e5;
            border-color: #4f46e5;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f1f5f9;
        }

        .data-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #475569;
        }

        .data-table tr:hover {
            background: #f8fafc;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 24px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
        }

        .welcome-banner h3 {
            font-size: 22px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .welcome-banner p {
            opacity: 0.9;
            font-size: 15px;
        }

        /* Grid Menu Hub Styles */
        .hub-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .hub-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }

        .hub-card:hover {
            transform: translateY(-10px);
            border-color: #4f46e5;
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.1), 0 10px 10px -5px rgba(79, 70, 229, 0.04);
        }

        .hub-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .hub-card:hover .hub-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .hub-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }

        .hub-desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
        }

        .btn-hub-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            margin-bottom: 20px;
            transition: all 0.2s;
        }

        .btn-hub-back:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        @media (max-width: 1024px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-main {
                margin-left: 0;
            }
        }
        
        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 2000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.5); 
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto; 
            padding: 30px; 
            border: 1px solid #888; 
            width: 500px; 
            max-width: 90%;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            animation: modalFadeIn 0.3s;
        }
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }
        .close:hover,
        .close:focus {
            color: #333;
            text-decoration: none;
            cursor: pointer;
        }
        .modal-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .modal-header h2 {
            margin: 0;
            color: #1e293b;
        }
        .detail-row {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #64748b;
            font-size: 14px;
        }
        .detail-value {
            color: #334155;
            font-weight: 500;
            text-align: right;
        }
    </style>

</head>

<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-header">
            <h1>FitNova</h1>
            <p>Admin Control Panel</p>
        </div>

        <div class="admin-info">
            <div class="admin-badge">
                <div class="admin-avatar"><?php echo $adminInitials; ?></div>
                <div>
                    <div class="admin-name"><?php echo htmlspecialchars($adminName); ?></div>
                    <div class="admin-role">System Administrator</div>
                </div>
            </div>
        </div>

        <nav class="nav-menu">
            <a href="#" class="nav-item active" onclick="showSection('hub')">
                <i class="fas fa-th-large"></i><span>Control Hub</span>
            </a>
            <a href="#" class="nav-item" onclick="showSection('dashboard')">
                <i class="fas fa-chart-line"></i><span>Overview</span>
            </a>
            <a href="#" class="nav-item" onclick="showSection('clients')">
                <i class="fas fa-users"></i><span>Clients</span>
            </a>
            <a href="#" class="nav-item" onclick="showSection('trainers')">
                <i class="fas fa-user-tie"></i><span>Trainers</span>
            </a>
            <a href="#" class="nav-item" onclick="showSection('products')">
                <i class="fas fa-box-open"></i><span>Products</span>
            </a>
            <a href="#" class="nav-item" onclick="showSection('classes')">
                <i class="fas fa-dumbbell"></i><span>Classes</span>
            </a>
            <a href="#" class="nav-item" onclick="showSection('orders')">
                <i class="fas fa-shopping-cart"></i><span>Orders</span>
            </a>
            <a href="#" class="nav-item" onclick="showSection('site')">
                <i class="fas fa-server"></i><span>Site Ops</span>
            </a>
            <a href="#" class="nav-item" onclick="showSection('offline')">
                <i class="fas fa-building"></i><span>Offline Gym</span>
            </a>
            <button class="logout-btn" onclick="logout()" style="position: relative; margin: 40px 20px 0; width: calc(100% - 40px); bottom: 0; left: 0;">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </button>
        </nav>
    </div>

    <!-- Main Admin Content -->
    <div class="admin-main">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div>
                <h3>FitNova Admin Environment 🚀</h3>
                <p>Logged in as: <?php echo htmlspecialchars($adminEmail); ?></p>
            </div>
            <div>
                <i class="fas fa-shield-alt" style="font-size: 40px; opacity: 0.3;"></i>
            </div>
        </div>

        <!-- Control Hub Section (Grid Menu) -->
        <div id="hub-section" class="section active">
            <div class="top-bar">
                <h2>Administrative Control Hub</h2>
                <div class="top-bar-actions">
                    <span class="badge badge-active">Active Session</span>
                </div>
            </div>

            <div class="hub-grid">
                <div class="hub-card" onclick="showSection('dashboard')">
                    <div class="hub-icon" style="background: #eef2ff; color: #4f46e5;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="hub-title">System Overview</div>
                    <div class="hub-desc">View real-time analytics, user growth, and revenue statistics.</div>
                </div>

                <div class="hub-card" onclick="showSection('clients')">
                    <div class="hub-icon" style="background: #fdf2f8; color: #ec4899;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="hub-title">Client Management</div>
                    <div class="hub-desc">Manage all registered members, handle subscriptions and profiles.</div>
                </div>

                <div class="hub-card" onclick="showSection('trainers')">
                    <div class="hub-icon" style="background: #ecfdf5; color: #10b981;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="hub-title">Trainer Portal</div>
                    <div class="hub-desc">Verify trainer applications and manage the professional database.</div>
                </div>

                <div class="hub-card" onclick="showSection('products')">
                    <div class="hub-icon" style="background: #e0f2fe; color: #0284c7;">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="hub-title">Products</div>
                    <div class="hub-desc">Manage shop inventory, categories, and product listings.</div>
                </div>

                <div class="hub-card" onclick="showSection('classes')">
                    <div class="hub-icon" style="background: #fdf4ff; color: #d946ef;">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <div class="hub-title">Classes</div>
                    <div class="hub-desc">Schedule and manage fitness classes and sessions.</div>
                </div>

                <div class="hub-card" onclick="showSection('orders')">
                    <div class="hub-icon" style="background: #fff1f2; color: #e11d48;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="hub-title">Orders</div>
                    <div class="hub-desc">Track and process customer orders and shipments.</div>
                </div>

                <div class="hub-card" onclick="showSection('site')">
                    <div class="hub-icon" style="background: #fffbeb; color: #f59e0b;">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="hub-title">Site Administration</div>
                    <div class="hub-desc">Configure system settings, backups, and platform maintenance.</div>
                </div>

                <div class="hub-card" onclick="showSection('offline')">
                    <div class="hub-icon" style="background: #fff7ed; color: #c2410c;">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="hub-title">Offline Gym</div>
                    <div class="hub-desc">Manage physical gym equipment and track on-site trainers.</div>
                </div>

                <div class="hub-card" onclick="showSection('reports')">
                    <div class="hub-icon" style="background: #f1f5f9; color: #475569;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="hub-title">Reports & Logs</div>
                    <div class="hub-desc">Generate detailed reports and monitor security audit logs.</div>
                </div>

                <div class="hub-card" onclick="showSection('orders')">
                    <div class="hub-icon" style="background: #fff0f6; color: #db2777;">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="hub-title">Shop Orders</div>
                    <div class="hub-desc">Manage customer orders, track payments, and update shipping status.</div>
                </div>

                <div class="hub-card" onclick="showSection('offline')">
                    <div class="hub-icon" style="background: #eef2ff; color: #7c3aed;">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="hub-title">Offline Gym</div>
                    <div class="hub-desc">Manage gym equipment, trainer clock-ins, and facility status.</div>
                </div>

                <div class="hub-card" onclick="showSection('settings')">
                    <div class="hub-icon" style="background: #ede9fe; color: #8b5cf6;">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="hub-title">System Settings</div>
                    <div class="hub-desc">Access global configuration and administrative preferences.</div>
                </div>
            </div>
        </div>

        <!-- Shop Orders Section -->
        <div id="orders-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            
            <div class="top-bar">
                <h2>Shop Orders</h2>
                <div class="top-bar-actions">
                    <button class="admin-btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh Data
                    </button>
                </div>
            </div>
            
            <div class="management-section">
                <div class="section-title">
                    <span>Recent Orders</span>
                    <span class="badge badge-active"><?php echo count($shopOrders); ?> Orders</span>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Client</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Delivery Date</th>
                                <th>Status</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($shopOrders) > 0): ?>
                                <?php foreach ($shopOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?><br>
                                        <small style="color:#888"><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td><?php echo $order['item_count']; ?> items</td>
                                    <td style="font-weight:600">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($order['delivery_date']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $order['order_status'] == 'Placed' ? 'badge-pending' : 'badge-active'; ?>">
                                            <?php echo htmlspecialchars($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['zip']); // Using zip as placeholder if no phone stored. Address is text. ?></td> 
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding: 20px;">No orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Dashboard Section -->
        <div id="dashboard-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            <div class="top-bar">
                <h2>System Overview</h2>
                <div class="top-bar-actions">
                    <button class="admin-btn btn-secondary"><i class="fas fa-download"></i> Export Data</button>
                    <button class="admin-btn btn-primary"><i class="fas fa-sync"></i> Refresh</button>
                </div>
            </div>

            <!-- Admin Stats -->
            <div class="admin-stats">
                <div class="stat-box">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($totalUsersCount); ?></div>
                            <div class="stat-label">Total Registered Users</div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i><span>Live Count</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($activeTrainersCount); ?></div>
                            <div class="stat-label">Active Trainers</div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-dumbbell"></i></div>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-check-circle"></i><span>Verified Professionals</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">₹0.00</div>
                            <div class="stat-label">Monthly Revenue</div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
                    </div>
                    <div class="stat-trend trend-up">
                        <span>No payment gateway active</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">99.9%</div>
                            <div class="stat-label">System Uptime</div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-server"></i></div>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-check"></i><span>All systems operational</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="management-section">
                <div class="section-title">Recent System Activity</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Event Type</th>
                            <th>User/Entity</th>
                            <th>Details</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recentActivities) > 0): ?>
                            <?php foreach ($recentActivities as $activity): ?>
                            <tr>
                                <td><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></td>
                                <td>
                                    <?php 
                                        if ($activity['role'] === 'trainer') echo 'Trainer Application';
                                        elseif ($activity['role'] === 'admin') echo 'Admin Created';
                                        else echo 'New User Registration';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($activity['email']); ?></td>
                                <td>
                                    <?php echo ucfirst($activity['role']); ?> account created via <?php echo ucfirst($activity['auth_provider']); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($activity['account_status'] === 'active' ? 'badge-active' : 'badge-pending'); ?>">
                                        <?php echo ucfirst($activity['account_status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;">No recent activity found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Client Management Section -->
        <div id="clients-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            <div class="top-bar">
                <h2>Client Management</h2>
                <button class="admin-btn btn-primary"><i class="fas fa-user-plus"></i> Add New Client</button>
            </div>

            <div class="management-section">
                <div class="section-title">
                    <span>All Registered Clients (<?php echo count($clients); ?>)</span>
                    <input type="text" placeholder="Search clients..." id="clientSearch" onkeyup="filterClients()"
                        style="padding: 8px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; color: #1e293b;">
                </div>
                
                <?php if(count($clients) > 0): ?>
                <div class="admin-grid" id="clientsGrid">
                    <?php foreach ($clients as $client): ?>
                        <div class="admin-card client-card" data-name="<?php echo strtolower($client['first_name'] . ' ' . $client['last_name']); ?>" data-email="<?php echo strtolower($client['email']); ?>">
                            <div class="card-header">
                                <div>
                                    <div class="card-icon" style="background: <?php echo ($client['role'] == 'elite' ? '#fdf2f8' : ($client['role'] == 'pro' ? '#eef2ff' : '#f1f5f9')); ?>; color: <?php echo ($client['role'] == 'elite' ? '#ec4899' : ($client['role'] == 'pro' ? '#4f46e5' : '#475569')); ?>;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <span class="badge <?php echo ($client['account_status'] === 'active' ? 'badge-active' : 'badge-inactive'); ?>">
                                    <?php echo ucfirst($client['account_status']); ?>
                                </span>
                            </div>
                            <div class="card-title"><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></div>
                            <div class="card-subtitle"><?php echo htmlspecialchars($client['email']); ?></div>
                            <div class="card-info">
                                <div class="info-row">
                                    <span class="info-label">Member Type</span>
                                    <span class="info-value"><?php echo ucfirst($client['role']); ?> Member</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Joined</span>
                                    <span class="info-value"><?php echo date('M d, Y', strtotime($client['created_at'])); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Phone</span>
                                    <span class="info-value"><?php echo htmlspecialchars($client['phone'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Auth Provider</span>
                                    <span class="info-value"><?php echo ucfirst($client['auth_provider']); ?></span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="action-btn" onclick='viewClientDetails(<?php echo json_encode($client, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>View Details</button>
                                <button class="action-btn" style="color: #dc2626; border-color: #fee2e2; background: #fef2f2;" onclick="handleClientAction(<?php echo $client['user_id']; ?>, 'delete')">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="padding: 20px; color: #64748b; font-style: italic;">No registered clients found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Trainer Management Section -->
        <div id="trainers-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            <div class="top-bar">
                <h2>Trainer Management</h2>
                <button class="admin-btn btn-primary"><i class="fas fa-user-plus"></i> Add New Trainer</button>
            </div>
            <div class="management-section">
                <div class="section-title">Pending Trainer Approvals</div>
                
                <?php if (count($pendingTrainers) > 0): ?>
                <div class="admin-grid">
                    <?php foreach ($pendingTrainers as $trainer): ?>
                        <div class="admin-card">
                            <div class="card-header">
                                <div>
                                    <div class="card-icon" style="background: #fff7ed; color: #ea580c;">
                                        <i class="fas fa-user-clock"></i>
                                    </div>
                                </div>
                                <span class="badge badge-pending">Pending</span>
                            </div>
                            <div class="card-title"><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></div>
                            <div class="card-subtitle"><?php echo htmlspecialchars($trainer['email']); ?></div>
                            <div class="card-info">
                                <div class="info-row">
                                    <span class="info-label">Specialization</span>
                                    <span class="info-value"><?php echo htmlspecialchars($trainer['trainer_specialization'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Experience</span>
                                    <span class="info-value"><?php echo htmlspecialchars($trainer['trainer_experience'] ?? '0'); ?> Years</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Certification</span>
                                    <span class="info-value">
                                        <?php if (!empty($trainer['trainer_certification'])): ?>
                                            <a href="<?php echo htmlspecialchars($trainer['trainer_certification']); ?>" target="_blank" style="color: blue; text-decoration: underline;">View File</a>
                                        <?php else: ?>
                                            Not Uploaded
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="action-btn" style="color: #059669; border: 1px solid #059669; background: #ecfdf5;" onclick="handleTrainerAction(<?php echo $trainer['user_id']; ?>, 'approve')">Approve</button>
                                <button class="action-btn" style="color: #dc2626; border: 1px solid #dc2626; background: #fef2f2;" onclick="handleTrainerAction(<?php echo $trainer['user_id']; ?>, 'reject')">Reject</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="padding: 20px; color: #64748b; font-style: italic;">No pending trainer requests.</p>
                <?php endif; ?>
            </div>

            <div class="management-section">
                <div class="section-title">
                    <span>Active Trainers (<?php echo count($activeTrainers); ?>)</span>
                    <input type="text" placeholder="Search trainers..." id="trainerSearch" onkeyup="filterTrainers()"
                        style="padding: 8px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; color: #1e293b;">
                </div>
                <?php if (count($activeTrainers) > 0): ?>
                <div class="admin-grid" id="activeTrainersGrid">
                    <?php foreach ($activeTrainers as $trainer): ?>
                        <div class="admin-card trainer-card" data-name="<?php echo strtolower($trainer['first_name'] . ' ' . $trainer['last_name']); ?>" data-email="<?php echo strtolower($trainer['email']); ?>">
                            <div class="card-header">
                                <div>
                                    <div class="card-icon" style="background: #f0fdf4; color: #16a34a;">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                </div>
                                <span class="badge badge-active">Active</span>
                            </div>
                            <div class="card-title"><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></div>
                            <div class="card-subtitle"><?php echo htmlspecialchars($trainer['email']); ?></div>
                            <div class="card-info">
                                <div class="info-row">
                                    <span class="info-label">Specialization</span>
                                    <span class="info-value"><?php echo htmlspecialchars($trainer['trainer_specialization'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="action-btn">View Profile</button>
                                <button class="action-btn" style="color: #dc2626;" onclick="handleTrainerAction(<?php echo $trainer['user_id']; ?>, 'delete')">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="padding: 20px; color: #64748b; font-style: italic;">No active trainers found.</p>
                <?php endif; ?>
            </div>
        </div>



        <!-- Products Section -->
        <div id="products-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            <div class="top-bar">
                <h2>Product Management</h2>
                <button class="admin-btn btn-primary" onclick="openProductModal()"><i class="fas fa-plus"></i> Add New Product</button>
            </div>
            <div class="management-section">
                <div class="section-title">Inventory & Listings (<?php echo count($products); ?>)</div>
                
                <?php if (count($products) > 0): ?>
                <div class="admin-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="admin-card">
                            <div style="height: 140px; background: #f1f5f9; border-radius: 8px; margin-bottom: 15px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div class="card-title"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="card-subtitle"><?php echo ucfirst($product['category']); ?></div>
                            <div class="card-info">
                                <div class="info-row">
                                    <span class="info-label">Price</span>
                                    <span class="info-value">₹<?php echo number_format($product['price']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Rating</span>
                                    <span class="info-value">⭐ <?php echo $product['rating']; ?> (<?php echo $product['review_count']; ?>)</span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="action-btn" onclick='openProductModal(<?php echo json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Edit</button>
                                <button class="action-btn" style="color: #dc2626;" onclick="deleteProduct(<?php echo $product['product_id']; ?>)">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="padding: 20px; color: #64748b; font-style: italic;">No products found in the database.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Classes Section -->
        <div id="classes-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            <div class="top-bar">
                <h2>Class Management</h2>
                <button class="admin-btn btn-primary"><i class="fas fa-calendar-plus"></i> Schedule Class</button>
            </div>
            <div class="management-section">
                <div class="section-title">Today's Sessions</div>
                
                <?php if (count($todaysClasses) > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Class / Session</th>
                                <th>Trainer</th>
                                <th>Status</th>
                                <th>Client(s)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todaysClasses as $class): ?>
                            <tr>
                                <td style="font-weight: 700; color: #0F2C59;"><?php echo date('h:i A', strtotime($class['session_time'])); ?></td>
                                <td><?php echo htmlspecialchars($class['session_type']); ?></td>
                                <td><?php echo htmlspecialchars($class['trainer_first'] . ' ' . $class['trainer_last']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($class['status'] === 'completed' ? 'badge-active' : 'badge-pending'); ?>">
                                        <?php echo ucfirst($class['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($class['client_name']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px; color: #64748b;">
                        <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 20px; display: block; opacity: 0.5;"></i>
                        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">No classes is going on</h3>
                        <p>There are no scheduled sessions for today.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Orders Section -->
        <div id="orders-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            <div class="top-bar">
                <h2>Order Management</h2>
            </div>
            <div class="management-section">
                <div class="section-title">Recent Orders</div>
                <p style="color: #64748b; padding: 40px; text-align: center;">
                    <i class="fas fa-shopping-cart" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                    Order processing system coming soon.
                </p>
            </div>
        </div>

        <!-- Site Administration Section -->
        <div id="site-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            <div class="top-bar">
                <h2>Site Administration</h2>
            </div>
            <div class="management-section">
                <div class="section-title">Site Configuration</div>
                <p style="color: #64748b; padding: 40px; text-align: center;">
                    <i class="fas fa-server" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                    Site administration tools coming soon.
                </p>
            </div>
        </div>

        <!-- Offline Gym Management Section -->
        <div id="offline-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            <div class="top-bar">
                <h2>Offline Gym Management</h2>
                <div class="top-bar-actions">
                    <button class="admin-btn btn-primary"><i class="fas fa-plus"></i> Add Equipment</button>
                </div>
            </div>

            <!-- Equipment Details -->
            <div class="management-section">
                <div class="section-title">Equipment Status Details</div>
                <div class="admin-grid">
                    <?php if (count($gymEquipment) > 0): ?>
                    <?php foreach ($gymEquipment as $equip): ?>
                        <div class="admin-card">
                            <div class="card-header">
                                <div>
                                    <div class="card-icon" style="background: <?php echo ($equip['color_class'] == 'success' ? '#dcfce7' : ($equip['color_class'] == 'warning' ? '#fef3c7' : '#fee2e2')); ?>; color: <?php echo ($equip['color_class'] == 'success' ? '#16a34a' : ($equip['color_class'] == 'warning' ? '#d97706' : '#dc2626')); ?>;">
                                        <i class="<?php echo htmlspecialchars($equip['icon']); ?>"></i>
                                    </div>
                                </div>
                                <span class="badge" style="background: <?php echo ($equip['color_class'] == 'success' ? '#dcfce7' : ($equip['color_class'] == 'warning' ? '#fef3c7' : '#fee2e2')); ?>; color: <?php echo ($equip['color_class'] == 'success' ? '#16a34a' : ($equip['color_class'] == 'warning' ? '#d97706' : '#dc2626')); ?>;">
                                    <?php echo htmlspecialchars($equip['status']); ?>
                                </span>
                            </div>
                            <div class="card-title"><?php echo htmlspecialchars($equip['name']); ?></div>
                            <div class="card-info">
                                <div class="info-row">
                                    <span class="info-label">Total Units</span>
                                    <span class="info-value"><?php echo $equip['total_units']; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Available</span>
                                    <span class="info-value"><?php echo $equip['available_units']; ?></span>
                                </div>
                                <div class="progress-container" style="height: 6px; background: #f1f5f9; border-radius: 10px; margin-top: 10px; overflow: hidden;">
                                    <div class="progress-bar" style="width: <?php echo ($equip['available_units'] / $equip['total_units']) * 100; ?>%; height: 100%; object-fit: cover; background: <?php echo ($equip['color_class'] == 'success' ? '#22c55e' : ($equip['color_class'] == 'warning' ? '#f59e0b' : '#ef4444')); ?>; border-radius: 10px;"></div>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="action-btn" onclick="openUpdateEquipmentModal(<?php echo $equip['id']; ?>, '<?php echo htmlspecialchars($equip['name']); ?>', <?php echo $equip['total_units']; ?>, <?php echo $equip['available_units']; ?>)">Update Status</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <p style="padding: 20px;">No equipment data found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Trainers On Site -->
            <div class="management-section">
                <div class="section-title">Trainers On Site</div>
                <?php if (count($activeTrainers) > 0): ?>
                <div class="admin-grid">
                    <?php foreach ($activeTrainers as $trainer): ?>
                        <div class="admin-card">
                            <div class="card-header">
                                <div>
                                    <div class="card-icon" style="background: #f0fdf4; color: #16a34a;">
                                        <i class="fas fa-id-badge"></i>
                                    </div>
                                </div>
                                <span class="badge badge-active">On Site</span>
                            </div>
                            <div class="card-title"><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></div>
                            <div class="card-subtitle"><?php echo htmlspecialchars($trainer['email']); ?></div>
                            <div class="card-info">
                                <div class="info-row">
                                    <span class="info-label">Shift Status</span>
                                    <span class="info-value">Checked In - <?php echo date('H:i'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Current Zone</span>
                                    <span class="info-value">General Gym Floor</span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="action-btn" onclick="viewTrainerSchedule(<?php echo $trainer['user_id']; ?>)">View Schedule</button>
                                <button class="action-btn" style="color: #64748b;" onclick="clockOutTrainer(<?php echo $trainer['user_id']; ?>, '<?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?>')">Clock Out</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="padding: 20px; color: #64748b; font-style: italic;">No trainers currently marked as on-site.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reports Section -->
        <div id="reports-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            <div class="top-bar">
                <h2>Reports & Analytics</h2>
                <button class="admin-btn btn-primary"><i class="fas fa-download"></i> Export All Reports</button>
            </div>
            <div class="management-section">
                <div class="section-title">System Analytics & Reports</div>
                <p style="color: #64748b; padding: 40px; text-align: center;">
                    <i class="fas fa-chart-bar" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                    Advanced analytics and reporting features coming soon.
                </p>
            </div>
        </div>

        <!-- Settings Section -->
        <div id="settings-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            <div class="top-bar">
                <h2>System Settings</h2>
                <button class="admin-btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
            <div class="management-section">
                <div class="section-title">Configuration & Preferences</div>
                <p style="color: #64748b; padding: 40px; text-align: center;">
                    <i class="fas fa-cog" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                    System configuration panel coming soon.
                </p>
            </div>
        </div>
    </div>

    <!-- Client Detail Modal -->
    <div id="clientModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('clientModal')">&times;</span>
            <div class="modal-header">
                <h2>Client Details</h2>
            </div>
            <div id="modalBody">
                <!-- Content will be injected by JS -->
            </div>
        </div>
    </div>

    <!-- Product Modal (Add/Edit) -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('productModal')">&times;</span>
            <div class="modal-header">
                <h2 id="productModalTitle">Add New Product</h2>
            </div>
            <div id="productModalBody">
                <form id="productForm" onsubmit="event.preventDefault(); saveProduct();">
                    <input type="hidden" id="p_id" name="product_id">
                    
                    <div class="detail-row">
                        <label class="detail-label">Product Name</label>
                        <input type="text" id="p_name" name="name" class="editable-input" style="display:block; width: 60%;" required>
                    </div>

                    <div class="detail-row">
                        <label class="detail-label">Category</label>
                        <select id="p_category" name="category" class="editable-input" style="display:block; width: 60%;">
                            <option value="men">Men's Wear</option>
                            <option value="women">Women's Wear</option>
                            <option value="supplements">Supplements</option>
                            <option value="equipment">Equipment</option>
                        </select>
                    </div>

                    <div class="detail-row">
                        <label class="detail-label">Price (₹)</label>
                        <input type="number" id="p_price" name="price" step="0.01" class="editable-input" style="display:block; width: 60%;" required>
                    </div>

                    <div class="detail-row">
                        <label class="detail-label">Image URL</label>
                        <input type="text" id="p_image" name="image_url" class="editable-input" style="display:block; width: 60%;" placeholder="https://...">
                    </div>

                    <div style="margin-top: 20px; text-align: right;">
                        <button type="button" class="action-btn" onclick="closeModal('productModal')">Cancel</button>
                        <button type="submit" class="admin-btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Equipment Update Modal -->
    <div id="equipmentModal" class="modal">
        <div class="modal-content" style="width: 400px;">
            <span class="close" onclick="closeModal('equipmentModal')">&times;</span>
            <div class="modal-header">
                <h2>Update Equipment Status</h2>
            </div>
            <div>
                <form id="equipmentForm" onsubmit="event.preventDefault(); saveEquipmentStatus();">
                    <input type="hidden" id="eq_id">
                    
                    <div class="detail-row">
                        <label class="detail-label">Equipment</label>
                        <span class="detail-value" id="eq_name_display" style="font-weight: 700;"></span>
                    </div>

                    <div class="detail-row">
                        <label class="detail-label">Total Units</label>
                        <span class="detail-value" id="eq_total_display"></span>
                    </div>

                    <div class="detail-row">
                        <label class="detail-label">Available Now</label>
                        <input type="number" id="eq_available" class="editable-input" style="display:block; width: 80px; text-align: right;" min="0" required>
                    </div>

                    <div style="margin-top: 20px; text-align: right;">
                         <button type="button" class="action-btn" onclick="closeModal('equipmentModal')">Cancel</button>
                         <button type="submit" class="admin-btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showSection(section) {
            // Update sidebar active state
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('onclick').includes(`'${section}'`)) {
                    item.classList.add('active');
                }
            });
            
            // Show corresponding section
            document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
            document.getElementById(section + '-section').classList.add('active');
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function logout() {
            if (confirm('Are you sure you want to logout from the admin panel?')) {
                window.location.href = 'logout.php';
            }
        }
        function filterTrainers() {
            const input = document.getElementById('trainerSearch');
            const filter = input.value.toLowerCase();
            const cards = document.getElementsByClassName('trainer-card');

            for (let i = 0; i < cards.length; i++) {
                const name = cards[i].getAttribute('data-name');
                const email = cards[i].getAttribute('data-email');
                if (name.includes(filter) || email.includes(filter)) {
                    cards[i].style.display = "";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }

        function handleTrainerAction(trainerId, action) {
            if (!confirm(`Are you sure you want to ${action} this trainer?`)) return;

            fetch('admin_trainer_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: action, trainer_id: trainerId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Request failed');
            });
        }
        function handleClientAction(clientId, action) {
             const actionText = action === 'delete' ? 'permanently delete' : action;
             if (!confirm(`Are you sure you want to ${actionText} this client?`)) return;

             fetch('admin_client_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: action, client_id: clientId })
             })
             .then(res => res.json())
             .then(data => {
                 if(data.status === 'success') {
                     alert(data.message);
                     location.reload();
                 } else {
                     alert('Error: ' + data.message);
                 }
             })
             .catch(err => {
                 console.error(err);
                 alert('Request failed');
             });
        }

        function filterClients() {
            const input = document.getElementById('clientSearch');
            const filter = input.value.toLowerCase();
            const cards = document.getElementsByClassName('client-card');

            for (let i = 0; i < cards.length; i++) {
                const name = cards[i].getAttribute('data-name');
                const email = cards[i].getAttribute('data-email');
                if (name.includes(filter) || email.includes(filter)) {
                    cards[i].style.display = "";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }

        // Modal Functions
        function viewClientDetails(client) {
            const modal = document.getElementById('clientModal');
            const body = document.getElementById('modalBody');
            
            body.innerHTML = `
                <div class="detail-row">
                    <span class="detail-label">Full Name</span>
                    <span class="detail-value">${client.first_name} ${client.last_name}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email Address</span>
                    <span class="detail-value">${client.email}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone Number</span>
                    <span class="detail-value">${client.phone || 'N/A'}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Membership Type</span>
                    <span class="detail-value" style="text-transform: capitalize;">${client.role}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Account Status</span>
                    <span class="detail-value" style="text-transform: capitalize;">${client.account_status}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Joined Date</span>
                    <span class="detail-value">${new Date(client.created_at).toLocaleDateString()}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Auth Provider</span>
                    <span class="detail-value" style="text-transform: capitalize;">${client.auth_provider || 'Local'}</span>
                </div>
            `;
            
            modal.style.display = "block";
        }

        function closeModal(modalId) {
            document.getElementById(modalId || 'clientModal').style.display = "none";
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }

        // Product Management Functions
        function openProductModal(product = null) {
            const modal = document.getElementById('productModal');
            document.getElementById('productForm').reset();
            
            if (product) {
                document.getElementById('productModalTitle').innerText = 'Edit Product';
                document.getElementById('p_id').value = product.product_id;
                document.getElementById('p_name').value = product.name;
                document.getElementById('p_category').value = product.category;
                document.getElementById('p_price').value = product.price;
                document.getElementById('p_image').value = product.image_url;
            } else {
                document.getElementById('productModalTitle').innerText = 'Add New Product';
                document.getElementById('p_id').value = '';
            }
            
            modal.style.display = "block";
        }

        function saveProduct() {
            const formData = new FormData(document.getElementById('productForm'));
            formData.append('action', 'save');

            fetch('admin_product_action.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Request failed');
            });
        }

        function deleteProduct(id) {
            if(!confirm('Are you sure you want to delete this product?')) return;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('product_id', id);

            fetch('admin_product_action.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Request failed');
            });
        }

        // Equipment Management
        function openUpdateEquipmentModal(id, name, total, current) {
            document.getElementById('equipmentModal').style.display = 'block';
            document.getElementById('eq_id').value = id;
            document.getElementById('eq_name_display').innerText = name;
            document.getElementById('eq_total_display').innerText = total;
            document.getElementById('eq_available').value = current;
            document.getElementById('eq_available').max = total;
        }

        function saveEquipmentStatus() {
            const id = document.getElementById('eq_id').value;
            const available = document.getElementById('eq_available').value;

            fetch('admin_equipment_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_status', id: id, available: available })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    location.reload(); 
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Request failed');
            });
        }

        // Trainer Attendance Management
        function viewTrainerSchedule(trainerId) {
            // Redirect to trainer schedule page
            window.open('trainer_schedule.php?trainer_id=' + trainerId, '_blank');
        }

        function clockOutTrainer(trainerId, trainerName) {
            if (!confirm(`Are you sure you want to clock out ${trainerName}?`)) return;

            fetch('admin_trainer_attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'clock_out', trainer_id: trainerId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Request failed');
            });
        }
    </script>
</body>

</html>


