<?php session_start(); include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress - FitNova</title>
    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #E63946;
            --secondary-color: #3498DB;
            --bg-color: #F8F9FA;
            --text-color: #333333;
            --text-light: #6C757D;
            --white: #FFFFFF;
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
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
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #0F2C59 0%, #2a5298 100%);
            color: white;
            padding: 80px 20px 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 30px 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 20px rgba(15, 44, 89, 0.3);
        }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 30px;
            position: relative;
            z-index: 1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
            transform: translateY(-40px);
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .stat-value {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: baseline;
            gap: 5px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .card-purple .stat-icon { background: rgba(155, 89, 182, 0.1); color: #9B59B6; }
        .card-red .stat-icon { background: rgba(230, 57, 70, 0.1); color: #E63946; }
        .card-blue .stat-icon { background: rgba(52, 152, 219, 0.1); color: #3498DB; }
        .card-green .stat-icon { background: rgba(46, 204, 113, 0.1); color: #2ECC71; }

        .weight-input {
            border: none; border-bottom: 2px solid #ddd; font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 700; color: var(--primary-color); width: 100px; background: transparent; outline: none;
        }

        .log-btn {
            width: 100%; padding: 12px; background: var(--primary-color); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: var(--transition); margin-top: 15px;
        }

        .dashboard-grid {
            display: grid; grid-template-columns: 2fr 1fr; gap: 30px;
        }

        .chart-card {
            background: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--shadow); display: flex; flex-direction: column; min-height: 400px;
        }
    </style>
</head>

<body>

    <div class="hero">
        <h1 class="hero-title">My Progress</h1>
        <p class="hero-subtitle">Track your stats, log your activities, and see your results.</p>
    </div>

    <div class="container">
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card card-purple">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-weight"></i></div>
                </div>
                <div class="stat-value">
                    <input type="number" id="weightInput" class="weight-input" value="72">
                    <span style="font-size: 1rem; color: #777;">kg</span>
                </div>
                <div class="stat-label">Current Weight</div>
            </div>

            <div class="stat-card card-red">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-fire"></i></div>
                </div>
                <div class="stat-value" id="totalCaloriesDisplay">1,250</div>
                <div class="stat-label">Calories Burned (Total)</div>
                <button class="log-btn">Log Activity</button>
            </div>

            <div class="stat-card card-blue">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-running"></i></div>
                </div>
                <div class="stat-value" id="workoutCount">12</div>
                <div class="stat-label">Workouts Logged</div>
            </div>

            <div class="stat-card card-green">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                </div>
                <div class="stat-value">1 Day</div>
                <div class="stat-label">Current Streak</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="chart-card" style="justify-content: center; align-items: center;">
                <i class="fas fa-chart-line" style="font-size: 4rem; color: #eee; margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 10px;">Progress Tracking</h3>
                <p style="color: #999; text-align: center;">Your detailed progress metrics will appear here as you log more activities.</p>
            </div>
            <div class="chart-card">
                <h3 style="margin-bottom: 20px;">Recent Logs</h3>
                <p style="color: #999; text-align: center; margin-top: 40px;">No recent activities.</p>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>
