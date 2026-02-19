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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1F2937;
            --primary-light: #F3F4F6;
            --accent-teal: #00DBDE;
            --accent-pink: #FC00FF;
            --status-green: #10B981;
            --status-yellow: #F59E0B;
            --status-red: #EF4444;
            --text-main: #111827;
            --text-muted: #6B7280;
            --card-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            --card-hover: 0 20px 40px -5px rgba(0, 0, 0, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { background-color: #F9FAFB; color: var(--text-main); }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }

        /* Navbar & Hero */
        .gym-header {
            background: linear-gradient(rgba(17, 24, 39, 0.7), rgba(17, 24, 39, 0.7)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=2000');
            background-size: cover;
            background-position: center;
            padding: 120px 0 100px;
            color: white;
            text-align: center;
            margin-bottom: 60px;
            position: relative;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -1px;
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 500;
            margin-bottom: 25px;
        }

        .owner-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }
        .owner-item { display: flex; align-items: center; gap: 8px; }
        .owner-item i { color: rgba(255, 255, 255, 0.6); }

        /* Section Styling */
        .section-wrapper { margin-bottom: 80px; }
        
        .section-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-end; 
            margin-bottom: 30px; 
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .section-title-block {
            border-left: 6px solid var(--accent-teal);
            padding-left: 20px;
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-dark);
            line-height: 1.2;
        }
        
        .section-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            margin-top: 5px;
            font-weight: 400;
        }

        .legend-wrapper {
            background: white;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .legend-item { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); }
        .legend-dot { width: 8px; height: 8px; border-radius: 50%; }

        /* Grid System */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 30px;
        }

        /* --- EQUIPMENT CARD --- */
        .equip-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid #F3F4F6;
        }
        
        .equip-card:hover { transform: translateY(-5px); box-shadow: var(--card-hover); }

        .ec-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .ec-icon {
            width: 50px; height: 50px;
            background: #FEF2F2; /* Light Red bg for icon placeholder */
            color: #EF4444;
            border-radius: 12px;
            display: flex; justify-content: center; align-items: center;
            font-size: 1.2rem;
        }
        /* Dynamic icon colors based on type could be added here */
        .ec-icon.blue { background: #EFF6FF; color: #3B82F6; }
        .ec-icon.green { background: #ECFDF5; color: #10B981; }

        .ec-badge {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }
        .ec-badge.avail { background: #ECFDF5; color: #10B981; }
        .ec-badge.busy { background: #FFFBEB; color: #F59E0B; }
        .ec-badge.full { background: #FEF2F2; color: #EF4444; }

        .ec-title { font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-bottom: 15px; }

        .ec-stats {
            display: flex; justify-content: space-between;
            font-size: 0.9rem; color: var(--text-muted);
            margin-bottom: 15px; font-weight: 500;
        }
        .ec-val { font-weight: 700; color: var(--text-main); }

        .ec-progress-bg { height: 6px; background: #E5E7EB; border-radius: 10px; overflow: hidden; }
        .ec-progress-bar { height: 100%; border-radius: 10px; transition: width 1s ease; }

        /* --- TRAINER CARD --- */
        .trainer-card {
            background: white;
            border-radius: 24px;
            padding: 30px 20px 20px;
            box-shadow: var(--card-shadow);
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
            border: 1px solid #F3F4F6;
        }
        .trainer-card:hover { transform: translateY(-5px); box-shadow: var(--card-hover); }

        .tc-status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            background: #F3F4F6;
            color: #9CA3AF;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tc-status-badge.active { background: #ECFDF5; color: #10B981; }

        .tc-img-wrapper {
            position: relative;
            width: 100px; height: 100px;
            margin: 0 auto 20px;
        }
        .tc-img {
            width: 100%; height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        /* Ring for active status */
        .tc-img-ring {
            position: absolute; top: -4px; left: -4px; right: -4px; bottom: -4px;
            border-radius: 50%;
            border: 2px solid transparent;
        }
        .tc-img-ring.active { border-color: #10B981; }

        .tc-name { font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 5px; }
        .tc-role { font-size: 0.9rem; font-weight: 600; color: #EF4444; margin-bottom: 25px; /* Red/Pink accent for role */ }

        .tc-footer {
            border-top: 1px solid #F3F4F6;
            padding-top: 15px;
            display: flex;
            justify-content: space-between;
            padding-left: 10px; padding-right: 10px;
        }
        .tc-stat { text-align: center; }
        .tc-label { display: block; font-size: 0.65rem; color: var(--text-muted); font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 3px; }
        .tc-value { font-size: 0.95rem; font-weight: 700; color: var(--text-main); }
        .tc-value.active { color: #10B981; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .section-title { font-size: 1.5rem; }
            .card-grid { grid-template-columns: 1fr; }
            .gym-header { padding: 80px 0 60px; }
            .hero-title { font-size: 2.5rem; }
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
    <header class="gym-header">
        <div class="container">
            <h1 class="hero-title">Physical Gym Status</h1>
            <p class="hero-subtitle">Check real-time equipment availability and trainer schedules before you head out. Stay efficient with your workout time.</p>
            
            <div class="status-pill">
                <i id="gym-status-icon" class="fas <?php echo $iconClass; ?>" style="color: <?php echo $iconColor; ?>; margin-right: 12px; font-size: 1.1rem;"></i>
                <span id="gym-status-text"><?php echo htmlspecialchars($timeText); ?></span>
            </div>

            <div class="owner-info">
                <div class="owner-item">
                    <i class="fas fa-user-shield"></i> <span>Owner: <strong>Asha</strong></span>
                </div>
                <div class="owner-item">
                    <i class="fas fa-phone-alt"></i> <span>Contact: <strong>9495868854</strong></span>
                </div>
            </div>
        </div>
    </header>

<main class="container">

    <!-- Equipment Section -->
    <section class="section-wrapper">
        <div class="section-header">
            <div class="section-title-block">
                <h2 class="section-title">Equipment Availability</h2>
                <span class="section-subtitle">Live monitoring of floor equipment utilization</span>
            </div>
            <div class="legend-wrapper">
                <div class="legend-item"><div class="legend-dot" style="background:var(--accent-teal);"></div> Available</div>
                <div class="legend-item"><div class="legend-dot" style="background:var(--status-yellow);"></div> In Use</div>
                <div class="legend-item"><div class="legend-dot" style="background:var(--status-red);"></div> Maintenance</div>
            </div>
        </div>

        <div class="card-grid" id="equipment-grid">
             <?php
                // Fetch Equipment from DB
                $sqlEq = "SELECT * FROM gym_equipment";
                $resEq = $conn->query($sqlEq);
                
                if ($resEq && $resEq->num_rows > 0) {
                    while($row = $resEq->fetch_assoc()) {
                        $total = $row['total_units'];
                        $avail = $row['available_units'];
                        $percent = ($avail / $total) * 100;
                        
                        // Icon mapping
                        $iconClass = "fas fa-dumbbell"; // default
                        $iconBg = "#EFF6FF"; $iconColor = "#3B82F6"; // default blue
                        $nameLower = strtolower($row['name']);
                        
                        if(strpos($nameLower, 'treadmill') !== false) { 
                            $iconClass = "fas fa-running"; 
                            $iconBg = "#FEF2F2"; $iconColor = "#EF4444"; // Red
                        }
                        elseif(strpos($nameLower, 'weight') !== false) { 
                            $iconClass = "fas fa-dumbbell"; 
                            $iconBg = "#FFF7ED"; $iconColor = "#F97316"; // Orange
                        }
                        elseif(strpos($nameLower, 'bench') !== false) { 
                            $iconClass = "fas fa-couch"; /* approximate */ 
                            $iconBg = "#FFF1F2"; $iconColor = "#E11D48"; // Pink
                        }
                        elseif(strpos($nameLower, 'rack') !== false) { 
                            $iconClass = "fas fa-child"; 
                            $iconBg = "#F0FDF4"; $iconColor = "#16A34A"; // Green
                        }

                        // Status Logic
                        if ($row['status'] === 'unavailable') {
                            $badgeText = "MAINTENANCE";
                            $badgeClass = "full";
                            $barColor = "#EF4444";
                        } elseif ($percent > 60) {
                            $badgeText = "HIGH AVAILABILITY";
                            $badgeClass = "avail";
                            $barColor = "#10B981"; // Green
                        } elseif ($percent > 20) {
                            $badgeText = "BUSY SESSION";
                            $badgeClass = "busy";
                            $barColor = "#F59E0B"; // Yellow
                        } else {
                            $badgeText = "FULL CAPACITY";
                            $badgeClass = "full";
                            $barColor = "#EF4444"; // Red
                        }
                ?>
                <div class="equip-card">
                    <div class="ec-header">
                        <div class="ec-icon" style="background: <?php echo $iconBg; ?>; color: <?php echo $iconColor; ?>;">
                            <i class="<?php echo $iconClass; ?>"></i>
                        </div>
                        <span class="ec-badge <?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span>
                    </div>
                    <h3 class="ec-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                    <div class="ec-stats">
                        <span>Total: <?php echo $total; ?></span>
                        <span class="ec-val"><?php echo $avail; ?> Available</span>
                    </div>
                    <div class="ec-progress-bg">
                        <div class="ec-progress-bar" style="width: <?php echo $percent; ?>%; background: <?php echo $barColor; ?>;"></div>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo "<p>No equipment data.</p>";
                }
                ?>
        </div>
    </section>

    <!-- Trainers Section -->
    <section class="section-wrapper">
        <div class="section-header">
            <div class="section-title-block">
                <h2 class="section-title">Our Trainers On-Site</h2>
                <span class="section-subtitle">Professional guidance currently available on the floor</span>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #9CA3AF; letter-spacing: 1px; text-transform: uppercase;">
                Current Shift Availability
            </div>
        </div>

        <div class="card-grid">
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
                        $isAvailable = ($row['attendance_status'] === 'checked_in');
                        $badgeText = $isAvailable ? "ON FLOOR" : "OFF DUTY";
                        $badgeClass = $isAvailable ? "active" : "";
                        $statusVal = $isAvailable ? "Active" : "Off Duty";
                        $statusValClass = $isAvailable ? "active" : "";
                        
                        $imgSrc = 'uploads/universal_trainer_profile.png';
                        // Keep simple image check or default
             ?>
            <div class="trainer-card">
                <span class="tc-status-badge <?php echo $badgeClass; ?>">
                    <?php if($isAvailable): ?><i class="fas fa-circle" style="font-size:6px; margin-right:4px;"></i><?php endif; ?>
                    <?php echo $badgeText; ?>
                </span>
                
                <div class="tc-img-wrapper">
                    <div class="tc-img-ring <?php echo $badgeClass; ?>"></div>
                    <img src="<?php echo $imgSrc; ?>" class="tc-img" alt="Trainer">
                </div>
                
                <h3 class="tc-name"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h3>
                <div class="tc-role"><?php echo htmlspecialchars($row['trainer_specialization']); ?></div>
                
                <div class="tc-footer">
                    <div class="tc-stat">
                        <span class="tc-label">STATUS</span>
                        <div class="tc-value <?php echo $statusValClass; ?>"><?php echo $statusVal; ?></div>
                    </div>
                    <div class="tc-stat">
                        <span class="tc-label">EXP.</span>
                        <div class="tc-value"><?php echo $row['trainer_experience']; ?> Years</div>
                    </div>
                </div>
            </div>
            <?php
                    }
                } else {
                    echo "<p>No trainers found.</p>";
                }
            ?>
        </div>
    </section>

</main>

<?php include 'footer.php'; ?>

</body>
</html>
