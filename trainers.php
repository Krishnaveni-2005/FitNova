<?php
session_start();

require 'db_connect.php';

// Fetch all real trainers from database with their specializations and bios
$sql = "SELECT user_id, first_name, last_name, trainer_specialization, bio FROM users WHERE role = 'trainer' AND account_status = 'active' ORDER BY first_name";
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
        
        .trainer-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
        .trainer-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: 0.3s; text-align: center; display: flex; flex-direction: column; padding-bottom: 15px; }
        .trainer-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        
        /* Compact Image Style */
        .t-img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; margin: 20px auto 10px; display: block; border: 3px solid #f8f9fa; }
        
        .t-info { padding: 0 15px 10px; display: flex; flex-direction: column; height: 100%; }
        .t-name { font-size: 1.1rem; font-weight: 700; color: #333; margin-bottom: 3px; }
        .t-spec { color: var(--accent-color); font-weight: 600; font-size: 0.8rem; margin-bottom: 10px; display: block; }
        .t-bio { font-size: 0.85rem; color: #666; margin-bottom: 15px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .btn-book { display: inline-block; padding: 6px 20px; background: var(--primary-color); color: white; border-radius: 50px; text-decoration: none; font-size: 0.8rem; transition: 0.3s; margin-top: auto; align-self: center; }
        .btn-book:hover { background: var(--accent-color); }
        
        /* Search Box Styles */
        .search-box { position: relative; max-width: 600px; margin: 0 auto; }
        .search-input { width: 100%; padding: 15px 50px 15px 20px; border: 2px solid #ddd; border-radius: 50px; font-size: 1rem; outline: none; transition: all 0.3s ease; font-family: 'Outfit', sans-serif; }
        .search-input:focus { border-color: var(--accent-color); box-shadow: 0 0 0 4px rgba(79, 172, 254, 0.1); }
        .search-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 1.2rem; pointer-events: none; }
    
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
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
                
                <!-- Search Bar -->
                <div class="search-container" style="margin-bottom: 30px;">
                    <div class="search-box">
                        <input type="text" id="trainerSearch" class="search-input" placeholder="Search by specialization (e.g., Muscle Building, Yoga, Weight Loss...)">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                    <div id="noResults" style="display: none; text-align: center; padding: 40px; color: #999;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p style="font-size: 1.2rem; font-weight: 600;">No trainers found</p>
                        <p style="font-size: 0.95rem;">Try searching with different keywords</p>
                    </div>
                </div>
                
                <div class="trainer-grid">
                    <?php foreach($trainers as $trainer): 
                        // Determine image source
                        $imgSrc = 'uploads/universal_trainer_profile.png';
                        $possibleFiles = [
                            'uploads/profile_' . $trainer['user_id'] . '.jpg',
                            'uploads/profile_' . $trainer['user_id'] . '.png',
                            'uploads/profile_' . $trainer['user_id'] . '.jpeg'
                        ];
                        
                        foreach ($possibleFiles as $file) {
                            if (file_exists($file)) {
                                $imgSrc = $file;
                                break;
                            }
                        }
                        
                        // Build profile URL - only real trainers now
                        $profileUrl = 'trainer_profile.php?id=' . $trainer['user_id'];
                        
                        // Get specialization from the correct column
                        $specialization = $trainer['trainer_specialization'] ?? 'Personal Trainer';
                        
                        // Use bio from database (now contains specialization-specific descriptions)
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

    <script>
        // Search Functionality for Trainers
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('trainerSearch');
            const trainerCards = document.querySelectorAll('.trainer-card');
            const noResults = document.getElementById('noResults');
            const trainerGrid = document.querySelector('.trainer-grid');

            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                let hasVisibleTrainer = false;

                trainerCards.forEach(card => {
                    // Get the specialization text from the card
                    const specElement = card.querySelector('.t-spec');
                    const specialization = specElement ? specElement.textContent.toLowerCase() : '';
                    
                    // Show card ONLY if query matches specialization (not name or bio)
                    if (query === '' || specialization.includes(query)) {
                        card.style.display = 'flex';
                        hasVisibleTrainer = true;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show/hide no results message
                if (hasVisibleTrainer || query === '') {
                    noResults.style.display = 'none';
                    trainerGrid.style.display = 'grid';
                } else {
                    noResults.style.display = 'block';
                    trainerGrid.style.display = 'none';
                }
            });
        });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
