<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$trainerId = $_SESSION['user_id'];
$trainerName = $_SESSION['user_name'];
$trainerInitials = strtoupper(substr($trainerName, 0, 1) . substr($trainerName, strrpos($trainerName, ' ') + 1, 1));

// Summary Stats
$stats = [
    'total_clients' => 0,
    'total_sessions' => 0,
    'avg_rating' => 4.9,
    'revenue' => 2450
];

// Get real client count
$res = $conn->query("SELECT COUNT(*) as count FROM users WHERE assigned_trainer_id = $trainerId");
if($row = $res->fetch_assoc()) $stats['total_clients'] = $row['count'];

// Get real session count
$res = $conn->query("SELECT COUNT(*) as count FROM trainer_schedules WHERE trainer_id = $trainerId");
if($row = $res->fetch_assoc()) $stats['total_sessions'] = $row['count'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Analytics - FitNova Trainer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .sidebar { width: 260px; background-color: var(--sidebar-bg); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; position: fixed; height: 100vh; }
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
        .header-section { margin-bottom: 40px; }
        .header-section h2 { font-family: 'Outfit', sans-serif; font-size: 32px; color: #1e293b; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .stat-icon { width: 45px; height: 45px; border-radius: 12px; background: var(--primary-light); color: var(--primary-color); display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 15px; }
        .stat-value { font-size: 28px; font-weight: 800; color: #1e293b; }
        .stat-label { font-size: 13px; color: var(--text-light); font-weight: 500; margin-top: 5px; }

        .charts-container { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 40px; }
        .chart-card { background: white; padding: 30px; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .chart-header h3 { font-family: 'Outfit', sans-serif; font-size: 18px; color: #1e293b; }

        .client-progress-table { width: 100%; border-collapse: collapse; }
        .client-progress-table th { text-align: left; padding: 15px; background: #f8fafc; color: var(--text-light); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        .client-progress-table td { padding: 20px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .progress-bar-wrap { width: 100px; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-top: 5px; }
        .progress-bar { height: 100%; background: var(--primary-color); border-radius: 3px; }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .charts-container { grid-template-columns: 1fr; }
        }
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
            <a href="trainer_diets.php" class="menu-item">
                <i class="fas fa-utensils"></i> Diet Plans
            </a>
            <a href="trainer_performance.php" class="menu-item active">
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
            <h2>Performance Metrics</h2>
            <p>Track your coaching impact and client milestones</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?php echo $stats['total_clients']; ?></div>
                <div class="stat-label">Active Clients</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #ecfdf5; color: #10b981;"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-value"><?php echo $stats['total_sessions']; ?></div>
                <div class="stat-label">Sessions Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fffbeb; color: #f59e0b;"><i class="fas fa-star"></i></div>
                <div class="stat-value"><?php echo $stats['avg_rating']; ?></div>
                <div class="stat-label">Trainer Rating</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fdf2f8; color: #ec4899;"><i class="fas fa-wallet"></i></div>
                <div class="stat-value">$<?php echo number_format($stats['revenue']); ?></div>
                <div class="stat-label">Monthly Impact</div>
            </div>
        </div>

        <div class="charts-container">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Engagement Overview</h3>
                    <select style="padding: 5px 10px; border-radius: 5px; border: 1px solid var(--border-color);">
                        <option>Last 7 Days</option>
                        <option>Last 30 Days</option>
                    </select>
                </div>
                <canvas id="engagementChart" height="200"></canvas>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Client Breakdown</h3>
                </div>
                <canvas id="clientBreakdown" height="250"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Goal Success Tracking</h3>
            </div>
            <table class="client-progress-table">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Target Goal</th>
                        <th>Progress Status</th>
                        <th>Success Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>John Smith</strong></td>
                        <td>Hypertrophy phase</td>
                        <td style="color: #10b981; font-weight: 600;">On Track</td>
                        <td>
                            <div class="calorie-label" style="display:flex; justify-content: space-between; font-size: 11px;"><span>75%</span></div>
                            <div class="progress-bar-wrap"><div class="progress-bar" style="width: 75%;"></div></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Emma Watson</strong></td>
                        <td>Fat Loss Focus</td>
                        <td style="color: #4f46e5; font-weight: 600;">Excelling</td>
                        <td>
                            <div class="calorie-label" style="display:flex; justify-content: space-between; font-size: 11px;"><span>92%</span></div>
                            <div class="progress-bar-wrap"><div class="progress-bar" style="width: 92%; background: #10b981;"></div></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Robert Brown</strong></td>
                        <td>Endurance Build</td>
                        <td style="color: #f59e0b; font-weight: 600;">Maintenance</td>
                        <td>
                            <div class="calorie-label" style="display:flex; justify-content: space-between; font-size: 11px;"><span>60%</span></div>
                            <div class="progress-bar-wrap"><div class="progress-bar" style="width: 60%; background: #f59e0b;"></div></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Engagement Line Chart
        const ctx = document.getElementById('engagementChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Client Interactions',
                    data: [12, 19, 15, 25, 22, 30, 20],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Client Breakdown Pie Chart
        const ctx2 = document.getElementById('clientBreakdown').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Pro Active', 'Free Trial', 'New Leads'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: ['#4f46e5', '#10b981', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>

</html>
