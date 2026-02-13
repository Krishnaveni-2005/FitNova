<?php
require 'db_connect.php';

// New Badges to fill the gaps (30, 35, 40, 45) to ensure 5-step increments
$newBadges = [
    ['Gladiator', 'Completed 30 Workouts.', 'fas fa-shield-virus', '#8E44AD', 'workout_milestone', 30],
    ['Warrior', 'Completed 35 Workouts.', 'fas fa-khanda', '#D35400', 'workout_milestone', 35],
    ['Champion', 'Completed 40 Workouts.', 'fas fa-trophy', '#F39C12', 'workout_milestone', 40],
    ['Titanium', 'Completed 45 Workouts.', 'fas fa-layer-group', '#7F8C8D', 'workout_milestone', 45]
];

foreach ($newBadges as $b) {
    // Check if exists by value to avoid collision if renmaed
    $check = $conn->query("SELECT badge_id FROM gamification_badges WHERE criteria_type = 'workout_milestone' AND criteria_value = " . $b[5]);
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO gamification_badges (name, description, icon_class, color, criteria_type, criteria_value) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $b[0], $b[1], $b[2], $b[3], $b[4], $b[5]);
        $stmt->execute();
        echo "Seeded badge: {$b[0]} ({$b[5]} workouts)<br>";
    }
}

// Higher Milestones (55 - 95)
$highBadges = [
    [55, 'Iron Will', 'fas fa-dumbbell'],
    [60, 'Steel Nerve', 'fas fa-shield-alt'],
    [65, 'Bronze Core', 'fas fa-medal'],
    [70, 'Silver Spirit', 'fas fa-ghost'],
    [75, 'Gold Heart', 'fas fa-heart'], // Gold
    [80, 'Platinum Power', 'fas fa-layer-group'],
    [85, 'Diamond Determination', 'fas fa-gem'],
    [90, 'Ruby Resolve', 'fas fa-square'],
    [95, 'Emerald Energy', 'fas fa-leaf']
];

foreach ($highBadges as $h) {
    $val = $h[0];
    $name = $h[1];
    $icon = $h[2];
    
    // Check by value
    $check = $conn->query("SELECT badge_id FROM gamification_badges WHERE criteria_type = 'workout_milestone' AND criteria_value = " . $val);
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO gamification_badges (name, description, icon_class, color, criteria_type, criteria_value) VALUES (?, ?, ?, ?, ?, ?)");
        $c = '#34495E'; // Default dark
        if($val % 10 == 0) $c = '#2980B9'; // Blue for tens
        if($val == 75) $c = '#F1C40F'; // Gold
        
        $desc = "Completed $val Workouts.";
        $type = 'workout_milestone';
        
        $stmt->bind_param("sssssi", $name, $desc, $icon, $c, $type, $val);
        $stmt->execute();
        echo "Seeded badge: $name ($val workouts)<br>";
    }
}

echo "Workout increments updated.";
?>
