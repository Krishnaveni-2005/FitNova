<?php
require 'db_connect.php';

// 1. Add total_calories column to stats table if not exists
$checkCol = $conn->query("SHOW COLUMNS FROM user_gamification_stats LIKE 'total_calories'");
if ($checkCol->num_rows == 0) {
    $conn->query("ALTER TABLE user_gamification_stats ADD COLUMN total_calories INT DEFAULT 0");
    echo "Column 'total_calories' added.<br>";
}

// 2. See Extra Badges
$badges = [
    // Streak Milestones
    ['Unbreakable', '30-Day Login Streak. Consistency is your middle name.', 'fas fa-shield-alt', '#8E44AD', 'streak', 30],
    ['Titan', '100-Day Login Streak. You are a fitness god.', 'fas fa-gavel', '#2C3E50', 'streak', 100],

    // Workout Milestones (Extended)
    ['Centurion', 'Completed 100 Workouts.', 'fas fa-chess-king', '#F1C40F', 'workout_milestone', 100],
    
    // Calorie Milestones
    ['Spark', 'Burned your first 500 Calories.', 'fas fa-fire', '#E67E22', 'calories_milestone', 500],
    ['Furnace', 'Burned 5,000 total Calories.', 'fas fa-fire-alt', '#D35400', 'calories_milestone', 5000],
    ['Fusion Reactor', 'Burned 25,000 total Calories.', 'fas fa-atom', '#C0392B', 'calories_milestone', 25000],
    ['Supernova', 'Burned 100,000 total Calories. Unbelievable energy!', 'fas fa-star', '#9B59B6', 'calories_milestone', 100000],

    // Time / Special
    ['Night Shift', 'Logged activity after 9 PM.', 'fas fa-moon', '#34495E', 'late_workout', 1],
    ['Weekend Warrior', 'Logged activity on a weekend.', 'fas fa-calendar-week', '#2ECC71', 'weekend_workout', 1]
];

foreach ($badges as $b) {
    $check = $conn->query("SELECT badge_id FROM gamification_badges WHERE name = '" . $conn->real_escape_string($b[0]) . "'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO gamification_badges (name, description, icon_class, color, criteria_type, criteria_value) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $b[0], $b[1], $b[2], $b[3], $b[4], $b[5]);
        $stmt->execute();
        echo "Seeded badge: {$b[0]}<br>";
    } else {
        // Optional: Update existing if criteria changed
    }
}

echo "Extra badges setup complete.";
?>
