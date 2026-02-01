<?php
session_start();
// Security Check for Gym Owner
$allowed_email = 'ashakayaplackal@gmail.com';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email']) || strtolower($_SESSION['user_email']) !== strtolower($allowed_email)) {
    header("Location: login.php?error=unauthorized_gym_owner");
    exit();
}

require 'db_connect.php';

// Fetch Offline Trainers
$sql = "SELECT u.*, 
        (SELECT 'checked_in' FROM trainer_attendance ta 
         WHERE ta.trainer_id = u.user_id AND status = 'checked_in' 
         LIMIT 1) as attendance_status,
        (SELECT check_in_time FROM trainer_attendance ta 
         WHERE ta.trainer_id = u.user_id AND status = 'checked_in'
         ORDER BY ta.check_in_time DESC LIMIT 1) as check_in_time
        FROM users u 
        WHERE u.role = 'trainer' 
        AND u.trainer_type = 'offline' 
        AND u.account_status = 'active'
        ORDER BY u.first_name ASC";
$result = $conn->query($sql);

$trainers = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $trainers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trainers - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #0F2C59; --accent: #4FACFE; --bg: #F8F9FA; --white: #FFF; --shadow: 0 4px 15px rgba(0,0,0,0.05); }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--bg); color: #333; display: flex; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: #0F2C59; height: 100vh; position: fixed; padding: 30px 20px; display: flex; flex-direction: column; box-shadow: 4px 0 15px rgba(0,0,0,0.1); color: #fff; z-index: 100; }
        .logo { font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 40px; display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 0 10px; }
        .menu-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.5); font-weight: 700; margin-bottom: 15px; padding-left: 15px; }
        .menu-item { display: flex; align-items: center; padding: 12px 15px; margin-bottom: 8px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 10px; font-weight: 500; transition: all 0.2s ease; font-size: 0.95rem; }
        .menu-item:hover { background: rgba(255,255,255,0.1); color: #fff; transform: translateX(2px); }
        .menu-item.active { background: #fff; color: var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.1); font-weight: 700; }
        .menu-item.active i { opacity: 1; color: var(--primary); }
        
        .user-profile { margin-top: auto; padding: 20px 15px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; border: 1px solid rgba(255,255,255,0.1); }
        .user-info div:first-child { font-weight: 600; font-size: 0.9rem; color: #fff; }
        .user-info div:last-child { font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-bottom: 4px; }
        .user-info a { color: var(--accent); font-size: 0.75rem; text-decoration: none; transition: 0.2s; }
        .user-info a:hover { text-decoration: underline; color: #fff; }
        
        .main-content { margin-left: 260px; padding: 40px; width: 100%; min-height: 100vh; }
        
        /* Trainer Grid - Compact */
        .trainer-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .trainer-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #eee; display: flex; flex-direction: column; }
        
        .t-header { position: relative; height: 70px; background: linear-gradient(135deg, #0F2C59, #1e4b8f); display: flex; align-items: center; justify-content: center; }
        .t-avatar { width: 65px; height: 65px; border-radius: 50%; border: 3px solid white; object-fit: cover; background: #ddd; position: absolute; bottom: -32px; }
        
        .t-body { padding: 40px 15px 15px; text-align: center; flex: 1; }
        .t-name { font-size: 1rem; font-weight: 700; color: #333; margin-bottom: 3px; }
        .t-spec { color: var(--accent); font-weight: 600; font-size: 0.8rem; margin-bottom: 10px; display: block; }
        
        .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 50px; font-weight: 600; font-size: 0.7rem; }
        .status-on { background: #dcfce7; color: #166534; }
        .status-off { background: #f3f4f6; color: #6b7280; }
        
        .t-footer { background: #fcfcfc; border-top: 1px solid #eee; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="#" class="logo"><i class="fas fa-dumbbell"></i> FitNova Gym</a>
        
        <div class="menu-title">Main Menu</div>
        <a href="gym_owner_dashboard.php" class="menu-item"><i class="fas fa-chart-pie"></i> Overview</a>
        <a href="gym_owner_clients.php" class="menu-item"><i class="fas fa-users"></i> Clients</a>
        <a href="home.php" class="menu-item"><i class="fas fa-home"></i> Home</a>
        <a href="gym.php" class="menu-item"><i class="fas fa-building"></i> Offline Gym Page</a>
        <a href="fitshop.php" class="menu-item"><i class="fas fa-shopping-bag"></i> Fitshop</a>
        <a href="gym_owner_trainers.php" class="menu-item active"><i class="fas fa-users"></i> Trainers</a>
        
        <div class="user-profile">
            <div class="user-avatar">AK</div>
            <div class="user-info">
                <div>Asha Kayaplackal</div>
                <div style="margin-bottom: 2px;">Offline Owner</div>
                <a href="logout.php" style="color: var(--accent); font-size: 0.8rem; font-weight: 600; text-decoration: none;">Sign Out</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 style="color: var(--primary); margin-bottom: 10px;">Manage Trainers</h1>
        <p style="color: #666; margin-bottom: 30px;">Track trainer attendance and manage shifts.</p>

        <?php if(!empty($trainers)): ?>
            <div class="trainer-grid">
                <?php foreach($trainers as $trainer): 
                    $imgSrc = 'uploads/universal_trainer_profile.png';
                    $possibleFiles = ['uploads/profile_' . $trainer['user_id'] . '.jpg', 'uploads/profile_' . $trainer['user_id'] . '.png'];
                    foreach($possibleFiles as $file) { if(file_exists($file)) { $imgSrc = $file . '?v=' . time(); break; } }
                    
                    $isCheckedIn = ($trainer['attendance_status'] === 'checked_in');
                    $checkInTimestamp = strtotime($trainer['check_in_time']);
                    $isToday = date('Y-m-d') === date('Y-m-d', $checkInTimestamp);
                    $checkInTime = ($isCheckedIn) ? ($isToday ? date('h:i A', $checkInTimestamp) : date('M d, h:i A', $checkInTimestamp)) : '';
                ?>
                <div class="trainer-card">
                    <div class="t-header">
                        <img src="<?php echo $imgSrc; ?>" alt="Trainer" class="t-avatar">
                    </div>
                    <div class="t-body">
                        <div class="t-name"><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></div>
                        <span class="t-spec"><?php echo htmlspecialchars($trainer['trainer_specialization']); ?></span>
                        
                        <?php if($isCheckedIn): ?>
                            <div class="status-badge status-on"><div style="width: 6px; height: 6px; background: #22c55e; border-radius: 50%;"></div> On Duty</div>
                        <?php else: ?>
                            <div class="status-badge status-off"><div style="width: 6px; height: 6px; background: #9ca3af; border-radius: 50%;"></div> Off Duty</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="t-footer">
                        <?php if ($isCheckedIn): ?>
                            <div style="font-size: 0.9rem; color: #166534;"><i class="far fa-clock"></i> In: <strong><?php echo $checkInTime; ?></strong></div>
                        <?php endif; ?>

                        <div style="display: flex; gap: 10px;">
                            <a href="trainer_profile.php?id=<?php echo $trainer['user_id']; ?>" style="flex:1; text-align: center; text-decoration: none; padding: 8px; border: 1px solid #ddd; color: #555; border-radius: 6px; font-weight: 500;">Profile</a>
                            <?php if (!$isCheckedIn): ?>
                                <button onclick="updateTrainerStatus(<?php echo $trainer['user_id']; ?>, 'clock_in')" style="flex:1; padding: 8px; border: none; background: #dcfce7; color: #166534; border-radius: 6px; cursor: pointer; font-weight: 600;">Check In</button>
                            <?php else: ?>
                                <button onclick="updateTrainerStatus(<?php echo $trainer['user_id']; ?>, 'clock_out')" style="flex:1; padding: 8px; border: none; background: #fee2e2; color: #991b1b; border-radius: 6px; cursor: pointer; font-weight: 600;">Clock Out</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No offline trainers found.</p>
        <?php endif; ?>
    </div>

    <script>
        function updateTrainerStatus(trainerId, action) {
            const actionText = action === 'clock_in' ? 'check in' : 'clock out';
            if (!confirm(`Are you sure you want to ${actionText} this trainer?`)) return;

            fetch('admin_trainer_attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ trainer_id: trainerId, action: action })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' || data.status === 'checked_in' || data.status === 'checked_out') {
                    alert('Success: ' + data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => console.error(err));
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
