<?php
session_start();
// gym.php - Gym Equipment & Trainer Availability
require 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Status - FitNova</title>
    <meta name="description" content="Check real-time equipment availability and trainer status at FitNova physical gyms. Plan your workout efficiently with our live tracking system.">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #4FACFE;
            --success-color: #00DBDE;
            --warning-color: #FAD02E;
            --danger-color: #FF4E50;
            --text-dark: #1A1A1A;
            --text-light: #555;
            --bg-light: #F8F9FA;
            --white: #FFFFFF;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --font-main: 'Outfit', sans-serif;
            --glass: rgba(255, 255, 255, 0.8);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-main); }
        body { background-color: var(--bg-light); color: var(--text-dark); overflow-x: hidden; line-height: 1.6; }
        a { text-decoration: none; color: inherit; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* Navbar */
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 15px 0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-container { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 28px; font-weight: 900; color: var(--primary-color); letter-spacing: -0.5px; }
        .nav-links { display: flex; align-items: center; gap: 40px; }
        .nav-link { font-weight: 500; font-size: 0.95rem; color: var(--text-dark); transition: var(--transition); position: relative; }
        .nav-link::after { content: ''; position: absolute; bottom: -5px; left: 0; width: 0; height: 2px; background: var(--accent-color); transition: var(--transition); }
        .nav-link:hover::after { width: 100%; }
        .nav-link.active::after { width: 100%; }
        .btn-signup { background: var(--primary-color); color: white; padding: 10px 25px; border-radius: 50px; font-weight: 600; font-size: 0.9rem; transition: var(--transition); }
        .btn-signup:hover { background: #0a1f40; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(15, 44, 89, 0.3); }

        /* Hero Section */
        .gym-hero {
            background: linear-gradient(rgba(15, 44, 89, 0.8), rgba(15, 44, 89, 0.8)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=2000');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: white;
            text-align: center;
            margin-bottom: 60px;
        }
        .gym-hero h1 { font-size: 3.5rem; font-weight: 900; margin-bottom: 15px; }
        .gym-hero p { font-size: 1.2rem; opacity: 0.9; max-width: 700px; margin: 0 auto; }

        /* Section Styling */
        .section-header { margin-bottom: 40px; display: flex; align-items: center; justify-content: space-between; }
        .section-header h2 { font-size: 2rem; font-weight: 800; color: var(--primary-color); }
        .status-legend { display: flex; gap: 15px; }
        .legend-item { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; }
        .dot { width: 10px; height: 10px; border-radius: 50%; }
        .dot.available { background: var(--success-color); box-shadow: 0 0 10px var(--success-color); }
        .dot.busy { background: var(--warning-color); box-shadow: 0 0 10px var(--warning-color); }
        .dot.unavailable { background: var(--danger-color); box-shadow: 0 0 10px var(--danger-color); }

        /* Grid Layouts */
        .gym-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-bottom: 60px; }

        /* Equipment Card */
        .equip-card {
            background: var(--white);
            border-radius: 16px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }
        .equip-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .equip-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, rgba(79, 172, 254, 0.2), rgba(79, 172, 254, 0.1));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            color: var(--accent-color);
            font-size: 1.8rem;
            box-shadow: 0 4px 12px rgba(79, 172, 254, 0.15);
        }
        .equip-card h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; }
        .equip-info { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.8rem; color: var(--text-light); }
        .progress-container { height: 6px; background: #eee; border-radius: 10px; overflow: hidden; margin-bottom: 8px; }
        .progress-bar { height: 100%; border-radius: 10px; transition: width 1s ease-in-out; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .status-badge.available { background: rgba(0, 219, 222, 0.1); color: var(--success-color); }
        .status-badge.busy { background: rgba(250, 208, 46, 0.1); color: #b7950b; }
        .status-badge.unavailable { background: rgba(255, 78, 80, 0.1); color: var(--danger-color); }

        /* Trainer Card */
        .trainer-card {
            background: var(--white);
            border-radius: 16px;
            padding: 0;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-align: center;
        }
        .trainer-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .trainer-img-wrapper { position: relative; height: 160px; }
        .trainer-img { width: 100%; height: 100%; object-fit: cover; }
        .trainer-status-indicator {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid white;
        }
        .trainer-content { padding: 15px; }
        .trainer-content h3 { font-size: 1.1rem; margin-bottom: 4px; }
        .trainer-specialty { color: var(--accent-color); font-weight: 600; font-size: 0.8rem; margin-bottom: 10px; display: block; }
        .trainer-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; border-top: 1px solid #eee; padding-top: 10px; }
        .stat-item span { display: block; font-size: 0.7rem; color: var(--text-light); }
        .stat-item strong { font-size: 0.9rem; color: var(--primary-color); }

        /* Live Animation */
        .live-pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: var(--danger-color);
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 78, 80, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(255, 78, 80, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 78, 80, 0); }
        }

        /* Footer */
        .footer { background: #111; color: white; padding: 60px 0 30px; margin-top: 100px; }
        .footer-bottom { text-align: center; color: #666; font-size: 0.9rem; padding-top: 30px; border-top: 1px solid #333; }

        @media (max-width: 768px) {
            .gym-hero h1 { font-size: 2.5rem; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <?php
    // Fetch Gym Schedule
    require 'db_connect.php';
    $gymSettings = ['gym_open_time' => '05:00 AM', 'gym_close_time' => '10:00 PM', 'gym_status' => 'open'];
    $sqlGs = "SELECT * FROM gym_settings";
    $resultGs = $conn->query($sqlGs);
    if ($resultGs && $resultGs->num_rows > 0) {
        while($row = $resultGs->fetch_assoc()) {
            $gymSettings[$row['setting_key']] = $row['setting_value'];
        }
    }
    $isClosed = ($gymSettings['gym_status'] === 'closed');
    $timeText = $isClosed 
        ? "Gym is Closed Today" 
        : "Open Today: " . $gymSettings['gym_open_time'] . " - " . $gymSettings['gym_close_time'];
    $iconColor = $isClosed ? '#ef4444' : '#4ade80';
    $iconClass = $isClosed ? 'fa-times-circle' : 'fa-clock';
    ?>

    <!-- Hero -->
    <header class="gym-hero">
        <div class="container">
            <h1>Physical Gym Status</h1>
            <p>Check real-time equipment availability and trainer schedules before you head out. Stay efficient with your workout time.</p>
            <div style="margin-top: 25px; display: inline-flex; align-items: center; background: rgba(255,255,255,0.15); padding: 12px 25px; border-radius: 50px; backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.2);">
                <i id="gym-status-icon" class="fas <?php echo $iconClass; ?>" style="color: <?php echo $iconColor; ?>; margin-right: 12px; font-size: 1.2rem;"></i>
                <span id="gym-status-text" style="font-weight: 600; font-size: 1.1rem; letter-spacing: 0.5px;"><?php echo htmlspecialchars($timeText); ?></span>
            </div>
        </div>
    </header>

    <main class="container">
        
        <!-- Equipment Status -->
        <section>
            <div class="section-header">
                <h2><span class="live-pulse"></span>Equipment Availability</h2>
                <div class="status-legend">
                    <div class="legend-item"><div class="dot available"></div> Available</div>
                    <div class="legend-item"><div class="dot busy"></div> In Use</div>
                    <div class="legend-item"><div class="dot unavailable"></div> Maintenance</div>
                </div>
            </div>

            <div class="gym-grid" id="equipment-grid">
                <?php
                // Fetch Equipment from DB
                // DB Connection is already established in header or top
                $sqlEq = "SELECT * FROM gym_equipment";
                $resEq = $conn->query($sqlEq);
                
                if ($resEq && $resEq->num_rows > 0) {
                    while($row = $resEq->fetch_assoc()) {
                        $percent = ($row['available_units'] / $row['total_units']) * 100;
                        // Logic duplicated for initial render
                        if ($percent > 60) {
                            $barColor = 'var(--success-color)';
                            $badgeClass = 'available';
                            $badgeText = 'High Availability';
                        } elseif ($percent > 20) {
                            $barColor = 'var(--warning-color)';
                            $badgeClass = 'busy';
                            $badgeText = 'Busy Session';
                        } else {
                            $barColor = 'var(--danger-color)';
                            $badgeClass = 'unavailable';
                            $badgeText = 'Full Capacity';
                        }
                        
                        if ($row['status'] === 'unavailable') {
                            $badgeClass = 'unavailable';
                            $badgeText = 'Maintenance / Unavailable';
                            $barColor = '#ccc';
                        }
                        
                        // Fallback icon mapping if icon is empty
                        $iconClass = $row['icon'];
                        if (empty($iconClass)) {
                            $iconMap = [
                                'Treadmills' => 'fas fa-running',
                                'Free Weights' => 'fas fa-dumbbell',
                                'Bench Press' => 'fas fa-weight-hanging',
                                'Squat Racks' => 'fas fa-child',
                                'Rowing Machines' => 'fas fa-water',
                                'Lat Pulldown' => 'fas fa-level-down-alt'
                            ];
                            $iconClass = isset($iconMap[$row['name']]) ? $iconMap[$row['name']] : 'fas fa-dumbbell';
                        }
                ?>
                <div class="equip-card" data-id="<?php echo $row['id']; ?>">
                    <div class="equip-icon"><i class="<?php echo $iconClass; ?>"></i></div>
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <div class="equip-info">
                        <span>Total: <?php echo $row['total_units']; ?></span>
                        <strong class="avail-text"><?php echo $row['available_units']; ?> Available</strong>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?php echo $percent; ?>%; background: <?php echo $barColor; ?>;"></div>
                    </div>
                    <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span>
                </div>
                <?php
                    }
                } else {
                    echo "<p>No equipment data available.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Trainer Status -->
        <section>
            <div class="section-header">
                <h2>Our Trainers On-Site</h2>
                <p style="color: var(--text-light);">Current shift availability</p>
            </div>

            <div class="gym-grid">
                <?php
                // Fetch All Active Offline Trainers
                $allOfflineTrainers = [];
                $sqlTrainers = "SELECT u.user_id, u.first_name, u.last_name, u.trainer_specialization, u.trainer_experience,
                                (SELECT status FROM trainer_attendance ta 
                                 WHERE ta.trainer_id = u.user_id AND DATE(ta.check_in_time) = CURDATE() 
                                 ORDER BY ta.check_in_time DESC LIMIT 1) as attendance_status
                                FROM users u
                                WHERE u.role = 'trainer' AND u.trainer_type = 'offline' AND u.account_status = 'active'
                                ORDER BY u.first_name ASC";
                
                $resTrainers = $conn->query($sqlTrainers);
                if ($resTrainers && $resTrainers->num_rows > 0) {
                    while($row = $resTrainers->fetch_assoc()) {
                        $allOfflineTrainers[] = $row;
                    }
                }

                if (count($allOfflineTrainers) > 0) {
                    foreach ($allOfflineTrainers as $trainer) {
                        $name = htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']);
                        $spec = htmlspecialchars($trainer['trainer_specialization'] ?: 'Trainer');
                        $exp = htmlspecialchars($trainer['trainer_experience'] ?: 'N/A');
                        
                        $isAvailable = ($trainer['attendance_status'] === 'checked_in');
                        $statusColor = $isAvailable ? 'var(--success-color)' : '#cbd5e1';
                        $statusText = $isAvailable ? 'Available' : 'Off Duty';
                        $statusLabelColor = $isAvailable ? 'var(--success-color)' : '#94a3b8';
                ?>
                <a href="trainer_profile.php?id=<?php echo $trainer['user_id']; ?>" class="trainer-card" style="text-decoration: none; color: inherit;">
                    <div class="trainer-img-wrapper">
                        <img src="uploads/universal_trainer_profile.png" alt="<?php echo $name; ?>" class="trainer-img">
                        <div class="trainer-status-indicator" style="background: <?php echo $statusColor; ?>;" title="<?php echo $statusText; ?>"></div>
                    </div>
                    <div class="trainer-content">
                        <h3><?php echo $name; ?></h3>
                        <span class="trainer-specialty"><?php echo $spec; ?></span>
                        <div class="trainer-stats">
                            <div class="stat-item"><span>Status</span><strong style="color: <?php echo $statusLabelColor; ?>;"><?php echo $statusText; ?></strong></div>
                            <div class="stat-item"><span>Exp.</span><strong><?php echo $exp; ?> Years</strong></div>
                        </div>
                    </div>
                </a>
                <?php 
                    }
                } else {
                    echo '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-light);">No offline trainers registered yet.</div>';
                }
                ?>
            </div>
        </section>

    </main>

    <?php include 'footer.php'; ?>
    
    <script>
        // Real-time Updates
        function updateGymStatus() {
            fetch('api_gym_status.php')
                .then(response => response.json())
                .then(data => {
                    // Update Gym Status Header
                    const settings = data.settings;
                    const statusText = document.getElementById('gym-status-text');
                    const statusIcon = document.getElementById('gym-status-icon');
                    
                    const openTime = settings.gym_open_time || '06:00 AM';
                    const closeTime = settings.gym_close_time || '10:00 PM';
                    const isOpen = (settings.gym_status || 'open') === 'open';
                    
                    if (isOpen) {
                        statusText.textContent = `Open Today: ${openTime} - ${closeTime}`;
                        statusIcon.className = 'fas fa-clock';
                        statusIcon.style.color = '#4ade80';
                    } else {
                        statusText.textContent = 'Gym is Closed Today';
                        statusIcon.className = 'fas fa-times-circle';
                        statusIcon.style.color = '#ef4444';
                    }
                    
                    // Update Equipment
                    data.equipment.forEach(eq => {
                        const card = document.querySelector(`.equip-card[data-id="${eq.id}"]`);
                        if (card) {
                            // Update Available Text
                            const availText = card.querySelector('.avail-text');
                            if (availText) availText.textContent = `${eq.available_units} Available`;
                            
                            // Update Progress Bar
                            const progressBar = card.querySelector('.progress-bar');
                            const percent = (eq.available_units / eq.total_units) * 100;
                            let barColor = 'var(--success-color)';
                            let badgeClass = 'available';
                            let badgeText = 'High Availability';
                            
                            if (percent <= 60 && percent > 20) {
                                barColor = 'var(--warning-color)';
                                badgeClass = 'busy';
                                badgeText = 'Busy Session';
                            } else if (percent <= 20) {
                                barColor = 'var(--danger-color)';
                                badgeClass = 'unavailable';
                                badgeText = 'Full Capacity';
                            }
                            
                            if (eq.status === 'unavailable') {
                                barColor = '#ccc';
                                badgeClass = 'unavailable';
                                badgeText = 'Maintenance / Unavailable';
                            }
                            
                            if (progressBar) {
                                progressBar.style.width = percent + '%';
                                progressBar.style.background = barColor;
                            }
                            
                            // Update Badge
                            const badge = card.querySelector('.status-badge');
                            if (badge) {
                                badge.className = `status-badge ${badgeClass}`;
                                badge.textContent = badgeText;
                            }
                        }
                    });
                })
                .catch(err => console.error('Error fetching gym status:', err));
        }

        // Poll every 5 seconds
        setInterval(updateGymStatus, 5000);
    </script>
</body>
</html>
