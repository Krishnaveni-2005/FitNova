<?php
session_start();
require 'db_connect.php';

// Get trainer ID from URL
$trainer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$trainer_name = isset($_GET['name']) ? $_GET['name'] : '';
$is_mock = isset($_GET['mock']) ? true : false;

$trainer = null;

if ($is_mock && $trainer_name) {
    // Handle mock trainer data
    $nameParts = explode(' ', $trainer_name);
    $firstName = $nameParts[0] ?? 'Trainer';
    $lastName = $nameParts[1] ?? '';
    
    // Get category from URL
    $category = isset($_GET['category']) ? $_GET['category'] : 'Strength & Conditioning';
    
    // Determine image based on name and category
    $catPrefix = strtolower(explode(' ', $category)[0]);
    $imgFile = isset($_GET['img']) ? $_GET['img'] : $catPrefix . '_0.jpg';
    
    $trainer = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => strtolower($firstName) . '@fitnova.com',
        'specialization' => $category,
        'bio' => 'Certified expert with over 5 years of experience in ' . $category . '. Passionate about helping clients achieve their fitness goals through personalized training programs.',
        'image_url' => 'assets/trainers/' . $imgFile,
        'experience_years' => rand(3, 10),
        'certifications' => 'Certified Personal Trainer, Nutrition Specialist',
        'is_mock' => true
    ];
} elseif ($trainer_id > 0) {
    // Fetch real trainer from database
    $sql = "SELECT * FROM users WHERE user_id = ? AND role = 'trainer'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $trainer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $trainer = $result->fetch_assoc();
    }
    $stmt->close();
}

// Logic for Client Request
$user_id = $_SESSION['user_id'] ?? 0;
$current_status = 'none';
$assigned_id = 0;
$request_sent = false;

if ($user_id) {
    // Check current status
    $statusSql = "SELECT assigned_trainer_id, assignment_status FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($statusSql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $statusRes = $stmt->get_result()->fetch_assoc();
    if ($statusRes) {
        $current_status = $statusRes['assignment_status'];
        $assigned_id = $statusRes['assigned_trainer_id'];
    }
    $stmt->close();
    
    // Handle Hire Request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hire_trainer'])) {
        $updateSql = "UPDATE users SET assigned_trainer_id = ?, assignment_status = 'pending' WHERE user_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $trainer_id, $user_id);
        if ($stmt->execute()) {
            $request_sent = true;
            $current_status = 'pending';
            $assigned_id = $trainer_id;
        }
        $stmt->close();
    }
}

// If no trainer found, redirect back
if (!$trainer) {
    header("Location: trainers.php");
    exit();
}

include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?> - FitNova Trainer</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #4FACFE;
            --text-dark: #1A1A1A;
            --text-light: #555;
            --bg-light: #F8F9FA;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg-light); color: var(--text-dark); }
        
        .profile-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a4d8f 100%);
            padding: 80px 0 60px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .profile-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80') center/cover;
            opacity: 0.1;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .profile-image {
            width: 200px;
            height: 200px;
            border-radius: 20px;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .profile-info h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .specialization {
            display: inline-block;
            background: var(--accent-color);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .profile-stats {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stat-item i {
            font-size: 1.2rem;
            color: var(--accent-color);
        }
        
        .content-section {
            background: white;
            margin: -40px auto 40px;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            max-width: 1200px;
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--accent-color);
        }
        
        .about-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-light);
            margin-bottom: 40px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .info-card {
            background: var(--bg-light);
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid var(--accent-color);
        }
        
        .info-card h3 {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-card p {
            color: var(--text-light);
            line-height: 1.6;
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a4d8f 100%);
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            color: white;
            margin-top: 40px;
        }
        
        .cta-section h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .cta-section p {
            font-size: 1.1rem;
            margin-bottom: 25px;
            opacity: 0.9;
        }
        
        .btn-book {
            display: inline-block;
            background: white;
            color: var(--primary-color);
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn-book:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            font-size: 1rem;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            gap: 15px;
        }
        
        .certifications-list {
            list-style: none;
            padding: 0;
        }
        
        .certifications-list li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: var(--text-light);
        }
        
        .certifications-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--accent-color);
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-info h1 {
                font-size: 2rem;
            }
            
            .content-section {
                padding: 30px 20px;
                margin: -20px 20px 20px;
            }
            
            .profile-stats {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-hero">
        <div class="container">
            <a href="trainers.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to All Trainers
            </a>
            
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($trainer['image_url'] ?? $trainer['profile_picture'] ?? 'https://images.unsplash.com/photo-1548690312-e3b507d17a12?auto=format&fit=crop&q=80&w=400'); ?>" 
                     alt="<?php echo htmlspecialchars($trainer['first_name']); ?>" 
                     class="profile-image">
                
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></h1>
                    <span class="specialization">
                        <?php echo htmlspecialchars($trainer['specialization'] ?? 'Personal Trainer'); ?>
                    </span>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <i class="fas fa-award"></i>
                            <span><?php echo isset($trainer['experience_years']) ? $trainer['experience_years'] : '5+'; ?> Years Experience</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <span>100+ Clients Trained</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-star"></i>
                            <span>4.9/5 Rating</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="content-section">
            <h2 class="section-title">About <?php echo htmlspecialchars($trainer['first_name']); ?></h2>
            <p class="about-text">
                <?php 
                echo htmlspecialchars($trainer['bio'] ?? 'Passionate fitness professional dedicated to helping clients achieve their health and wellness goals through personalized training programs and expert guidance.');
                ?>
            </p>
            
            <div class="info-grid">
                <div class="info-card">
                    <h3><i class="fas fa-certificate"></i> Certifications</h3>
                    <ul class="certifications-list">
                        <?php 
                        $certs = isset($trainer['certifications']) ? explode(',', $trainer['certifications']) : ['Certified Personal Trainer', 'Nutrition Specialist'];
                        foreach($certs as $cert): 
                        ?>
                            <li><?php echo htmlspecialchars(trim($cert)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-dumbbell"></i> Specialization</h3>
                    <p><?php echo htmlspecialchars($trainer['specialization'] ?? 'Personal Training'); ?></p>
                    <p style="margin-top: 10px; font-size: 0.95rem;">
                        Expert in creating customized workout plans tailored to individual fitness levels and goals.
                    </p>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-envelope"></i> Contact</h3>
                    <p><?php echo htmlspecialchars($trainer['email'] ?? 'trainer@fitnova.com'); ?></p>
                    <p style="margin-top: 10px; font-size: 0.95rem;">
                        Available for online and in-person training sessions.
                    </p>
                </div>
            </div>
            
            <div class="cta-section">
                <h3>Ready to Start Your Fitness Journey?</h3>
                <p>Book a session with <?php echo htmlspecialchars($trainer['first_name']); ?> and take the first step towards your goals!</p>
                <?php if (!$user_id): ?>
                    <a href="login.php" class="btn-book">Sign In to Hire</a>
                <?php else: ?>
                    <?php if ($assigned_id == $trainer_id && $current_status === 'approved'): ?>
                        <div style="margin-bottom: 20px;">
                            <span style="background: #28a745; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">Your Trainer</span>
                        </div>
                        <a href="messages.php?trainer=<?php echo $trainer_id; ?>" class="btn-book">Message Trainer</a>
                    <?php elseif ($assigned_id == $trainer_id && $current_status === 'pending'): ?>
                         <button class="btn-book" style="background: #e0e0e0; color: #555; cursor: default;" disabled>Request Pending</button>
                         <p style="margin-top: 10px; font-size: 0.9rem;">Waiting for trainer approval.</p>
                    <?php elseif ($current_status === 'approved' || $current_status === 'pending'): ?>
                        <p>You already have an assigned trainer or a pending request.</p>
                        <a href="trainer_profile.php?id=<?php echo $assigned_id; ?>" style="color: white; text-decoration: underline;">View My Trainer</a>
                    <?php else: ?>
                        <form method="POST">
                            <button type="submit" name="hire_trainer" class="btn-book" style="border:none; cursor:pointer;">Send Hire Request</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>
