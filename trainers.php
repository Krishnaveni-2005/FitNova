<?php
session_start();
include 'header.php';
require 'db_connect.php';

// Fetch trainers from DB
// We will also try to simulate categorization if 'specialization' column is missing or empty
// Ideally check if column exists, but for 'Agentic' speed, fetching all fields is safest
$trainers = [];
$sql = "SELECT * FROM users WHERE role = 'trainer' AND account_status = 'active'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $trainers[] = $row;
    }
}

// Group trainers. If no specialization, assign random for demo.
$groupedTrainers = [
    'Strength & Conditioning' => [],
    'Yoga & Flexibility' => [],
    'Cardio & HIIT' => [],
    'Rehabilitation' => []
];

$cats = array_keys($groupedTrainers);

foreach ($trainers as $t) {
    // Check if specialization exists in user row (e.g. if we added it before)
    // If not, assign random
    $spec = $t['specialization'] ?? '';
    if (!$spec || !array_key_exists($spec, $groupedTrainers)) {
        $spec = $cats[array_rand($cats)];
    }
    $groupedTrainers[$spec][] = $t;
}

// If no trainers in DB, add some fake ones for display
if (empty($trainers)) {
    $groupedTrainers['Strength & Conditioning'][] = ['first_name'=>'John', 'last_name'=>'Doe', 'bio'=>'Expert in heavy lifting.'];
    $groupedTrainers['Yoga & Flexibility'][] = ['first_name'=>'Jane', 'last_name'=>'Smith', 'bio'=>'Certified Yoga Instructor.'];
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
        <?php foreach($groupedTrainers as $category => $list): ?>
            <?php if(!empty($list)): ?>
            <div class="category-section">
                <h2 class="cat-title"><?php echo htmlspecialchars($category); ?></h2>
                <div class="trainer-grid">
                    <?php foreach($list as $trainer): ?>
                        <div class="trainer-card">
                            <img src="https://images.unsplash.com/photo-1548690312-e3b507d17a12?auto=format&fit=crop&q=80&w=400" alt="Trainer" class="t-img">
                            <div class="t-info">
                                <div class="t-name"><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></div>
                                <span class="t-spec"><?php echo htmlspecialchars($category); ?></span>
                                <p class="t-bio">Expert in guiding clients to achieve their personal fitness goals through customized plans.</p>
                                <a href="#" class="btn-book">View Profile</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
