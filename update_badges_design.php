<?php
require 'db_connect.php';

// Array of updates: Old Name => [New Name, New Description, New Icon, New Color]
$updates = [
    'Newbie' => ['Nova Initiate', 'Started your FitNova journey.', 'fas fa-bolt', '#2ECC71'], // Green - Energy
    'Profile Pro' => ['Identity Forged', 'Completed your fitness profile.', 'fas fa-id-card-clip', '#3498DB'], // Blue
    'Gym Rat' => ['Iron Access', 'Unlocked offline gym access.', 'fas fa-dungeon', '#34495E'], // Dark Blue/Grey
    'Committed' => ['Momentum Builder', 'Logged in for 3 days in a row.', 'fas fa-forward', '#F1C40F'], // Yellow
    'Unstoppable' => ['Nova Streak', '7-day login streak. You are on fire!', 'fas fa-fire-flame-curved', '#E74C3C'], // Red
    'Early Bird' => ['Sunrise Grinder', 'Logged in before 8 AM.', 'fas fa-sun', '#F39C12'], // Orange
    
    // Workout Milestones
    'Starter' => ['Fit Start', 'Completed first 5 workouts.', 'fas fa-shoe-prints', '#1ABC9C'], // Teal
    'Regular' => ['Active Mover', 'Completed 10 workouts.', 'fas fa-person-running', '#16A085'], // Dark Teal
    'Dedicated' => ['Discipline Core', 'Completed 15 workouts.', 'fas fa-fist-raised', '#9B59B6'], // Purple
    'Athlete' => ['Elite Performer', 'Completed 20 workouts.', 'fas fa-medal', '#8E44AD'], // Dark Purple
    'Machine' => ['Endurance Master', 'Completed 25 workouts.', 'fas fa-heart-pulse', '#D35400'], // Pumpkin
    'Ironborn' => ['FitNova Legend', 'Completed 50 workouts. A true legend.', 'fas fa-crown', '#F1C40F'] // Gold
];

foreach ($updates as $oldName => $data) {
    $newName = $data[0];
    $desc = $data[1];
    $icon = $data[2];
    $color = $data[3];
    
    $stmt = $conn->prepare("UPDATE gamification_badges SET name = ?, description = ?, icon_class = ?, color = ? WHERE name = ?");
    $stmt->bind_param("sssss", $newName, $desc, $icon, $color, $oldName);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "Updated badge: '$oldName' -> '$newName'<br>";
    } else {
        // If not found by old name, try updating by new name (idempotency) just to update style/desc
        $stmt2 = $conn->prepare("UPDATE gamification_badges SET description = ?, icon_class = ?, color = ? WHERE name = ?");
        $stmt2->bind_param("ssss", $desc, $icon, $color, $newName);
        $stmt2->execute();
    }
}

echo "Badges updated to FitNova style.";
?>
