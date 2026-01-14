<?php
require 'db_connect.php';

$target_email = 'agnussabu2028@mca.ajce.in';
$current_name = 'Agnus Sabu';

// 1. Check if the specific email already exists
$checkSql = "SELECT user_id FROM users WHERE email = '$target_email'";
$checkResult = $conn->query($checkSql);

if ($checkResult->num_rows > 0) {
    echo "User with email $target_email already exists.\n";
    // Ensure password is reset if needed, but user said "no other changes allowed" generally,
    // but implies they can't login ("User not found").
    // I will just confirming existence first.
} else {
    // 2. If not, find the Agnus Sabu record we created and update it
    // We look for Agnus Sabu created recently (or any Agnus Sabu)
    $findSql = "SELECT user_id, email FROM users WHERE first_name = 'Agnus' AND last_name = 'Sabu' LIMIT 1";
    $findResult = $conn->query($findSql);

    if ($findResult->num_rows > 0) {
        $row = $findResult->fetch_assoc();
        $user_id = $row['user_id'];
        $old_email = $row['email'];

        $updateSql = "UPDATE users SET email = '$target_email' WHERE user_id = $user_id";
        if ($conn->query($updateSql) === TRUE) {
            echo "Updated email for Agnus Sabu from '$old_email' to '$target_email'.\n";
        } else {
            echo "Error updating email: " . $conn->error . "\n";
        }
    } else {
        // Create if completely missing
        $password = password_hash('password123', PASSWORD_DEFAULT);
        $insertSql = "INSERT INTO users (first_name, last_name, email, password_hash, role, account_status, phone, trainer_specialization, bio, experience_years) 
                      VALUES ('Agnus', 'Sabu', '$target_email', '$password', 'trainer', 'active', '9876543210', 'Nutrition & Wellness', 'Certified Nutrition & Wellness trainer dedicated to helping you reach your fitness goals.', 5)";
        if ($conn->query($insertSql) === TRUE) {
            echo "Created new user for $target_email.\n";
        } else {
            echo "Error creating user: " . $conn->error . "\n";
        }
    }
}

$conn->close();
?>
