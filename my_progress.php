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

        /* Modal Styles */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s ease; }
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-content { background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 400px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); transform: translateY(20px); transition: transform 0.3s ease; }
        .modal-overlay.active .modal-content { transform: translateY(0); }
        .input-group label { display: block; margin-bottom: 8px; color: var(--text-light); font-size: 0.9rem; }
        .input-field { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Inter', sans-serif; margin-bottom: 20px; outline: none; transition: border-color 0.3s; }
        .input-field:focus { border-color: var(--primary-color); }
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
                    <input type="number" id="weightInput" class="weight-input" value="0">
                    <span style="font-size: 1rem; color: #777;">kg</span>
                </div>
                <div class="stat-label">Current Weight</div>
            </div>

            <div class="stat-card card-red">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-fire"></i></div>
                </div>
                <div class="stat-value" id="totalCaloriesDisplay">0</div>
                <div class="stat-label">Calories Burned (Total)</div>
                <button class="log-btn" onclick="openLogModal()">Log Activity</button>
            </div>

            <div class="stat-card card-blue">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-running"></i></div>
                </div>
                <div class="stat-value" id="workoutCount">0</div>
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
            <div class="chart-card">
                <h3 style="margin-bottom: 20px;">Progress Tracking</h3>
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="progressChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Recent Logs</h3>
                    <button onclick="clearLogs()" style="background: none; border: none; color: #E63946; cursor: pointer; font-size: 0.8rem;">Clear All</button>
                </div>
                <ul id="recentLogsList" style="list-style: none; width: 100%; overflow-y: auto; max-height: 400px; padding-right: 5px;">
                    <!-- Logs will appear here -->
                    <p id="noLogsMsg" style="color: #999; text-align: center; margin-top: 40px;">No recent activities.</p>
                </ul>
            </div>
        </div>
    </div>

    <!-- Log Activity Modal -->
    <div id="logModal" class="modal-overlay">
        <div class="modal-content">
            <h3 style="margin-bottom: 20px; color: var(--primary-color);">Log Activity</h3>
            
            <div class="input-group">
                <label>Select Activity</label>
                <select id="logActivity" class="input-field">
                    <option value="" disabled selected>Choose an activity...</option>
                    <option value="run">Running (Moderate)</option>
                    <option value="walk">Walking</option>
                    <option value="cycle">Cycling</option>
                    <option value="swim">Swimming</option>
                    <option value="weights">Weight Lifting</option>
                    <option value="yoga">Yoga</option>
                    <option value="hiit">HIIT</option>
                </select>
            </div>

            <div class="input-group">
                <label>Duration (Minutes)</label>
                <input type="number" id="logDuration" class="input-field" placeholder="e.g. 30">
            </div>

            <p id="calEstimate" style="margin-bottom: 20px; font-size: 0.9rem; color: var(--text-light); text-align: center; height: 1.2em;"></p>

            <div style="display: flex; gap: 15px;">
                <button onclick="saveLog()" class="log-btn" style="margin-top: 0;">Save Activity</button>
                <button onclick="closeLogModal()" class="log-btn" style="margin-top: 0; background: #f1f3f5; color: var(--text-color);">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // User Identity
        const CURRENT_USER_ID = "<?php echo $_SESSION['user_id']; ?>";
        const S_KEY = (key) => `${key}_${CURRENT_USER_ID}`;

        // Chart Instance
        let myChart = null;

        // Use LocalStorage to persist data
        document.addEventListener('DOMContentLoaded', () => {
            loadStats();
            renderLogs();
            renderChart();

            // Save weight on change
            document.getElementById('weightInput').addEventListener('change', (e) => {
                localStorage.setItem(S_KEY('fitnova_weight'), e.target.value);
            });
        });

        function loadStats() {
            const savedWeight = localStorage.getItem(S_KEY('fitnova_weight'));
            if (savedWeight) document.getElementById('weightInput').value = savedWeight;
            
            // Recalculate totals from logs to ensure accuracy
            calculateTotalsFromLogs();
        }

        function calculateTotalsFromLogs() {
            const logs = JSON.parse(localStorage.getItem(S_KEY('fitnova_logs')) || '[]');
            
            const totalCalories = logs.reduce((sum, log) => sum + parseInt(log.calories || 0), 0);
            const totalWorkouts = logs.length;

            document.getElementById('totalCaloriesDisplay').innerText = totalCalories.toLocaleString();
            document.getElementById('workoutCount').innerText = totalWorkouts;

            // Sync legacy keys just in case other pages read them
            localStorage.setItem(S_KEY('fitnova_calories'), totalCalories);
            localStorage.setItem(S_KEY('fitnova_workouts'), totalWorkouts);
        }

        function openLogModal() {
            const modal = document.getElementById('logModal');
            modal.classList.add('active');
        }

        function closeLogModal() {
            const modal = document.getElementById('logModal');
            modal.classList.remove('active');
            // Clear inputs
            document.getElementById('logActivity').selectedIndex = 0;
            document.getElementById('logDuration').value = '';
            document.getElementById('calEstimate').innerText = '';
        }

        const METS = {
            'run': 8.0,
            'walk': 3.5,
            'cycle': 6.0,
            'swim': 6.0,
            'weights': 3.5,
            'yoga': 2.5,
            'hiit': 8.0
        };

        function saveLog() {
            const activity = document.getElementById('logActivity').value;
            const duration = parseInt(document.getElementById('logDuration').value) || 0;
            const weight = parseFloat(document.getElementById('weightInput').value) || 0; // Default 0kg

            if (!activity || duration <= 0) {
                alert('Please select an activity and enter valid duration');
                return;
            }

            // Calculate Calories: (MET * 3.5 * weight) / 200 * duration
            // Simplified Formula: Calories = MET * weight * (duration/60)
            const met = METS[activity] || 1;
            const calories = Math.round(met * weight * (duration / 60));

            // Create Log Object
            const newLog = {
                id: Date.now(),
                activity: activity,
                calories: calories,
                duration: duration,
                date: new Date().toLocaleDateString(),
                timestamp: Date.now()
            };

            // Save Log
            const logs = JSON.parse(localStorage.getItem(S_KEY('fitnova_logs')) || '[]');
            logs.unshift(newLog); 
            localStorage.setItem(S_KEY('fitnova_logs'), JSON.stringify(logs));

            // Save Time
            let savedTime = parseFloat(localStorage.getItem(S_KEY('fitnova_time')) || 0);
            savedTime += (duration / 60);
            localStorage.setItem(S_KEY('fitnova_time'), savedTime);
            calculateTotalsFromLogs();

            // Refresh UI
            renderLogs();
            renderChart();
            closeLogModal();
            
            alert(`Activity Logged! You burned approx ${calories} kcal 🔥`);
        }

        function renderLogs() {
            const list = document.getElementById('recentLogsList');
            const noLogsMsg = document.getElementById('noLogsMsg');
            const logs = JSON.parse(localStorage.getItem(S_KEY('fitnova_logs')) || '[]');

            // Clear list
            list.innerHTML = '';

            if (logs.length === 0) {
                list.appendChild(noLogsMsg);
                noLogsMsg.style.display = 'block';
                return;
            }

            // Hide empty message
            if(noLogsMsg) noLogsMsg.style.display = 'none';

            logs.forEach(log => {
                const li = document.createElement('li');
                li.style.cssText = 'display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #eee;';
                
                let iconClass = 'fa-dumbbell';
                if (log.activity === 'run') iconClass = 'fa-running';
                if (log.activity === 'walk') iconClass = 'fa-walking';
                if (log.activity === 'cycle') iconClass = 'fa-bicycle';
                if (log.activity === 'swim') iconClass = 'fa-swimmer';

                // Format activity Name
                const activityName = log.activity.charAt(0).toUpperCase() + log.activity.slice(1);

                li.innerHTML = `
                    <div style="width: 40px; height: 40px; background: rgba(15, 44, 89, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); margin-right: 15px;">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div style="flex: 1;">
                        <h4 style="font-size: 0.95rem; margin-bottom: 3px;">${activityName}</h4>
                        <span style="font-size: 0.8rem; color: #999;">${log.date} • ${log.duration} min</span>
                    </div>
                    <div style="font-weight: 700; color: var(--accent-color);">
                        ${log.calories} kcal
                    </div>
                `;
                list.appendChild(li);
            });
        }

        function renderChart() {
            const ctx = document.getElementById('progressChart').getContext('2d');
            const logs = JSON.parse(localStorage.getItem(S_KEY('fitnova_logs')) || '[]');
            
            const recentLogs = logs.slice(0, 10).reverse(); 
            
            const labels = recentLogs.map(log => log.activity.charAt(0).toUpperCase() + log.activity.slice(1));
            const actualData = recentLogs.map(log => parseInt(log.calories));
            
            // Fixed Target for visualization (e.g., 600 kcal per session goal)
            const targetVal = 600;
            const remainingData = actualData.map(val => Math.max(0, targetVal - val));

            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels.length ? labels : ['No Data'],
                    datasets: [
                        {
                            label: 'Calories Burned',
                            data: actualData.length ? actualData : [0],
                            backgroundColor: '#E65100', // Dark Orange
                            maxBarThickness: 30,
                            stack: 'Stack 0'
                        },
                        {
                            label: 'Target',
                            data: actualData.length ? remainingData : [targetVal],
                            backgroundColor: '#FFE0B2', // Light Orange
                            maxBarThickness: 30,
                            stack: 'Stack 0'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                color: '#333'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Progress Chart',
                            color: '#E65100',
                            font: {
                                size: 18,
                                family: 'Outfit',
                                weight: 'bold'
                            },
                            padding: { bottom: 20 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if(context.dataset.label === 'Target') return 'Goal: ' + targetVal + ' kcal';
                                    return 'Burned: ' + context.parsed.y + ' kcal';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            stacked: true,
                            grid: { display: false },
                            ticks: { font: { family: 'Inter' } }
                        },
                        x: {
                            stacked: true,
                            grid: { display: false },
                            ticks: { 
                                font: { family: 'Inter' },
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }

        function clearLogs() {
            if(confirm('Clear all activity history?')) {
                localStorage.removeItem(S_KEY('fitnova_logs'));
                localStorage.setItem(S_KEY('fitnova_calories'), '0');
                localStorage.setItem(S_KEY('fitnova_workouts'), '0');
                localStorage.setItem(S_KEY('fitnova_time'), '0');
                
                // Reset UI
                document.getElementById('totalCaloriesDisplay').innerText = '0';
                document.getElementById('workoutCount').innerText = '0';
                
                renderLogs();
                renderChart();
            }
        }

        // Close on outside click
        document.getElementById('logModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('logModal')) {
                closeLogModal();
            }
        });
    </script>


    <?php include 'footer.php'; ?>
</body>

</html>
