<?php
require 'db_connect.php';

// Version 2 of seeding script to avoid caching issues
// Clearing old cache if any...
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

$trainers = [
    ['Joshua', 'Joseph', 'Strength & Conditioning'],
    ['Abner', 'Sam', 'HIIT Specialist'],
    ['David', 'John', 'Bodybuilding Expert'],
    ['Albin', 'Jojo', 'Yoga & Flexibility'],
    ['Melbin', 'James', 'Cardio & Endurance'],
    ['Nevin', 'Benny', 'CrossFit Coach'],
    ['Rajesh', 'R', 'Martial Arts'],
    ['Alen', 'Thomas', 'Weight Loss Specialist'],
    ['Elis', 'Reji', 'Pilates Instructor'],
    ['Reibin Chacko', 'Thomas', 'Functional Training'],
    ['Sharon', 'Shibu', 'Zumba & Dance'],
    ['Agnus', 'Sabu', 'Nutrition & Wellness']
];

$password = password_hash('password123', PASSWORD_DEFAULT); 

echo "<div style='font-family:sans-serif; padding:20px;'>";
echo "<h2>Seeding Real Trainers (V2)...</h2>";

foreach ($trainers as $t) {
    $fname = $t[0];
    $lname = $t[1];
    $spec = $t[2];
    
    // Create unique email
    $email = strtolower(str_replace(' ', '', $fname) . "." . str_replace(' ', '', $lname)) . "@fitnova.com";
    
    // Check if exists
    $check_sql = "SELECT user_id FROM users WHERE email = '$email'";
    $check = $conn->query($check_sql);
    
    if ($check && $check->num_rows == 0) {
        // Explicitly using password_hash column based on schema check
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash, role, account_status, phone, trainer_specialization, bio, experience_years) 
                VALUES ('$fname', '$lname', '$email', '$password', 'trainer', 'active', '9876543210', '$spec', 'Certified $spec trainer dedicated to helping you reach your fitness goals.', 5)";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green;'>Created: <strong>$fname $lname</strong> ($spec)</p>";
        } else {
            echo "<p style='color:red;'>Error creating $fname: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:orange;'>Skipped: $fname $lname (Email already exists)</p>";
    }
}

echo "<br><h3>Done! Refresh your homepage or trainers page.</h3>";
echo "</div>";

$conn->close();
?>
