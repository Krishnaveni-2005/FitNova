<?php
session_start();
require 'db_connect.php';

// Fetch On-Site Trainers (Currently Checked In Today)
// Fetch All Active Offline Trainers
$sql = "SELECT u.*, 
        (SELECT status FROM trainer_attendance ta 
         WHERE ta.trainer_id = u.user_id AND DATE(ta.check_in_time) = CURDATE() 
         ORDER BY ta.check_in_time DESC LIMIT 1) as attendance_status,
        (SELECT check_in_time FROM trainer_attendance ta 
         WHERE ta.trainer_id = u.user_id AND DATE(ta.check_in_time) = CURDATE() 
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
    <title>Gym Trainers - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #0F2C59; --accent-color: #4FACFE; }
        body { background: #F8F9FA; font-family: 'Outfit', sans-serif; margin: 0; }
        
        .header-section {
            background: #0F2C59; color: white; padding: 25px 20px; text-align: center;
        }
        .header-section h1 { margin: 0; font-size: 1.8rem; }
        .header-section p { opacity: 0.8; margin-top: 5px; font-size: 0.9rem; }

        .container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
        
        .trainer-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        
        .trainer-card { 
            background: white; border-radius: 12px; overflow: hidden; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: 0.3s; 
            display: flex; flex-direction: column;
            border: 1px solid #eee;
        }
        .trainer-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        
        .t-header { 
            position: relative; height: 90px; background: linear-gradient(135deg, #0F2C59, #1e4b8f); 
            display: flex; align-items: center; justify-content: center;
        }
        .t-avatar { 
            width: 80px; height: 80px; border-radius: 50%; 
            border: 3px solid white; object-fit: cover; background: #ddd;
            position: absolute; bottom: -40px;
        }
        
        .t-body { padding: 50px 15px 15px; text-align: center; flex: 1; }
        .t-name { font-size: 1.1rem; font-weight: 700; color: #333; margin-bottom: 3px; }
        .t-spec { color: var(--accent-color); font-weight: 600; font-size: 0.8rem; margin-bottom: 10px; display: block; }
        .t-status { 
            display: inline-flex; align-items: center; gap: 6px;
            background: #dcfce7; color: #166534; padding: 4px 12px; 
            border-radius: 50px; font-weight: 600; font-size: 0.75rem;
        }
        .dot { width: 6px; height: 6px; background: #22c55e; border-radius: 50%; display: inline-block; }
        
        .t-footer { 
            background: #fcfcfc; border-top: 1px solid #eee; 
            padding: 12px; text-align: center; color: #666; font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="header-section">
        <h1>Gym Trainers</h1>
        <p>Expert trainers available at FitNova Gym</p>
    </div>

    <div class="container">
        <?php if(!empty($trainers)): ?>
            <div class="trainer-grid">
                <?php foreach($trainers as $trainer): 
                    // Image Logic
                    $imgSrc = 'uploads/universal_trainer_profile.png';
                    $possibleFiles = [
                        'uploads/profile_' . $trainer['user_id'] . '.jpg',
                        'uploads/profile_' . $trainer['user_id'] . '.png'
                    ];
                    foreach ($possibleFiles as $file) {
                        if (file_exists($file)) { $imgSrc = $file; break; }
                    }
                    
                    $isCheckedIn = ($trainer['attendance_status'] === 'checked_in');
                    $checkInTime = $isCheckedIn ? date('h:i A', strtotime($trainer['check_in_time'])) : '--:--';
                ?>
                    <div class="trainer-card">
                        <div class="t-header">
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="t-avatar">
                        </div>
                        <div class="t-body">
                            <div class="t-name"><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></div>
                            <span class="t-spec"><?php echo htmlspecialchars($trainer['trainer_specialization'] ?? 'Trainer'); ?></span>
                            
                            <?php if ($isCheckedIn): ?>
                                <div class="t-status">
                                    <span class="dot"></span> Checked In
                                </div>
                            <?php else: ?>
                                <div class="t-status" style="background: #f1f5f9; color: #64748b;">
                                    <span class="dot" style="background: #94a3b8;"></span> Off Duty
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="t-footer" style="display: flex; flex-direction: column; gap: 10px; padding: 15px;">
                            <?php if ($isCheckedIn): ?>
                                <div style="display: flex; justify-content: center; align-items: center; gap: 6px;">
                                    <i class="far fa-clock"></i> Checked in: <strong><?php echo $checkInTime; ?></strong>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; justify-content: center; align-items: center; gap: 6px;">
                                    <i class="fas fa-building"></i> FitNova Gym Trainer
                                </div>
                            <?php endif; ?>
                            
                            <a href="trainer_profile.php?id=<?php echo $trainer['user_id']; ?>" style="display: block; background: var(--primary-color); color: white; padding: 8px 0; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 0.9rem; transition: 0.2s;" onmouseover="this.style.background='#1e4b8f'" onmouseout="this.style.background='var(--primary-color)'">View Profile</a>

                            <?php 
                            // Gym Owner Controls
                            $isOwner = (isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'ashakayaplackal@gmail.com');
                            if ($isOwner): 
                            ?>
                                <div style="display: flex; gap: 10px; margin-top: 5px;">
                                    <?php if (!$isCheckedIn): ?>
                                        <button onclick="updateTrainerStatus(<?php echo $trainer['user_id']; ?>, 'clock_in')" style="flex: 1; padding: 8px; border: 1px solid #166534; background: #dcfce7; color: #166534; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem;">Check In</button>
                                    <?php else: ?>
                                        <button onclick="updateTrainerStatus(<?php echo $trainer['user_id']; ?>, 'clock_out')" style="flex: 1; padding: 8px; border: 1px solid #991b1b; background: #fee2e2; color: #991b1b; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem;">Clock Out</button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 80px 20px;">
                <i class="fas fa-users-slash" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                <h2 style="color: #666;">No Trainers Found</h2>
                <p style="color: #888;">No offline trainers are currently registered.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateTrainerStatus(trainerId, action) {
            // Confirm logic
            const actionText = action === 'clock_in' ? 'check in' : 'clock out';
            if (!confirm(`Are you sure you want to ${actionText} this trainer?`)) return;

            fetch('admin_trainer_attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: action, trainer_id: trainerId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
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
