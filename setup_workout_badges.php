<?php
require 'db_connect.php';

// 1. Add completed_workouts column to stats table if not exists
$checkCol = $conn->query("SHOW COLUMNS FROM user_gamification_stats LIKE 'completed_workouts'");
if ($checkCol->num_rows == 0) {
    $conn->query("ALTER TABLE user_gamification_stats ADD COLUMN completed_workouts INT DEFAULT 0");
    echo "Column 'completed_workouts' added.<br>";
}

// 2. Seed Workout Milestone Badges
$badges = [
    ['Starter', 'Completed 5 Workouts', 'fas fa-walking', '#3498DB', 'workout_milestone', 5],
    ['Regular', 'Completed 10 Workouts', 'fas fa-running', '#2ECC71', 'workout_milestone', 10],
    ['Dedicated', 'Completed 15 Workouts', 'fas fa-dumbbell', '#9B59B6', 'workout_milestone', 15],
    ['Athlete', 'Completed 20 Workouts', 'fas fa-swimmer', '#E67E22', 'workout_milestone', 20],
    ['Machine', 'Completed 25 Workouts', 'fas fa-robot', '#E74C3C', 'workout_milestone', 25],
    ['Ironborn', 'Completed 50 Workouts', 'fas fa-mountain', '#34495E', 'workout_milestone', 50]
];

foreach ($badges as $b) {
    $check = $conn->query("SELECT badge_id FROM gamification_badges WHERE name = '" . $conn->real_escape_string($b[0]) . "'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO gamification_badges (name, description, icon_class, color, criteria_type, criteria_value) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $b[0], $b[1], $b[2], $b[3], $b[4], $b[5]);
        $stmt->execute();
        echo "Seeded badge: {$b[0]}<br>";
    }
}

echo "Workout badges setup complete.";
?>
