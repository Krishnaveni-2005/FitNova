<?php
session_start();

require 'db_connect.php';

// Fetch all real trainers from database with their specializations and bios and average rating
$sql = "SELECT users.user_id, users.first_name, users.last_name, users.trainer_specialization, users.bio, 
        COALESCE(AVG(trainer_reviews.rating), 0) as avg_rating, 
        COUNT(trainer_reviews.review_id) as review_count
        FROM users 
        LEFT JOIN trainer_reviews ON users.user_id = trainer_reviews.trainer_id
        WHERE users.role = 'trainer' AND users.account_status = 'active' 
        GROUP BY users.user_id
        ORDER BY users.first_name";
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
        .hero { padding: 80px 0; background: linear-gradient(135deg, #0F2C59 0%, #1565C0 100%); color: white; text-align: center; }
        .hero h1 { font-size: 3rem; margin-bottom: 15px; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        
        .category-section { margin-bottom: 60px; }
        .cat-title { font-size: 1.8rem; color: var(--primary-color); margin-bottom: 25px; border-left: 5px solid var(--accent-color); padding-left: 15px; }
        
        .trainer-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
        .trainer-card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: 0.3s; text-align: center; display: flex; flex-direction: column; padding-bottom: 12px; }
        .trainer-card:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.08); }
        
        /* Compact Image Style */
        .t-img { width: 70px; height: 70px; object-fit: cover; border-radius: 50%; margin: 15px auto 8px; display: block; border: 2px solid #f8f9fa; }
        
        .t-info { padding: 0 12px 8px; display: flex; flex-direction: column; height: 100%; }
        .t-name { font-size: 0.95rem; font-weight: 700; color: #333; margin-bottom: 2px; }
        .t-spec { color: var(--accent-color); font-weight: 600; font-size: 0.7rem; margin-bottom: 6px; display: block; }
        .t-bio { font-size: 0.75rem; color: #666; margin-bottom: 10px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .btn-book { display: inline-block; padding: 5px 16px; background: var(--primary-color); color: white; border-radius: 50px; text-decoration: none; font-size: 0.75rem; transition: 0.3s; margin-top: auto; align-self: center; }
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
                    <div class="star-filter" style="text-align:center; margin-top: 15px; user-select: none;">
                        <span style="font-size:0.9rem; color:#666; margin-right: 10px;">Filter by Rating:</span>
                        <span id="starFilter" style="font-size: 1.2rem; cursor: pointer;">
                            <i class="far fa-star" data-rating="1" style="color: #f1c40f;"></i>
                            <i class="far fa-star" data-rating="2" style="color: #f1c40f;"></i>
                            <i class="far fa-star" data-rating="3" style="color: #f1c40f;"></i>
                            <i class="far fa-star" data-rating="4" style="color: #f1c40f;"></i>
                            <i class="far fa-star" data-rating="5" style="color: #f1c40f;"></i>
                        </span>
                        <span id="clearFilter" style="font-size: 0.8rem; color: #E63946; margin-left: 10px; cursor: pointer; display: none;">(Clear)</span>
                    </div>
                    <div id="noResults" style="display: none; text-align: center; padding: 40px; color: #999;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p style="font-size: 1.2rem; font-weight: 600;">No trainers found</p>
                        <p style="font-size: 0.95rem;">Try searching with different keywords or rating</p>
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
                                <div class="t-rating" style="color: #f1c40f; margin-bottom: 8px; font-size: 0.9rem;">
                                    <?php 
                                    $rating = round($trainer['avg_rating'], 1);
                                    for($i=1; $i<=5; $i++) {
                                        if($rating >= $i) echo '<i class="fas fa-star"></i>';
                                        elseif($rating >= $i-0.5) echo '<i class="fas fa-star-half-alt"></i>';
                                        else echo '<i class="far fa-star"></i>';
                                    }
                                    echo " <span style='color: #888; font-size: 0.8rem; font-weight: 500;'>(" . ($trainer['review_count'] > 0 ? $rating : 'New') . ")</span>";
                                    ?>
                                </div>
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
            
            // Star Filter Logic
            const stars = document.querySelectorAll('#starFilter i');
            const clearFilter = document.getElementById('clearFilter');
            let currentRatingFilter = 0;

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = parseInt(this.getAttribute('data-rating'));
                    currentRatingFilter = rating;
                    
                    // Highlight stars
                    stars.forEach(s => {
                        const r = parseInt(s.getAttribute('data-rating'));
                        if (r <= rating) {
                            s.classList.remove('far');
                            s.classList.add('fas');
                        } else {
                            s.classList.remove('fas');
                            s.classList.add('far');
                        }
                    });
                    
                    clearFilter.style.display = 'inline';
                    filterTrainers();
                });
            });

            clearFilter.addEventListener('click', function() {
                currentRatingFilter = 0;
                stars.forEach(s => {
                    s.classList.remove('fas');
                    s.classList.add('far');
                });
                this.style.display = 'none';
                filterTrainers();
            });

            searchInput.addEventListener('input', filterTrainers);

            function filterTrainers() {
                const query = searchInput.value.toLowerCase().trim();
                let hasVisibleTrainer = false;

                trainerCards.forEach(card => {
                    // Get Text
                    const specElement = card.querySelector('.t-spec');
                    const specialization = specElement ? specElement.textContent.toLowerCase() : '';
                    
                    // Get Rating
                    const ratingElement = card.querySelector('.t-rating span');
                    let ratingVal = 0;
                    if (ratingElement) {
                        const rateText = ratingElement.textContent.replace(/[()]/g, '');
                        if (rateText.includes('New')) {
                            ratingVal = 0;
                        } else {
                            ratingVal = parseFloat(rateText) || 0;
                        }
                    }

                    // Check Text Match
                    const matchesQuery = (query === '' || specialization.includes(query));
                    
                    // Check Rating Match (Show trainers with rating >= filter)
                    // If filter is 5, show 4.5+? Usually >= filter.
                    // If filter is 4, show 4.0+
                    const matchesRating = (currentRatingFilter === 0 || ratingVal >= currentRatingFilter);

                    if (matchesQuery && matchesRating) {
                        card.style.display = 'flex';
                        hasVisibleTrainer = true;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show/hide no results
                if (hasVisibleTrainer) {
                    noResults.style.display = 'none';
                    trainerGrid.style.display = 'grid'; // Ensure grid style
                } else {
                    noResults.style.display = 'block';
                    trainerGrid.style.display = 'none';
                }
            }
        });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
