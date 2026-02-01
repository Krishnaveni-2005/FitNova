<?php
require 'db_connect.php';

// Define bio templates based on specialization
$bioTemplates = [
    'Muscle Building' => 'Expert in hypertrophy training and strength development. Specializes in creating customized muscle-building programs with progressive overload techniques to help you achieve maximum gains.',
    'Yoga & Flexibility' => 'Certified yoga instructor specializing in flexibility, mindfulness, and holistic wellness. Helps clients improve mobility, reduce stress, and achieve mind-body balance through personalized yoga practices.',
    'Weight Loss' => 'Dedicated weight loss specialist focused on sustainable fat loss through balanced nutrition and effective workout strategies. Helps clients achieve their ideal body composition with science-backed methods.',
    'Cardio & Endurance' => 'Endurance training expert specializing in cardiovascular fitness and stamina building. Designs programs to improve heart health, boost energy levels, and enhance overall athletic performance.'
];

// Update bios for all trainers based on their specialization
$sql = "SELECT user_id, first_name, last_name, trainer_specialization FROM users WHERE role = 'trainer'";
$result = $conn->query($sql);

echo "Updating Trainer Bios:\n";
echo str_repeat("=", 100) . "\n\n";

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $specialization = $row['trainer_specialization'];
        $bio = $bioTemplates[$specialization] ?? 'Expert in guiding clients to achieve their personal fitness goals through customized plans.';
        
        $updateSql = "UPDATE users SET bio = ? WHERE user_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $bio, $row['user_id']);
        
        if ($stmt->execute()) {
            echo "✓ Updated bio for " . $row['first_name'] . " " . $row['last_name'] . " (" . $specialization . ")\n";
        } else {
            echo "✗ Failed to update bio for " . $row['first_name'] . " " . $row['last_name'] . "\n";
        }
        
        $stmt->close();
    }
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "Bio update complete!\n";
} else {
    echo "No trainers found.\n";
}

$conn->close();
?>
