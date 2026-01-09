<?php
session_start();
include 'header.php';
require 'db_connect.php';

// Fetch all real trainers from database
$sql = "SELECT * FROM users WHERE role = 'trainer' AND account_status = 'active' ORDER BY first_name";
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
    <title>Our Expert Trainers - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #0F2C59; --accent-color: #4FACFE; }
        body { background: #F8F9FA; font-family: 'Outfit', sans-serif; }
        .hero { padding: 80px 0; background: #0F2C59; color: white; text-align: center; }
        .hero h1 { font-size: 3rem; margin-bottom: 15px; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        
        .category-section { margin-bottom: 60px; }
        .cat-title { font-size: 1.8rem; color: var(--primary-color); margin-bottom: 25px; border-left: 5px solid var(--accent-color); padding-left: 15px; }
        
        .trainer-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }
        .trainer-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; text-align: center; }
        .trainer-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        
        .t-img { width: 100%; height: 260px; object-fit: cover; background: #eee; }
        .t-info { padding: 25px; }
        .t-name { font-size: 1.4rem; font-weight: 700; color: #333; margin-bottom: 5px; }
        .t-spec { color: var(--accent-color); font-weight: 600; font-size: 0.9rem; margin-bottom: 15px; display: block; }
        .t-bio { font-size: 0.9rem; color: #666; margin-bottom: 20px; }
        .btn-book { display: inline-block; padding: 10px 25px; background: var(--primary-color); color: white; border-radius: 50px; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .btn-book:hover { background: var(--accent-color); }
    </style>
</head>
<body>
    <div class="hero">
        <div class="container" style="padding:0;">
            <h1>Meet Our Trainers</h1>
            <p>Categorized by their area of expertise to help you find your perfect match.</p>
        </div>
    </div>

    <div class="container">
        <?php if(!empty($trainers)): ?>
            <div class="category-section">
                <h2 class="cat-title">Our Expert Trainers</h2>
                <div class="trainer-grid">
                    <?php foreach($trainers as $trainer): 
                        // Determine image source
                        $imgSrc = 'https://images.unsplash.com/photo-1548690312-e3b507d17a12?auto=format&fit=crop&q=80&w=400'; // Default
                        if (!empty($trainer['profile_picture'])) {
                            $imgSrc = $trainer['profile_picture'];
                        } elseif (!empty($trainer['image_url'])) {
                            $imgSrc = $trainer['image_url'];
                        }
                        
                        // Build profile URL - only real trainers now
                        $profileUrl = 'trainer_profile.php?id=' . $trainer['user_id'];
                        
                        // Get specialization or default
                        $specialization = $trainer['specialization'] ?? 'Personal Trainer';
                        $bio = $trainer['bio'] ?? 'Expert in guiding clients to achieve their personal fitness goals through customized plans.';
                    ?>
                        <div class="trainer-card">
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Trainer" class="t-img">
                            <div class="t-info">
                                <div class="t-name"><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></div>
                                <span class="t-spec"><?php echo htmlspecialchars($specialization); ?></span>
                                <p class="t-bio"><?php echo htmlspecialchars(substr($bio, 0, 100)); ?><?php echo strlen($bio) > 100 ? '...' : ''; ?></p>
                                <a href="<?php echo htmlspecialchars($profileUrl); ?>" class="btn-book">View Profile</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="category-section" style="text-align: center; padding: 80px 20px;">
                <h2 style="color: #999; font-size: 2rem; margin-bottom: 20px;">No Trainers Available</h2>
                <p style="color: #666; font-size: 1.1rem;">Check back soon for our expert trainers!</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
