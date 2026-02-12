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

    // Fetch Client Count
    $countSql = "SELECT COUNT(*) as total FROM users WHERE assigned_trainer_id = ? AND assignment_status = 'approved'";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param("i", $trainer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $clientCount = 0;
    if ($row = $result->fetch_assoc()) {
        $clientCount = $row['total'];
    }
    $stmt->close();

    // Ensure ratings table exists
    $conn->query("CREATE TABLE IF NOT EXISTS trainer_ratings (
        rating_id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_id INT NOT NULL,
        user_id INT NOT NULL,
        rating DECIMAL(3,1) NOT NULL,
        review TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trainer_id) REFERENCES users(user_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )");

    // Fetch Average Rating
    $rateSql = "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM trainer_ratings WHERE trainer_id = ?";
    $stmt = $conn->prepare($rateSql);
    $stmt->bind_param("i", $trainer_id);
    $stmt->execute();
    $rateRes = $stmt->get_result()->fetch_assoc();
    $avgRating = $rateRes['avg_rating'] ? round($rateRes['avg_rating'], 1) : 0;
    $ratingCount = $rateRes['count'] ?? 0;
    $stmt->close();

    // Fetch Achievements
    $achievements = [];
    $achStmt = $conn->prepare("SELECT * FROM trainer_achievements WHERE trainer_id = ? ORDER BY date_earned DESC");
    if ($achStmt) {
        $achStmt->bind_param("i", $trainer_id);
        $achStmt->execute();
        $achRes = $achStmt->get_result();
        while ($row = $achRes->fetch_assoc()) {
            $achievements[] = $row;
        }
        $achStmt->close();
    }
}
$clientCount = $clientCount ?? 0;
$avgRating = $avgRating ?? 0;
$ratingCount = $ratingCount ?? 0;

// Logic for Client Request
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['user_role'] ?? 'guest';
$current_status = 'none';
$assigned_id = 0;
$request_sent = false;
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

if ($user_id) {
    // Check current status
    $statusSql = "SELECT first_name, last_name, assigned_trainer_id, assignment_status FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($statusSql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $statusRes = $stmt->get_result()->fetch_assoc();
    if ($statusRes) {
        $current_status = $statusRes['assignment_status'];
        $assigned_id = $statusRes['assigned_trainer_id'];
        $clientName = $statusRes['first_name'] . ' ' . $statusRes['last_name'];
    }
    $stmt->close();
    
    // Handle Hire Request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hire_trainer'])) {
        if ($user_role === 'free') {
           // If user is free, they shouldn't trigger this via POST if UI logic is sound, 
           // but as a fallback/security layer, we set the msg.
           $msg = 'upgrade_required';
        } else {
            $updateSql = "UPDATE users SET assigned_trainer_id = ?, assignment_status = 'pending' WHERE user_id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("ii", $trainer_id, $user_id);
            if ($stmt->execute()) {
                $request_sent = true;
                $current_status = 'pending';
                $assigned_id = $trainer_id;
                
                // Add notification for the user
                $notifMsg = "You sent a request to Coach " . ($trainer['first_name'] ?? 'Trainer') . " and it is pending.";
                $notifSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, 'trainer_request_sent', ?)";
                $nStmt = $conn->prepare($notifSql);
                $nStmt->bind_param("is", $user_id, $notifMsg);
                $nStmt->execute();
                $nStmt->close();

                // Send Admin Notification (Dash + WhatsApp)
                require_once 'admin_notifications.php';
                $adminMsg = "Client $clientName has requested a trainer.";
                // Note: sendAdminNotification is globally available now
                if (function_exists('sendAdminNotification')) {
                    sendAdminNotification($conn, $adminMsg);
                } else {
                    error_log("sendAdminNotification function missing in trainer_profile.php");
                }
            }

            $stmt->close();
        }
    }
}

// If no trainer found, redirect back
if (!$trainer) {
    header("Location: trainers.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?> - FitNova Trainer</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        
        /* Modal for Image Preview */
        .img-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
        }
        .img-modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            max-height: 80vh;
            object-fit: contain;
        }
        .img-caption {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            text-align: center;
            color: #ccc;
            padding: 10px 0;
            height: 150px;
        }
        .img-close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }
        .img-close:hover,
        .img-close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }

        /* Compact Layout Styles */
        .profile-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a4d8f 100%);
            padding: 40px 0 50px; /* Reduced padding */
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
            max-width: 1100px; /* Slightly tighter container */
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 20px; /* Reduced margin */
        }
        
        .profile-image {
            width: 140px; /* Smaller image */
            height: 140px;
            border-radius: 16px;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .profile-info h1 {
            font-size: 2.2rem; /* Smaller font */
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .specialization {
            display: inline-block;
            background: var(--accent-color);
            color: white;
            padding: 5px 15px; /* Smaller padding */
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .profile-stats {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        
        .stat-item i {
            font-size: 1rem;
            color: var(--accent-color);
        }
        
        .content-section {
            background: white;
            margin: -30px auto 30px; /* Tighter overlap */
            border-radius: 16px;
            padding: 30px; /* Reduced padding */
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            max-width: 1100px;
        }
        
        .section-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
        }
        
        .about-text {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-light);
            margin-bottom: 30px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px; /* Reduced gap */
            margin-bottom: 30px;
        }
        
        .info-card {
            background: var(--bg-light);
            padding: 20px; /* Reduced padding */
            border-radius: 12px;
            border-left: 3px solid var(--accent-color);
        }
        
        .info-card h3 {
            font-size: 1.1rem;
            color: var(--primary-color);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-card p {
            color: var(--text-light);
            line-height: 1.5;
            font-size: 0.95rem;
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a4d8f 100%);
            padding: 30px; /* Reduced padding */
            border-radius: 12px;
            text-align: center;
            color: white;
            margin-top: 30px;
        }
        
        .cta-section h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .cta-section p {
            font-size: 1rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .btn-book {
            display: inline-block;
            background: white;
            color: var(--primary-color);
            padding: 12px 30px; /* Smaller button */
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        
        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            gap: 12px;
        }
        
        .certifications-list {
            list-style: none;
            padding: 0;
        }
        
        .certifications-list li {
            padding: 6px 0;
            padding-left: 25px;
            position: relative;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .certifications-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--accent-color);
            font-weight: bold;
            font-size: 1rem;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .profile-info h1 {
                font-size: 1.8rem;
            }
            
            .content-section {
                padding: 20px;
                margin: -20px 15px 20px;
            }
            
            .profile-stats {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <!-- Image Modal -->
    <div id="certModal" class="img-modal">
      <span class="img-close" onclick="document.getElementById('certModal').style.display='none'">&times;</span>
      <img class="img-modal-content" id="img01">
      <div id="caption" class="img-caption"></div>
    </div>

    <?php if ($msg === 'upgrade_required'): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Premium Feature',
                    text: 'Make payment for any subscription then only you can access the trainers.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Upgrade Now',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#0F2C59',
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'subscription_plans.php';
                    }
                });
            });
        </script>
    <?php endif; ?>


    <div class="profile-hero">
        <div class="container">

            
            <div class="profile-header">
                <img src="uploads/universal_trainer_profile.png" 
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
                            <span><?php echo $clientCount; ?> Clients Trained</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-star"></i>
                            <span><?php echo $ratingCount > 0 ? $avgRating . '/5 (' . $ratingCount . ' reviews)' : 'New Trainer'; ?></span>
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
                    
                    <?php if(!empty($trainer['trainer_certification'])): ?>
                        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #eee;">
                            <h4 style="font-size: 0.95rem; margin-bottom: 10px; color: var(--primary-color);">Verified Certificate</h4>
                            <a href="<?php echo htmlspecialchars($trainer['trainer_certification']); ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; background: #eef2ff; color: var(--primary-color); padding: 8px 15px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: 0.2s;">
                                <i class="fas fa-file-pdf"></i> View Official Document
                            </a>
                        </div>
                    <?php endif; ?>
                    <div style="margin-top: 20px; border-top: 1px dashed #eee; padding-top: 15px;">
                        <h4 style="font-size: 0.9rem; color: #888; margin-bottom: 15px; font-weight: 600;">Credentials Showcase</h4>
                        <?php if(!empty($achievements)): ?>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px;">
                                <?php foreach($achievements as $ach): ?>
                                    <div style="text-align:center;">
                                        <img src="<?php echo htmlspecialchars($ach['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($ach['title']); ?>" 
                                             style="width: 100%; aspect-ratio: 1/1; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-radius: 4px; cursor: pointer; transition: transform 0.2s;"
                                             onmouseover="this.style.transform='scale(1.05)'" 
                                             onmouseout="this.style.transform='scale(1)'"
                                             onclick="showImage('<?php echo htmlspecialchars($ach['image_url']); ?>', '<?php echo htmlspecialchars($ach['title']); ?>')"
                                        >
                                        <p style="font-size: 0.7rem; color: #666; margin-top: 5px; line-height: 1.2; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" title="<?php echo htmlspecialchars($ach['title']); ?>"><?php echo htmlspecialchars($ach['title']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="font-size: 0.85rem; color: #aaa; font-style: italic;">No uploaded credentials yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-dumbbell"></i> Specialization</h3>
                    <p><?php echo htmlspecialchars($trainer['specialization'] ?? 'Personal Training'); ?></p>
                    <p style="margin-top: 10px; font-size: 0.95rem;">
                        Expert in creating customized workout plans tailored to individual fitness levels and goals.
                    </p>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-address-book"></i> Contact</h3>
                    <p style="margin-bottom: 8px;"><i class="fas fa-envelope" style="color:var(--accent-color); width:20px;"></i> <?php echo htmlspecialchars($trainer['email'] ?? 'trainer@fitnova.com'); ?></p>
                    
                    <?php if(!empty($trainer['phone'])): ?>
                        <p style="margin-bottom: 8px;"><i class="fas fa-phone" style="color:var(--accent-color); width:20px;"></i> <?php echo htmlspecialchars($trainer['phone']); ?></p>
                    <?php endif; ?>

                    <p style="margin-top: 15px; font-size: 0.9rem; color: #888; border-top: 1px solid #eee; padding-top: 10px;">
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
                        <button type="button" class="btn-book" onclick="sendSessionRequest(<?php echo $trainer_id; ?>)">Request Session</button>
                    <?php else: ?>
                        <form method="POST">
                             <?php if (isset($user_role) && $user_role === 'free'): ?>
                                <button type="button" onclick="window.location.href='?id=<?php echo $trainer_id; ?>&msg=upgrade_required'" class="btn-book" style="background: #E63946; border: none; cursor: pointer;">Send Hire Request</button>
                             <?php elseif (isset($user_role) && $user_role === 'lite'): ?>
                                <div style="color: #b45309; background: #fffbeb; padding: 10px; border-radius: 8px; border: 1px solid #fcd34d; font-size: 0.9em; text-align: left;">
                                    <i class="fas fa-info-circle"></i> <strong>Lite Member</strong><br>
                                    You cannot pick a specific trainer directly. Use your Dashboard to request a match based on availability.
                                </div>
                                <a href="liteuser_dashboard.php" class="btn-book" style="background: #f59e0b; margin-top: 5px;">Go to Dashboard</a>
                             <?php else: ?>
                                <button type="submit" name="hire_trainer" class="btn-book" style="border:none; cursor:pointer;">Send Hire Request</button>
                             <?php endif; ?>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    <script>
    function showImage(src, title) {
        var modal = document.getElementById("certModal");
        var modalImg = document.getElementById("img01");
        var captionText = document.getElementById("caption");
        modal.style.display = "block";
        modalImg.src = src;
        captionText.innerHTML = title;
    }

    // Close the modal when clicking anywhere outside the image
    window.onclick = function(event) {
        var modal = document.getElementById("certModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function sendSessionRequest(trainerId) {
        Swal.fire({
            title: 'Sending Request...',
            text: 'Please wait',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('send_session_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                trainer_id: trainerId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Request Sent',
                    text: 'Your request has been sent and approval is pending.',
                    icon: 'success',
                    confirmButtonColor: '#0F2C59',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    title: 'Notice',
                    text: data.message,
                    icon: 'info',
                    confirmButtonColor: '#0F2C59'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'An error occurred while sending the request.',
                icon: 'error',
                confirmButtonColor: '#0F2C59'
            });
        });
    }
    </script>
</body>
</html>
