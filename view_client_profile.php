<?php
session_start();
require "db_connect.php";

// Check if trainer is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['user_id'])) {
    die("User ID not specified.");
}

$clientId = intval($_GET['user_id']);
$trainerId = $_SESSION['user_id'];

// Fetch client info and verify assignment
$sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.role, p.* 
        FROM users u 
        LEFT JOIN client_profiles p ON u.user_id = p.user_id 
        WHERE u.user_id = ? AND u.assigned_trainer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $clientId, $trainerId);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

if (!$client) {
    die("Client not found or not assigned to you.");
}

$initials = strtoupper(substr($client['first_name'], 0, 1) . substr($client['last_name'], 0, 1));
$age = $client['age'] ?? 'N/A';
$gender = ucfirst($client['gender'] ?? 'N/A');
$height = $client['height'] ?? 'N/A';
$weight = $client['weight'] ?? 'N/A';
$goal = ucfirst($client['goal'] ?? 'Not set');
$activityLevel = ucfirst($client['activity_level'] ?? 'Not set');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile - <?php echo htmlspecialchars($client['first_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --secondary-color: #DAC0A3;
            --accent-color: #E63946;
            --bg-color: #F8F9FA;
            --text-color: #333333;
            --text-light: #6C757D;
            --border-color: #E9ECEF;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-color); margin: 0; padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
        .header { background: var(--primary-color); color: white; padding: 40px; text-align: center; position: relative; }
        .back-btn { position: absolute; top: 20px; left: 20px; color: white; text-decoration: none; display: flex; align-items: center; gap: 5px; font-weight: 500; }
        .avatar { width: 100px; height: 100px; background: white; border-radius: 50%; color: var(--primary-color); display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 700; margin: 0 auto 15px; border: 4px solid var(--secondary-color); }
        h1 { font-family: 'Outfit', sans-serif; margin: 0; font-size: 24px; }
        .subtitle { opacity: 0.8; margin-top: 5px; font-size: 14px; }
        .content { padding: 40px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color); }
        .stat-label { font-size: 12px; color: var(--text-light); text-transform: uppercase; display: block; margin-bottom: 5px; }
        .stat-value { font-size: 18px; font-weight: 700; color: var(--primary-color); font-family: 'Outfit', sans-serif; }
        
        .section-title { font-size: 18px; font-weight: 600; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; color: #1e293b; }
        .info-row { display: flex; margin-bottom: 15px; }
        .info-label { width: 150px; color: var(--text-light); font-weight: 500; }
        .info-value { flex: 1; font-weight: 500; }
        
        .btn-action { display: inline-block; padding: 10px 20px; background: var(--secondary-color); color: #0F2C59; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 20px; transition: 0.3s; }
        .btn-action:hover { background: #cbb092; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="trainer_clients.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Lists</a>
            <div class="avatar"><?php echo $initials; ?></div>
            <h1><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></h1>
            <div class="subtitle"><?php echo htmlspecialchars($client['email']); ?> • <?php echo ucfirst($client['role']); ?> Member</div>
        </div>
        
        <div class="content">
            <div class="section-title"><i class="fas fa-chart-bar"></i> Physical Stats</div>
            <div class="stats-grid">
                <div class="stat-box">
                    <span class="stat-label">Age</span>
                    <span class="stat-value"><?php echo $age; ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label">Height</span>
                    <span class="stat-value"><?php echo $height; ?> cm</span>
                </div>
                <div class="stat-box">
                    <span class="stat-label">Weight</span>
                    <span class="stat-value"><?php echo $weight; ?> kg</span>
                </div>
                <div class="stat-box">
                    <span class="stat-label">Gender</span>
                    <span class="stat-value"><?php echo $gender; ?></span>
                </div>
            </div>
            
            <div class="section-title"><i class="fas fa-bullseye"></i> Fitness Goals</div>
            <div class="info-row">
                <div class="info-label">Primary Goal</div>
                <div class="info-value"><?php echo $goal; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Activity Level</div>
                <div class="info-value"><?php echo $activityLevel; ?></div>
            </div>
            
            <div class="section-title"><i class="fas fa-info-circle"></i> Additional Info</div>
            <div class="info-row">
                <div class="info-label">Ideally Workout Days</div>
                <div class="info-value"><?php echo $client['workout_days'] ?? 'Not specified'; ?> days/week</div>
            </div>
             <div class="info-row">
                <div class="info-label">Medical Conditions</div>
                <div class="info-value"><?php echo htmlspecialchars($client['medical_conditions'] ?? 'None'); ?></div>
            </div>
            
            <center>
                <a href="trainer_workouts.php?assign_to=<?php echo $clientId; ?>" class="btn-action">Create Workout Plan</a>
            </center>
        </div>
    </div>
</body>
</html>
