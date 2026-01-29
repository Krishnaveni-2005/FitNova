<?php
require 'db_connect.php';

echo "<h2>Fixing Database Schema...</h2>";

// 1. Fix trainer_diet_plans
$check = $conn->query("SHOW COLUMNS FROM trainer_diet_plans LIKE 'user_id'");
if ($check->num_rows == 0) {
    echo "Adding user_id to trainer_diet_plans... ";
    if ($conn->query("ALTER TABLE trainer_diet_plans ADD COLUMN user_id INT AFTER trainer_id")) {
        echo "<span style='color:green'>Done.</span><br>";
        // Attempt to add FK, ignore failure if constraint exists
        try {
            $conn->query("ALTER TABLE trainer_diet_plans ADD CONSTRAINT fk_diet_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE");
        } catch (Exception $e) {}
    } else {
        echo "<span style='color:red'>Error: " . $conn->error . "</span><br>";
    }
} else {
    echo "user_id already exists in trainer_diet_plans.<br>";
}

// 2. Fix trainer_workouts
$check = $conn->query("SHOW COLUMNS FROM trainer_workouts LIKE 'user_id'");
if ($check->num_rows == 0) {
    echo "Adding user_id to trainer_workouts... ";
    if ($conn->query("ALTER TABLE trainer_workouts ADD COLUMN user_id INT AFTER trainer_id")) {
        echo "<span style='color:green'>Done.</span><br>";
        try {
            $conn->query("ALTER TABLE trainer_workouts ADD CONSTRAINT fk_workout_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE");
        } catch (Exception $e) {}
    } else {
        echo "<span style='color:red'>Error: " . $conn->error . "</span><br>";
    }
} else {
    echo "user_id already exists in trainer_workouts.<br>";
}

// 3. Fix session_requests table if missing (referenced in trainer_dashboard)
$checkTable = $conn->query("SHOW TABLES LIKE 'session_requests'");
if ($checkTable->num_rows == 0) {
    echo "Creating session_requests table... ";
    $sql = "CREATE TABLE session_requests (
        request_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        trainer_id INT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (trainer_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "<span style='color:green'>Done.</span><br>";
    } else {
        echo "<span style='color:red'>Error: " . $conn->error . "</span><br>";
    }
}

// 4. Fix payments table if missing (referenced in trainer_dashboard)
$checkTable = $conn->query("SHOW TABLES LIKE 'payments'");
if ($checkTable->num_rows == 0) {
    echo "Creating payments table... ";
    $sql = "CREATE TABLE payments (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_id INT NOT NULL,
        user_id INT, 
        amount DECIMAL(10,2) NOT NULL,
        payment_date DATE NOT NULL,
        currency VARCHAR(3) DEFAULT 'INR'
    )";
    if ($conn->query($sql)) {
         echo "<span style='color:green'>Done.</span><br>";
    }
}

echo "<h3>Schema Fixes Complete.</h3>";
?>
