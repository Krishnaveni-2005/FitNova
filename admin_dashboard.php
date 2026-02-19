<?php
session_start();
require "db_connect.php";

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

// Fetch Clients Waiting for Trainer with Preferences
$waitingSql = "SELECT u.user_id, u.first_name, u.last_name, u.email, 
                      r.goal, r.training_style, r.notes
               FROM users u
               LEFT JOIN (
                   SELECT client_id, goal, training_style, notes 
                   FROM client_trainer_requests 
                   WHERE request_id IN (SELECT MAX(request_id) FROM client_trainer_requests GROUP BY client_id)
               ) r ON u.user_id = r.client_id
               WHERE u.assignment_status = 'looking_for_trainer'";
$waitingClients = [];
$res = $conn->query($waitingSql);
if($res) while($r = $res->fetch_assoc()) $waitingClients[] = $r;

// Fetch Active Trainers for Dropdown
// Fetch Active Trainers for Dropdown
$trainerSql = "SELECT user_id, first_name, last_name, trainer_specialization as specialization FROM users WHERE role='trainer' AND account_status='active'";
$availTrainers = [];
$res = $conn->query($trainerSql);
if($res) while($r = $res->fetch_assoc()) $availTrainers[] = $r;

// Fetch Pending Trainers

$pendingTrainers = [];
$sql = "SELECT * FROM users WHERE role = 'trainer' AND account_status = 'pending' AND trainer_type = 'online' ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pendingTrainers[] = $row;
    }
}

// Fetch Active Trainers (All users with role='trainer' and status!='pending')
$activeTrainers = [];
$sqlActive = "SELECT * FROM users WHERE role = 'trainer' AND account_status != 'pending' ORDER BY created_at DESC";
$resultActive = $conn->query($sqlActive);
if ($resultActive->num_rows > 0) {
    while($row = $resultActive->fetch_assoc()) {
        $activeTrainers[] = $row;
    }
}

// Fetch Specific Offline Trainers (Removed demo trainers)
// Fetch Active Offline Trainers (On-Site Staff)
$offlineTrainers = [];
$sqlOffline = "SELECT u.*, 
               (SELECT check_in_time FROM trainer_attendance ta 
                WHERE ta.trainer_id = u.user_id AND DATE(ta.check_in_time) = CURDATE() 
                ORDER BY ta.check_in_time DESC LIMIT 1) as check_in_time
               FROM users u 
               WHERE u.role = 'trainer' AND u.trainer_type = 'offline' AND u.account_status = 'active'
               ORDER BY u.first_name ASC";
$resultOffline = $conn->query($sqlOffline);
if ($resultOffline && $resultOffline->num_rows > 0) {
    while($row = $resultOffline->fetch_assoc()) {
        $offlineTrainers[] = $row;
    }
}

// Fetch Clients (All Statuses)
$clients = [];
$sqlClients = "SELECT * FROM users WHERE role IN ('free', 'pro', 'elite') ORDER BY created_at DESC";
$resultClients = $conn->query($sqlClients);
if ($resultClients->num_rows > 0) {
    while($row = $resultClients->fetch_assoc()) {
        $clients[] = $row;
    }
}

// Fetch Gym Owners (Admins) - Excluding the main system administrator
$gymOwners = [];
// Exclude the main admin email
$sqlAdmins = "SELECT * FROM users WHERE role = 'admin' AND email != 'krishnavenirnair2005@gmail.com' ORDER BY created_at ASC";
$resAdmins = $conn->query($sqlAdmins);
if($resAdmins && $resAdmins->num_rows > 0) {
    while($r = $resAdmins->fetch_assoc()) {
        $gymOwners[] = $r;
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

// Fetch Subscription Plans
$subPlans = [];
$sqlPlans = "SELECT * FROM subscription_plans";
$resPlans = $conn->query($sqlPlans);
if($resPlans && $resPlans->num_rows > 0) {
    while($r = $resPlans->fetch_assoc()) {
        $subPlans[] = $r;
    }
}

// Fetch Today's Classes (Trainer Schedules)
$todaysClasses = [];
$todayDate = date('Y-m-d');
// Join schedules with bookings to get actual sessions
$sqlClasses = "SELECT ts.session_time, ts.status,
                      COALESCE(cs.class_name, 'Personal Session') as session_type,
                      u.first_name as trainer_first, u.last_name as trainer_last,
                      GROUP_CONCAT(CONCAT(c.first_name, ' ', c.last_name) SEPARATOR ', ') as client_name
               FROM trainer_schedules ts 
               JOIN users u ON ts.trainer_id = u.user_id 
               LEFT JOIN trainer_bookings tb ON ts.schedule_id = tb.schedule_id
               LEFT JOIN users c ON tb.client_id = c.user_id
               LEFT JOIN class_sessions cs ON tb.session_id = cs.session_id
               WHERE ts.session_date = '$todayDate' AND ts.status != 'cancelled'
               GROUP BY ts.schedule_id
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

// Calculate Monthly Revenue from Subscriptions
$planPrices = [
    'lite' => 499,
    'pro' => 999,
    'elite' => 1999
];

$revenueResult = $conn->query("SELECT role, COUNT(*) as count FROM users WHERE role IN ('lite', 'pro', 'elite') GROUP BY role");
$monthlyRevenue = 0;
while ($row = $revenueResult->fetch_assoc()) {
    $monthlyRevenue += $planPrices[$row['role']] * $row['count'];
}

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

// Fetch Shop Orders & Calculate Monthly Sales
$shopOrders = [];
$monthlySalesData = [];
$sqlOrders = "SELECT o.*, u.first_name, u.last_name, u.email,
              (SELECT COUNT(*) FROM shop_order_items WHERE order_id = o.order_id) as item_count
              FROM shop_orders o
              JOIN users u ON o.user_id = u.user_id
              ORDER BY o.order_date DESC";
$resultOrders = $conn->query($sqlOrders);
if ($resultOrders && $resultOrders->num_rows > 0) {
    while($row = $resultOrders->fetch_assoc()) {
        $shopOrders[] = $row;
        // Group by month for chart/table
        $monthKey = date('F', strtotime($row['order_date']));
        if(!isset($monthlySalesData[$monthKey])) $monthlySalesData[$monthKey] = 0;
        $monthlySalesData[$monthKey] += $row['total_amount'];
    }
}

// Calculate Equipment Health
$eqTotal = 0; $eqAvail = 0;
foreach($gymEquipment as $e) { $eqTotal += $e['total_units']; $eqAvail += $e['available_units']; }
$eqHealth = ($eqTotal > 0) ? round(($eqAvail / $eqTotal) * 100) : 100;

// Calculate Trainer Availability (Active vs Total)
$totalTrainersRes = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='trainer'");
$totalTrainers = $totalTrainersRes->fetch_assoc()['c'];
$trainerAvailPct = ($totalTrainers > 0) ? round(($activeTrainersCount / $totalTrainers) * 100) : 0;

// Calculate User Growth Stats
$oneWeekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
$newUsersRes = $conn->query("SELECT COUNT(*) as c FROM users WHERE created_at >= '$oneWeekAgo'");
$newUsersCount = $newUsersRes->fetch_assoc()['c'];
$priorUsersCount = $totalUsersCount - $newUsersCount;
$growthPct = ($priorUsersCount > 0) ? round(($newUsersCount / $priorUsersCount) * 100, 1) : 100;

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
            display: flex;
            flex-direction: column;
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
            overflow-y: auto;
            flex: 1; /* Take up remaining space */
            display: flex;
            flex-direction: column;
        }

        /* Custom Scrollbar for Sidebar */
        .nav-menu::-webkit-scrollbar {
            width: 4px;
        }
        .nav-menu::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 4px;
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
            flex-shrink: 0;
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
            margin: 20px;
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
            flex-shrink: 0;
            width: calc(100% - 40px); /* Ensure correct width */
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
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 15px;
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
        .editable-input {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 5px 10px;
            font-family: inherit;
            font-size: 14px;
        }

        /* Order Tracker Styles */
        .track-container {
            margin: 30px 0;
            position: relative;
        }
        .validation-step-list {
            display: flex;
            justify-content: space-between;
            position: relative;
            list-style: none;
        }
        .validation-step-list::before {
            content: "";
            position: absolute;
            top: 20px;
            left: 0;
            width: 100%;
            height: 4px;
            background: #e2e8f0;
            z-index: 1;
        }
        .validation-step {
            position: relative;
            z-index: 2;
            text-align: center;
            width: 33.33%;
        }
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #cbd5e1;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            border: 4px solid #fff;
            transition: all 0.3s;
        }
        .step-label {
            margin-top: 10px;
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Active/Completed States */
        .step-active .step-icon {
            background: #f59e0b; /* Yellow/Orange */
            box-shadow: 0 0 0 2px #fef3c7;
        }
        .step-completed .step-icon {
            background: #10b981; /* Green */
        }
        
        .progress-bar-track {
            position: absolute;
            top: 20px;
            left: 0;
            height: 4px;
            background: linear-gradient(to right, #10b981, #f59e0b);
            z-index: 1;
            width: 0%;
            transition: width 0.3s;
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
            <a href="#" class="nav-item" onclick="showSection('clients')">
                <i class="fas fa-handshake"></i><span>Trainer Matching</span>
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
            <a href="#" class="nav-item" onclick="showSection('subscriptions')">
                <i class="fas fa-crown"></i><span>Subscriptions</span>
            </a>

            <a href="#" class="nav-item" onclick="showSection('offline')">
                <i class="fas fa-building"></i><span>Offline Gym</span>
            </a>
            <a href="#" class="nav-item" onclick="showSection('reports')">
                <i class="fas fa-file-alt"></i><span>Reports</span>
            </a>
            <a href="#" class="nav-item" onclick="showSection('settings')">
                <i class="fas fa-cog"></i><span>Settings</span>
            </a>
        </nav>
        <button class="logout-btn" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </button>
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

                <div class="hub-card" onclick="showSection('clients')">
                    <div class="hub-icon" style="background: #fef3c7; color: #b45309;">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="hub-title">Trainer Matching</div>
                    <div class="hub-desc">
                        <strong style="font-size: 1.2em;"><?php echo count($waitingClients); ?></strong> Pending Requests.<br>
                        Assign trainers to waiting clients.
                    </div>
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



                <div class="hub-card" onclick="showSection('subscriptions')">
                    <div class="hub-icon" style="background: #eef2ff; color: #4f46e5;">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="hub-title">Subscriptions</div>
                    <div class="hub-desc">Manage user plans and membership tiers.</div>
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
                                <th>Action</th>
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
                                        <?php 
                                            $status = $order['order_status'];
                                            $badgeClass = 'badge-pending';
                                            if($status == 'Delivered') $badgeClass = 'badge-active';
                                            elseif($status == 'Cancelled') $badgeClass = 'badge-inactive';
                                            elseif($status == 'Shipped' || $status == 'Processing') $badgeClass = 'badge-active'; // Or custom blue
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['zip']); // Using zip as placeholder if no phone stored. Address is text. ?></td> 
                                    <td>
                                        <button class="action-btn" onclick="openOrderModal(<?php echo $order['order_id']; ?>, '<?php echo htmlspecialchars($order['order_status']); ?>')">
                                            Track/Update
                                        </button>
                                    </td>
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
                    <button class="admin-btn btn-secondary" onclick="window.location.href='admin_export_csv.php'"><i class="fas fa-download"></i> Export Data</button>
                    <button class="admin-btn btn-primary" onclick="location.reload()"><i class="fas fa-sync"></i> Refresh</button>
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
                            <div class="stat-value">₹<?php echo number_format($monthlyRevenue, 2); ?></div>
                            <div class="stat-label">Monthly Revenue</div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-check-circle"></i><span>Razorpay Active</span>
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

            <!-- Manual Matching Section -->
            <div class="management-section" style="border: 2px solid #fcd34d; background: #fffbeb; margin-bottom: 20px;">
                <div class="section-title" style="color: #b45309;"><i class="fas fa-handshake"></i> Clients Awaiting Trainer</div>
                
                <?php if (empty($waitingClients)): ?>
                    <p style="padding: 20px; text-align: center; color: #b45309; font-style: italic;">No Lite users are currently requesting a trainer.</p>
                <?php else: ?>
                    <div class="admin-grid">
                        <?php foreach ($waitingClients as $client): ?>
                        <div class="admin-card" style="border-color: #fcd34d;">
                            <div class="card-title"><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></div>
                            <div class="card-subtitle">Looking for Match</div>
                            
                            <?php if(!empty($client['goal'])): ?>
                            <div style="background: #fffbeb; padding: 8px; border-radius: 6px; margin: 8px 0; font-size: 11px; border: 1px dashed #fcd34d;">
                                <strong>Goal:</strong> <?php echo htmlspecialchars($client['goal']); ?><br>
                                <strong>Style:</strong> <?php echo htmlspecialchars($client['training_style']); ?><br>
                                <?php if(!empty($client['notes'])): ?>
                                <em>"<?php echo htmlspecialchars($client['notes']); ?>"</em>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <div style="margin-top:10px;">
                                <label style="font-size:12px; font-weight:600; color:#b45309;">Suggest Trainer:</label>
                                <select id="trainer-select-<?php echo $client['user_id']; ?>" style="width:100%; padding:5px; border-radius:4px; border:1px solid #fcd34d; margin-top:2px;">
                                    <option value="">Select Trainer...</option>
                                    <?php 
                                    $matches = [];
                                    $others = [];
                                    foreach ($availTrainers as $t) {
                                        $spec = $t['specialization'] ?? '';
                                        // Match logic: Check if goal or style applies to trainer's specialization (Bidirectional matching)
                                        if ((!empty($client['goal']) && (stripos($spec, $client['goal']) !== false || stripos($client['goal'], $spec) !== false)) || 
                                            (!empty($client['training_style']) && (stripos($spec, $client['training_style']) !== false || stripos($client['training_style'], $spec) !== false))) {
                                            $matches[] = $t;
                                        } else {
                                            $others[] = $t;
                                        }
                                    }
                                    
                                    // If matches exist, show ONLY matches (as per request)
                                    // But adding "Show Others" might be useful. For now, strict as requested + fallback.
                                    if (!empty($matches)) {
                                        foreach ($matches as $t): ?>
                                            <option value="<?php echo $t['user_id']; ?>" style="font-weight:bold; color:#166534; background:#dcfce7;">
                                                <?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?> • <?php echo htmlspecialchars($t['specialization']); ?>
                                            </option>
                                        <?php endforeach;
                                    } else {
                                        // Fallback if no matches
                                        $target = htmlspecialchars($client['goal'] ?? 'Preferences');
                                        echo '<option disabled style="color:#b45309; font-weight:600; background:#fff7ed;">No exact match for "' . $target . '". Showing available trainers:</option>';
                                        foreach ($others as $t): ?>
                                            <option value="<?php echo $t['user_id']; ?>">
                                                <?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?> • <?php echo htmlspecialchars($t['specialization']); ?>
                                            </option>
                                        <?php endforeach;
                                    }
                                    ?>
                                </select>
                                <button onclick="notifyTrainer(<?php echo $client['user_id']; ?>)" style="width:100%; margin-top:5px; background:#b45309; color:white; border:none; padding:6px; border-radius:4px; cursor:pointer; font-weight:600;">Notify Trainer</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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

        <!-- Subscriptions Section -->
        <div id="subscriptions-section" class="section">
            <button class="btn-hub-back" onclick="showSection('hub')">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </button>
            <div class="top-bar">
                <h2>Subscription Management</h2>
            </div>
            
            <div class="management-section">
                <div class="section-title">
                    <span>Plan Management</span>
                </div>
                <div class="admin-grid">
                    <?php foreach($subPlans as $plan): ?>
                    <div class="admin-card">
                        <div class="card-header">
                            <div class="card-icon" style="background:var(--primary-color); color:white;">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <span class="badge badge-active"><?php echo htmlspecialchars($plan['name']); ?></span>
                        </div>
                        <div class="card-title">Monthly: ₹<?php echo number_format($plan['price_monthly']); ?></div>
                        <div class="card-subtitle">Yearly: ₹<?php echo number_format($plan['price_yearly']); ?></div>
                        <div class="card-actions">
                            <button class="action-btn" onclick='openPlanModal(<?php echo json_encode($plan, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Edit Details</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="management-section">
                <div class="section-title">
                    <span>Client Plans</span>
                    <span class="badge badge-active"><?php echo count($clients); ?> Users</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Email</th>
                                <th>Current Plan</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($client['email']); ?></td>
                                <td>
                                    <span class="badge" style="background: <?php echo ($client['role'] == 'elite' ? '#fdf2f8' : ($client['role'] == 'pro' ? '#eef2ff' : '#f1f5f9')); ?>; color: <?php echo ($client['role'] == 'elite' ? '#ec4899' : ($client['role'] == 'pro' ? '#4f46e5' : '#475569')); ?>;">
                                        <?php echo ucfirst($client['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($client['account_status'] === 'active' ? 'badge-active' : 'badge-inactive'); ?>">
                                        <?php echo ucfirst($client['account_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn" onclick="openSubscriptionModal(<?php echo $client['user_id']; ?>, '<?php echo htmlspecialchars(addslashes($client['first_name'] . ' ' . $client['last_name'])); ?>', '<?php echo $client['role']; ?>')">
                                        <i class="fas fa-edit"></i> Edit Plan
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
                                <button class="action-btn" onclick='viewTrainerDetails(<?php echo json_encode($trainer, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>View Profile</button>
                                <button class="action-btn" style="color: #dc2626;" onclick="handleTrainerAction(<?php echo $trainer['user_id']; ?>, 'delete')">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="padding: 20px; color: #64748b; font-style: italic;">No trainers found.</p>
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
                                <div class="info-row">
                                    <span class="info-label">Stock</span>
                                    <span class="info-value" style="color: <?php echo $product['stock_quantity'] < 10 ? '#dc2626' : '#16a34a'; ?>; font-weight: 700;">
                                        <?php echo $product['stock_quantity'] ?? 0; ?> Units
                                    </span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="action-btn" onclick='openProductModal(<?php echo json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Edit</button>
                                <button class="action-btn" style="background: #eff6ff; color: #1e40af; border: 1px solid #dbeafe;" onclick="openStockModal(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['stock_quantity'] ?? 0; ?>)">Stock</button>
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
                    <!-- Button Removed -->
                </div>
            </div>


            <!-- Gym Owners Details -->
            <div class="management-section">
                <div class="section-title">Offline Gym Owners</div>
                <?php if (count($gymOwners) > 0): ?>
                <div class="admin-grid">
                    <?php foreach ($gymOwners as $owner): ?>
                        <div class="admin-card" style="border-left: 4px solid #4f46e5;">
                            <div class="card-header">
                                <div>
                                    <div class="card-icon" style="background: #eef2ff; color: #4f46e5;">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                </div>
                                <span class="badge badge-active">Owner</span>
                            </div>
                            <div class="card-title"><?php echo htmlspecialchars($owner['first_name'] . ' ' . $owner['last_name']); ?></div>
                            <div class="card-subtitle"><?php echo htmlspecialchars($owner['email']); ?></div>
                            <div class="card-info">
                                <div class="info-row">
                                    <span class="info-label">Role</span>
                                    <span class="info-value">System Administrator</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Contact</span>
                                    <span class="info-value"><?php echo !empty($owner['phone']) ? htmlspecialchars($owner['phone']) : 'N/A'; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Joined</span>
                                    <span class="info-value"><?php echo date('M d, Y', strtotime($owner['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="action-btn" onclick="alert('Contact details: <?php echo htmlspecialchars($owner['email']); ?>')">Contact Owner</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="padding: 20px; color: #64748b; font-style: italic;">No gym owners found.</p>
                <?php endif; ?>
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
                <?php if (count($offlineTrainers) > 0): ?>
                <div class="admin-grid">
                    <?php foreach ($offlineTrainers as $trainer): 
                        $isCheckedIn = !empty($trainer['check_in_time']);
                        $statusBadgeClass = $isCheckedIn ? 'badge-active' : 'badge-inactive';
                        $statusText = $isCheckedIn ? 'On Site' : 'Off Duty';
                        $checkInDisplay = $isCheckedIn ? 'Checked In - ' . date('h:i A', strtotime($trainer['check_in_time'])) : 'Not Checked In';
                        $zone = $isCheckedIn ? 'General Gym Floor' : 'N/A';
                    ?>
                        <div class="admin-card">
                            <div class="card-header">
                                <div>
                                    <div class="card-icon" style="background: <?php echo $isCheckedIn ? '#f0fdf4' : '#f1f5f9'; ?>; color: <?php echo $isCheckedIn ? '#16a34a' : '#64748b'; ?>;">
                                        <i class="fas fa-id-badge"></i>
                                    </div>
                                </div>
                                <span class="badge <?php echo $statusBadgeClass; ?>"><?php echo $statusText; ?></span>
                            </div>
                            <div class="card-title"><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></div>
                            <div class="card-subtitle"><?php echo htmlspecialchars($trainer['email']); ?></div>
                            <div class="card-info">
                                <div class="info-row">
                                    <span class="info-label">Shift Status</span>
                                    <span class="info-value"><?php echo $checkInDisplay; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Current Zone</span>
                                    <span class="info-value"><?php echo $zone; ?></span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="action-btn" onclick="viewTrainerSchedule(<?php echo $trainer['user_id']; ?>)">View Schedule</button>
                                <?php if($isCheckedIn): ?>
                                    <button class="action-btn" style="color: #64748b;" onclick="clockOutTrainer(<?php echo $trainer['user_id']; ?>, '<?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?>')">Clock Out</button>
                                <?php else: ?>
                                    <button class="action-btn" disabled style="color: #94a3b8; cursor: not-allowed; opacity: 0.7;">Clock Out</button>
                                <?php endif; ?>
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
                <button class="admin-btn btn-primary" onclick="window.print()"><i class="fas fa-download"></i> Export All Reports</button>
            </div>
            <div style="font-size: 14px; color: #64748b; margin-bottom: 20px;">Real-time platform performance metrics</div>
            
            <!-- KPI Cards Row -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                
                <!-- Revenue -->
                <div style="background: white; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600; margin-bottom: 5px;">Revenue</div>
                    <div style="font-size: 24px; font-weight: 800; color: #1e293b;">₹<?php echo number_format($monthlyRevenue); ?></div>
                    <div style="font-size: 12px; color: #10b981; margin-top: 5px; font-weight: 600;">+12% <span style="color: #94a3b8; font-weight: 400;">Compare to last month</span></div>
                </div>

                <!-- All Orders -->
                <div style="background: white; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <div style="font-size: 13px; color: #64748b; font-weight: 600; margin-bottom: 5px;">All Orders</div>
                            <div style="font-size: 24px; font-weight: 800; color: #1e293b;"><?php echo count($shopOrders); ?></div>
                        </div>
                        <div style="background: #eff6ff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                    <div style="font-size: 12px; color: #94a3b8; margin-top: 5px;">Total shop transactions</div>
                </div>

                <!-- Today's Sales (Dark) -->
                <div style="background: #1e293b; padding: 20px; border-radius: 16px; color: white;">
                    <div style="font-size: 13px; opacity: 0.8; font-weight: 600; margin-bottom: 5px;">Today's Sales</div>
                    <div style="font-size: 24px; font-weight: 800;">₹<?php echo number_format(round($monthlyRevenue / 30)); ?></div>
                    <div style="font-size: 12px; opacity: 0.6; margin-top: 5px;">Approximate daily avg</div>
                </div>

                <!-- Active Trainers -->
                <div style="background: white; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600; margin-bottom: 5px;">Active Trainers</div>
                    <div style="font-size: 24px; font-weight: 800; color: #1e293b;"><?php echo $activeTrainersCount; ?></div>
                    <div style="font-size: 12px; color: #10b981; margin-top: 5px; background: #ecfdf5; display: inline-block; padding: 2px 8px; border-radius: 12px; font-weight: 600;">Active <span style="color: #94a3b8; font-weight: 400;">staff members</span></div>
                </div>

                <!-- Total Users -->
                <div style="background: white; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600; margin-bottom: 5px;">Total Users</div>
                    <div style="font-size: 24px; font-weight: 800; color: #1e293b;"><?php echo $totalUsersCount; ?></div>
                    <div style="font-size: 12px; color: #10b981; margin-top: 5px; background: #ecfdf5; display: inline-block; padding: 2px 8px; border-radius: 12px; font-weight: 600;">+<?php echo $growthPct; ?>% <span style="color: #94a3b8; font-weight: 400;">New this week (+<?php echo $newUsersCount; ?>)</span></div>
                </div>

            </div>

            <!-- Charts Row 1 -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; align-items: start;">
            <!-- Data Tables Row 1 -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; align-items: start;">
                
                <!-- Shop Sales Performance -->
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: #1e293b;">Shop Sales Performance</h3>
                        <button style="border: none; background: #f1f5f9; padding: 4px 12px; border-radius: 6px; font-size: 12px; color: #475569; cursor: pointer;" onclick="exportSalesToCSV()">Export</button>
                    </div>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: left;">
                                    <th style="padding-bottom: 12px; padding-left: 8px;">Month</th>
                                    <th style="padding-bottom: 12px;">Total Sales</th>
                                    <th style="padding-bottom: 12px;">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (!empty($monthlySalesData)) {
                                    foreach($monthlySalesData as $m => $rev) {
                                        $perf = ($rev > 5000) ? 'Strong' : 'Steady';
                                        $perfColor = ($rev > 5000) ? '#dcfce7; color: #166534' : '#dbeafe; color: #1e40af';
                                ?>
                                <tr style="border-bottom: 1px solid #f8fafc; font-size: 13px;">
                                    <td style="padding: 12px 8px; color: #334155; font-weight: 500;"><?php echo $m; ?></td>
                                    <td style="padding: 12px 0; font-weight: 700; color: #1e293b;">₹<?php echo number_format($rev); ?></td>
                                    <td style="padding: 12px 0;"><span style="font-size: 11px; padding: 2px 8px; border-radius: 12px; background: <?php echo $perfColor; ?>"><?php echo $perf; ?></span></td>
                                </tr>
                                <?php 
                                    } 
                                } else {
                                ?>
                                <tr><td colspan="3" style="padding: 20px; text-align: center; color: #94a3b8;">No sales data available yet.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Registrations List -->
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0; height: 100%;">
                    <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px;">Recent Registrations</h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php 
                        if(isset($recentActivities) && count($recentActivities) > 0):
                            foreach(array_slice($recentActivities, 0, 5) as $u):
                                $initials = strtoupper(substr($u['first_name'], 0, 1) . substr($u['last_name'], 0, 1));
                        ?>
                        <div style="display: flex; align-items: center; gap: 12px;">
                           <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; color: #64748b;"><?php echo $initials; ?></div>
                           <div>
                               <div style="font-size: 13px; font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></div>
                               <div style="font-size: 11px; color: #64748b;">Role: <?php echo ucfirst($u['role']); ?></div>
                           </div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <button style="width: 100%; margin-top: 20px; padding: 10px; border: 1px solid #e2e8f0; background: white; border-radius: 8px; color: #64748b; font-size: 13px; font-weight: 500; cursor: pointer;" onclick="showSection('clients')">View All Users</button>
                </div>

            </div>

            <!-- Bottom Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <!-- System Summary -->
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: #1e293b;">System Summary</h3>
                        <button style="border: none; background: #f1f5f9; padding: 4px 12px; border-radius: 6px; font-size: 12px; color: #475569; cursor: pointer;" onclick="showSection('trainers')">View All</button>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 8px;">
                            <div style="width: 40px; height: 40px; background: #3b82f6; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 14px; font-weight: 600; color: #1e293b;">Equipment Health</div>
                                <div style="height: 6px; background: #f1f5f9; border-radius: 3px; margin-top: 8px; overflow: hidden;">
                                    <div style="width: <?php echo $eqHealth; ?>%; height: 100%; background: #3b82f6;"></div>
                                </div>
                            </div>
                            <div style="font-weight: 700; color: #3b82f6;"><?php echo $eqHealth; ?>%</div>
                        </div>
                    </div>

                    <div>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <div style="width: 40px; height: 40px; background: #10b981; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 14px; font-weight: 600; color: #1e293b;">Trainer Availability</div>
                                <div style="height: 6px; background: #f1f5f9; border-radius: 3px; margin-top: 8px; overflow: hidden;">
                                    <div style="width: <?php echo $trainerAvailPct; ?>%; height: 100%; background: #10b981;"></div>
                                </div>
                            </div>
                            <div style="font-weight: 700; color: #10b981;"><?php echo $trainerAvailPct; ?>%</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions List -->
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0;">
                     <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: #1e293b;">Recent Transactions</h3>
                        <button style="border: none; background: #f1f5f9; padding: 4px 12px; border-radius: 6px; font-size: 12px; color: #475569; cursor: pointer;" onclick="showSection('orders')">View All</button>
                    </div>
                     <div style="display: flex; flex-direction: column; gap: 12px; max-height: 200px; overflow-y: auto;">
                        <?php 
                        if(isset($shopOrders) && count($shopOrders) > 0):
                            foreach(array_slice($shopOrders, 0, 4) as $order):
                        ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 36px; height: 36px; background: #f0fdf4; color: #16a34a; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 600; color: #1e293b;">Order #<?php echo $order['order_id']; ?></div>
                                    <div style="font-size: 11px; color: #64748b;"><?php echo $order['item_count']; ?> items • <?php echo date('M d', strtotime($order['order_date'])); ?></div>
                                </div>
                            </div>
                            <div style="font-size: 13px; font-weight: 700; color: #16a34a;">+₹<?php echo number_format($order['total_amount']); ?></div>
                        </div>
                        <?php endforeach; else: ?>
                            <div style="text-align: center; color: #94a3b8; font-size: 13px; padding: 20px;">No recent transactions</div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <script>
            function exportSalesToCSV() {
                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "Month,Total Sales,Performance\n";
                // Select rows from the table
                document.querySelectorAll('#reports-section table tbody tr').forEach(function(row) {
                    let cols = row.querySelectorAll('td');
                    if(cols.length === 3) {
                        let rowData = [
                            cols[0].innerText,
                            cols[1].innerText.replace('₹', '').replace(',', ''),
                            cols[2].innerText
                        ];
                        csvContent += rowData.join(",") + "\n";
                    }
                });
                var encodedUri = encodeURI(csvContent);
                var link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "shop_sales_performance.csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
            </script>


            <!-- Init Charts -->

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

    <!-- Trainer Detail Modal -->
    <div id="trainerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('trainerModal')">&times;</span>
            <div class="modal-header">
                <h2>Trainer Profile</h2>
            </div>
            <div id="trainerModalBody">
                <!-- Content injected by JS -->
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
                        <label class="detail-label">Stock Quantity</label>
                        <input type="number" id="p_stock" name="stock_quantity" class="editable-input" style="display:block; width: 60%;" min="0" required>
                    </div>

                    <div class="detail-row">
                        <label class="detail-label">Image URL (Optional)</label>
                        <input type="text" id="p_image" name="image_url" class="editable-input" style="display:block; width: 60%;" placeholder="https://...">
                    </div>

                    <div class="detail-row">
                        <label class="detail-label">Or Upload Image</label>
                        <input type="file" id="p_image_file" name="image_file" class="editable-input" style="display:block; width: 60%;" accept="image/*">
                        <small style="color: #64748b; font-size: 11px;">Recommended: JPG/PNG, Max 2MB</small>
                    </div>

                    <div class="detail-row">
                        <label class="detail-label">Description</label>
                        <textarea id="p_description" name="description" class="editable-input" style="display:block; width: 100%; height: 80px; resize: vertical;" placeholder="Product description..."></textarea>
                    </div>

                    <div style="margin-top: 20px; text-align: right;">
                        <button type="button" class="action-btn" onclick="closeModal('productModal')">Cancel</button>
                        <button type="submit" class="admin-btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Stock Modal -->
    <div id="stockModal" class="modal">
        <div class="modal-content" style="width: 400px;">
            <span class="close" onclick="closeModal('stockModal')">&times;</span>
            <div class="modal-header">
                <h2>Update Stock</h2>
            </div>
            <form id="stockForm" onsubmit="event.preventDefault(); saveStock();">
                <input type="hidden" id="stock_p_id">
                <div class="detail-row">
                    <label class="detail-label">Product</label>
                    <span class="detail-value" id="stock_p_name" style="font-weight:700;"></span>
                </div>
                <div class="detail-row">
                    <label class="detail-label">Stock Quantity</label>
                    <input type="number" id="stock_qty" class="editable-input" style="width:80px; text-align:right;" min="0" required>
                </div>
                <div style="text-align: right; margin-top:20px;">
                     <button type="submit" class="admin-btn btn-primary">Update Stock</button>
                </div>
            </form>
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

    <!-- Edit Plan Modal -->
    <div id="planModal" class="modal">
        <div class="modal-content" style="width: 500px;">
            <span class="close" onclick="closeModal('planModal')">&times;</span>
            <div class="modal-header">
                <h2>Edit Plan: <span id="plan_name_title"></span></h2>
            </div>
            <form id="planForm" onsubmit="event.preventDefault(); savePlan();">
                <input type="hidden" id="plan_id">
                <div class="detail-row">
                    <label class="detail-label">Monthly Price (₹)</label>
                    <input type="number" id="plan_price_m" class="editable-input" style="width:100%" step="0.01">
                </div>
                <div class="detail-row">
                    <label class="detail-label">Yearly Price (₹)</label>
                    <input type="number" id="plan_price_y" class="editable-input" style="width:100%" step="0.01">
                </div>
                <div class="detail-row">
                    <label class="detail-label">Features (One per line)</label>
                    <textarea id="plan_features" class="editable-input" style="width:100%; height:150px; resize:vertical; background:#f8fafc; border:1px solid #e2e8f0; padding:10px;"></textarea>
                </div>
                <div style="text-align: right; margin-top:20px;">
                     <button type="submit" class="admin-btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Status Modal -->
    <div id="orderStatusModal" class="modal">
        <div class="modal-content" style="width: 400px;">
            <span class="close" onclick="closeModal('orderStatusModal')">&times;</span>
            <div class="modal-header">
                <h2>Update Order Status</h2>
            </div>
            
            <!-- Tracker Visualization -->
            <div class="track-container">
                <div class="progress-bar-track" id="track-line"></div>
                <div class="validation-step-list">
                    <div class="validation-step" id="step-placed">
                        <div class="step-icon"><i class="fas fa-shopping-basket"></i></div>
                        <div class="step-label">Order Placed</div>
                    </div>
                    <div class="validation-step" id="step-transit">
                        <div class="step-icon"><i class="fas fa-shipping-fast"></i></div>
                        <div class="step-label">In Transit</div>
                    </div>
                    <div class="validation-step" id="step-completed">
                        <div class="step-icon"><i class="fas fa-check"></i></div>
                        <div class="step-label">Completed</div>
                    </div>
                </div>
            </div>

            <form id="orderStatusForm" onsubmit="event.preventDefault(); saveOrderStatus();">
                <input type="hidden" id="order_id_track">
                <div class="detail-row">
                    <label class="detail-label">Order ID</label>
                    <span class="detail-value" id="order_display_id" style="font-weight:700;"></span>
                </div>
                <div class="detail-row">
                    <label class="detail-label">Current Status</label>
                    <select id="order_status_select" class="editable-input" style="width: 100%;" onchange="updateTrackerVisual(this.value)">
                        <option value="Placed">Placed</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div style="text-align: right; margin-top:20px;">
                     <button type="submit" class="admin-btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- Custom Dialog Modal -->
    <div id="customDialogModal" class="modal" style="z-index: 10000; align-items: center; justify-content: center;">
        <div class="modal-content" style="max-width: 400px; text-align: center; padding: 30px; border-radius: 16px; margin: 0;">
             <div id="dialogIcon" style="font-size: 40px; margin-bottom: 20px;"></div>
             <h3 id="dialogTitle" style="margin-bottom: 10px; color: #1e293b; font-size: 20px;">Notification</h3>
             <p id="dialogMessage" style="color: #64748b; margin-bottom: 25px; line-height: 1.5; font-size: 15px;"></p>
             <div id="dialogActions" style="display: flex; gap: 10px; justify-content: center;"></div>
        </div>
    </div>

    <!-- Subscription Modal -->
    <div id="subscriptionModal" class="modal">
        <div class="modal-content" style="width: 400px;">
            <span class="close" onclick="closeModal('subscriptionModal')">&times;</span>
            <div class="modal-header">
                <h2>Edit Subscription Plan</h2>
            </div>
            <div id="subModalBody">
                <form id="subscriptionForm" onsubmit="event.preventDefault(); saveSubscription();">
                    <input type="hidden" id="sub_user_id">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; color: #64748b; font-size: 14px;">User</label>
                        <div id="sub_user_name" style="font-weight: 600; font-size: 16px; color: #1e293b;"></div>
                    </div>
                    <div style="margin-bottom: 25px;">
                        <label for="sub_role" style="display: block; margin-bottom: 8px; color: #64748b; font-size: 14px;">Membership Tier</label>
                        <select id="sub_role" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px;">
                            <option value="free">Free Member</option>
                            <option value="lite">Lite Member (Formerly Pro)</option>
                            <option value="pro">Pro Member (Formerly Elite)</option>
                        </select>
                    </div>
                    <div style="text-align: right;">
                         <button type="submit" class="admin-btn btn-primary" style="width: 100%; justify-content: center;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Existing functions...

        // Subscription Management
        function openSubscriptionModal(userId, userName, currentRole) {
            document.getElementById('subscriptionModal').style.display = 'block';
            document.getElementById('sub_user_id').value = userId;
            document.getElementById('sub_user_name').innerText = userName;
            document.getElementById('sub_role').value = currentRole;
        }

        function saveSubscription() {
            const userId = document.getElementById('sub_user_id').value;
            const newRole = document.getElementById('sub_role').value;

            fetch('admin_subscription_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, new_role: newRole })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    showCustomAlert(data.message, 'success', () => location.reload());
                } else {
                    showCustomAlert('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showCustomAlert('Request failed', 'error');
            });
        }

        // Plan Management JS
        function openPlanModal(plan) {
            document.getElementById('planModal').style.display = 'block';
            document.getElementById('plan_name_title').innerText = plan.name;
            document.getElementById('plan_id').value = plan.plan_id;
            document.getElementById('plan_price_m').value = plan.price_monthly;
            document.getElementById('plan_price_y').value = plan.price_yearly;
            
            // Features
            let features = '';
            try {
                const fData = typeof plan.features === 'string' ? JSON.parse(plan.features) : plan.features;
                features = Array.isArray(fData) ? fData.join('\n') : fData;
            } catch(e) { features = plan.features; }
            document.getElementById('plan_features').value = features;
        }

        function savePlan() {
             const data = {
                 plan_id: document.getElementById('plan_id').value,
                 price_monthly: document.getElementById('plan_price_m').value,
                 price_yearly: document.getElementById('plan_price_y').value,
                 features: document.getElementById('plan_features').value
             };
             
             fetch('admin_plan_update.php', {
                 method: 'POST',
                 headers: {'Content-Type': 'application/json'},
                 body: JSON.stringify(data)
             })
             .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    showCustomAlert(data.message, 'success', () => location.reload());
                } else {
                    showCustomAlert(data.message, 'error');
                }
            })
            .catch(err => console.error(err));
        }

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

        // Custom Dialog Logic
        function closeDialog() {
            document.getElementById('customDialogModal').style.display = 'none';
        }

        function showCustomAlert(message, type = 'info', callback = null) {
            const modal = document.getElementById('customDialogModal');
            const icon = document.getElementById('dialogIcon');
            const title = document.getElementById('dialogTitle');
            const msg = document.getElementById('dialogMessage');
            const actions = document.getElementById('dialogActions');
            
            // Reset
            actions.innerHTML = '';
            
            // Config
            if(type === 'success') {
                icon.innerHTML = '<i class="fas fa-check-circle" style="color: #10b981;"></i>';
                title.innerText = 'Success';
            } else if(type === 'error') {
                icon.innerHTML = '<i class="fas fa-exclamation-circle" style="color: #ef4444;"></i>';
                title.innerText = 'Error';
            } else {
                icon.innerHTML = '<i class="fas fa-info-circle" style="color: #3b82f6;"></i>';
                title.innerText = 'Information';
            }
            
            msg.innerText = message;
            
            // OK Button
            const btn = document.createElement('button');
            btn.className = 'admin-btn btn-primary';
            btn.innerText = 'OK';
            btn.style.minWidth = '100px';
            btn.onclick = function() {
                closeDialog();
                if(callback) callback();
            };
            actions.appendChild(btn);
            
            modal.style.display = 'flex';
        }

        function showCustomConfirm(message, onConfirm) {
            const modal = document.getElementById('customDialogModal');
            const icon = document.getElementById('dialogIcon');
            const title = document.getElementById('dialogTitle');
            const msg = document.getElementById('dialogMessage');
            const actions = document.getElementById('dialogActions');
            
            actions.innerHTML = '';
            
            icon.innerHTML = '<i class="fas fa-question-circle" style="color: #f59e0b;"></i>';
            title.innerText = 'Confirm Action';
            msg.innerText = message;
            
            // Cancel Button
            const btnCancel = document.createElement('button');
            btnCancel.className = 'admin-btn btn-secondary';
            btnCancel.innerText = 'Cancel';
            btnCancel.onclick = closeDialog;
            
            // Confirm Button
            const btnConfirm = document.createElement('button');
            btnConfirm.className = 'admin-btn btn-primary';
            btnConfirm.innerText = 'Confirm';
            btnConfirm.style.background = '#0F2C59';
            btnConfirm.onclick = function() {
                closeDialog();
                if(onConfirm) onConfirm();
            };
            
            actions.appendChild(btnCancel);
            actions.appendChild(btnConfirm);
            
            modal.style.display = 'flex';
        }

        function logout() {
            showCustomConfirm('Are you sure you want to logout from the admin panel?', () => {
                window.location.href = 'logout.php';
            });
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
            showCustomConfirm(`Are you sure you want to ${action} this trainer?`, () => {
                fetch('admin_trainer_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: action, trainer_id: trainerId })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        showCustomAlert(data.message, 'success', () => location.reload());
                    } else {
                        showCustomAlert('Error: ' + data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showCustomAlert('Request failed', 'error');
                });
            });
        }
        function handleClientAction(clientId, action) {
             const actionText = action === 'delete' ? 'permanently delete' : action;
             showCustomConfirm(`Are you sure you want to ${actionText} this client?`, () => {
                 fetch('admin_client_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: action, client_id: clientId })
                 })
                 .then(res => res.json())
                 .then(data => {
                     if(data.status === 'success') {
                         showCustomAlert(data.message, 'success', () => location.reload());
                     } else {
                         showCustomAlert('Error: ' + data.message, 'error');
                     }
                 })
                 .catch(err => {
                     console.error(err);
                     showCustomAlert('Request failed', 'error');
                 });
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
        // Trainer Modal Function
        // Trainer Modal Function - Editable Version
        function viewTrainerDetails(trainer) {
            const modal = document.getElementById('trainerModal');
            const body = document.getElementById('trainerModalBody');
            
            // Format certs - editable as text for now
            const certs = trainer.certifications || '';
            
            // Fallback for image
            const imgHtml = trainer.image_url 
                ? `<img src="${trainer.image_url}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">` 
                : '<i class="fas fa-user"></i>';

            body.innerHTML = `
                <form id="editTrainerForm" onsubmit="event.preventDefault(); saveTrainerDetails(${trainer.user_id});">
                    <div style="display: flex; gap: 20px; margin-bottom: 20px; align-items: start;">
                        <div style="width: 100px; height: 100px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; color: #64748b; flex-shrink: 0;">
                            ${imgHtml}
                        </div>
                        <div style="flex: 1;">
                            <div style="display:flex; gap: 10px; margin-bottom: 10px;">
                                <input type="text" id="t_first_name" class="editable-input" value="${trainer.first_name}" style="font-weight:700; color:#0F2C59; width: 48%;" placeholder="First Name">
                                <input type="text" id="t_last_name" class="editable-input" value="${trainer.last_name}" style="font-weight:700; color:#0F2C59; width: 48%;" placeholder="Last Name">
                            </div>
                            <input type="email" id="t_email" class="editable-input" value="${trainer.email}" style="width: 100%; color: #64748b; margin-bottom: 8px;">
                            <span class="badge badge-active" style="text-transform: capitalize;">${trainer.account_status}</span>
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Phone</span>
                        <input type="text" id="t_phone" class="editable-input" value="${trainer.phone || ''}" style="width: 60%; text-align: right;">
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Specialization</span>
                        <input type="text" id="t_spec" class="editable-input" value="${trainer.trainer_specialization || trainer.specialization || ''}" style="width: 60%; text-align: right;">
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Experience (Years)</span>
                        <input type="number" id="t_exp" class="editable-input" value="${trainer.experience_years || 0}" style="width: 60%; text-align: right;">
                    </div>

                    <div class="detail-row" style="border-bottom: none;">
                        <div style="width:100%">
                            <span class="detail-label">About</span>
                            <textarea id="t_bio" class="editable-input" style="width: 100%; height: 100px; margin-top: 8px; resize: vertical; padding: 10px;">${trainer.bio || ''}</textarea>
                        </div>
                    </div>

                    <div style="margin-top: 20px; text-align: right; border-top: 1px solid #eee; padding-top: 20px;">
                         <button type="button" class="action-btn" onclick="closeModal('trainerModal')">Cancel</button>
                         <button type="submit" class="admin-btn btn-primary" style="display: inline-flex;">Save Changes</button>
                    </div>
                </form>
            `;
            
            modal.style.display = "block";
        }

        function saveTrainerDetails(trainerId) {
            const data = {
                trainer_id: trainerId,
                first_name: document.getElementById('t_first_name').value,
                last_name: document.getElementById('t_last_name').value,
                email: document.getElementById('t_email').value,
                phone: document.getElementById('t_phone').value,
                trainer_specialization: document.getElementById('t_spec').value,
                experience_years: document.getElementById('t_exp').value,
                bio: document.getElementById('t_bio').value
            };

            fetch('admin_trainer_update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    showCustomAlert(data.message, 'success', () => location.reload());
                } else {
                    showCustomAlert('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showCustomAlert('Request failed', 'error');
            });
        }

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
                document.getElementById('p_stock').value = product.stock_quantity ?? 50;
                document.getElementById('p_image').value = product.image_url;
                document.getElementById('p_description').value = product.description || '';
            } else {
                document.getElementById('productModalTitle').innerText = 'Add New Product';
                document.getElementById('p_id').value = '';
                document.getElementById('p_stock').value = '50';
                document.getElementById('p_description').value = '';
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
                    showCustomAlert(data.message, 'success', () => location.reload());
                } else {
                    showCustomAlert('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showCustomAlert('Request failed', 'error');
            });
        }

        function deleteProduct(id) {
            showCustomConfirm('Are you sure you want to delete this product?', () => {
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
                        showCustomAlert(data.message, 'success', () => location.reload());
                    } else {
                        showCustomAlert('Error: ' + data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showCustomAlert('Request failed', 'error');
                });
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
                    showCustomAlert(data.message, 'success', () => location.reload());
                } else {
                    showCustomAlert('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showCustomAlert('Request failed', 'error');
            });
        }

        // Trainer Attendance Management
        function viewTrainerSchedule(trainerId) {
            // Open in modal instead of new tab
            const modal = document.createElement('div');
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
            modal.style.zIndex = '9999';
            modal.style.display = 'flex';
            modal.style.justifyContent = 'center';
            modal.style.alignItems = 'center';
            
            modal.innerHTML = `
                <div style="background:white; width:90%; height:90%; border-radius:12px; overflow:hidden; position:relative; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
                    <button onclick="this.closest('div').parentElement.remove()" style="position:absolute; top:15px; right:20px; z-index:100; background:#ef4444; color:white; border:none; width:30px; height:30px; border-radius:50%; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 5px rgba(0,0,0,0.2);"><i class="fas fa-times"></i></button>
                    <iframe src="trainer_schedule.php?trainer_id=${trainerId}" style="width:100%; height:100%; border:none;"></iframe>
                </div>
            `;
            
            document.body.appendChild(modal);
        }

        function clockOutTrainer(trainerId, trainerName) {
            showCustomConfirm(`Are you sure you want to clock out ${trainerName}?`, () => {
                fetch('admin_trainer_attendance.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'clock_out', trainer_id: trainerId })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        showCustomAlert(data.message, 'success', () => location.reload());
                    } else {
                        showCustomAlert('Error: ' + data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showCustomAlert('Request failed', 'error');
                });
            });
        }
    function notifyTrainer(clientId) {
        const trainerId = document.getElementById('trainer-select-' + clientId).value;
        if (!trainerId) {
            showCustomAlert('Please select a trainer first.', 'error');
            return;
        }
        showCustomConfirm('Inform this trainer about the opportunity?', () => {
            fetch('admin_notify_trainer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ client_id: clientId, trainer_id: trainerId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showCustomAlert('Trainer notified!', 'success', () => location.reload());
                } else {
                    showCustomAlert('Error: ' + data.message, 'error');
                }
            })
            .catch(err => console.error(err));
        });
    }

    // Stock Management
    function openStockModal(id, name, qty) {
        document.getElementById('stockModal').style.display = 'block';
        document.getElementById('stock_p_id').value = id;
        document.getElementById('stock_p_name').innerText = name;
        document.getElementById('stock_qty').value = qty;
    }

    function saveStock() {
        showCustomConfirm('Update stock for this product?', () => {
            const formData = new FormData();
            formData.append('action', 'update_stock');
            formData.append('product_id', document.getElementById('stock_p_id').value);
            formData.append('stock_quantity', document.getElementById('stock_qty').value);

            fetch('admin_product_action.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    showCustomAlert(data.message, 'success', () => location.reload());
                } else {
                    showCustomAlert('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showCustomAlert('Request failed', 'error');
            });
        });
    }

    // Order Tracking Management
    function openOrderModal(orderId, currentStatus) {
        document.getElementById('orderStatusModal').style.display = 'block';
        document.getElementById('order_id_track').value = orderId;
        document.getElementById('order_display_id').innerText = '#' + orderId;
        document.getElementById('order_status_select').value = currentStatus;
        updateTrackerVisual(currentStatus);
    }

    function updateTrackerVisual(status) {
        const stepPlaced = document.getElementById('step-placed');
        const stepTransit = document.getElementById('step-transit');
        const stepCompleted = document.getElementById('step-completed');
        const trackLine = document.getElementById('track-line');

        // Reset
        [stepPlaced, stepTransit, stepCompleted].forEach(el => {
            el.className = 'validation-step';
            el.querySelector('.step-icon').style.background = '#cbd5e1';
            el.querySelector('.step-icon').style.boxShadow = 'none';
        });
        trackLine.style.background = 'linear-gradient(to right, #10b981, #f59e0b)';
        trackLine.style.width = '0%';

        if (status === 'Cancelled') {
            trackLine.style.width = '0%';
            trackLine.style.background = '#ef4444'; // Red
            return;
        }

        // Helper to set active/completed
        const setStep = (el, state) => {
            if(state === 'completed') {
                el.classList.add('step-completed');
                el.querySelector('.step-icon').style.background = '#10b981';
            } else if(state === 'active') {
                el.classList.add('step-active');
                el.querySelector('.step-icon').style.background = '#f59e0b';
                el.querySelector('.step-icon').style.boxShadow = '0 0 0 3px #fef3c7';
            }
        };

        if (status === 'Placed') {
            setStep(stepPlaced, 'active');
            trackLine.style.width = '10%';
        } else if (status === 'Shipped') {
            setStep(stepPlaced, 'completed');
            setStep(stepTransit, 'active');
            trackLine.style.width = '50%';
        } else if (status === 'Delivered') {
            setStep(stepPlaced, 'completed');
            setStep(stepTransit, 'completed');
            setStep(stepCompleted, 'completed');
            trackLine.style.width = '100%';
        }
    }

    function saveOrderStatus() {
        showCustomConfirm('Update order status?', () => {
            const orderId = document.getElementById('order_id_track').value;
            const status = document.getElementById('order_status_select').value;

            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('order_id', orderId);
            formData.append('status', status);

            fetch('admin_order_action.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    showCustomAlert(data.message, 'success', () => location.reload());
                } else {
                    showCustomAlert('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showCustomAlert('Request failed', 'error');
            });
        });
    }
    </script>
</body>

</html>


