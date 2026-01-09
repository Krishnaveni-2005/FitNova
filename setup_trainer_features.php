<?php
require 'db_connect.php';

// 1. Add assignment_status to users if not exists
$checkCol = $conn->query("SHOW COLUMNS FROM users LIKE 'assignment_status'");
if ($checkCol->num_rows == 0) {
    if ($conn->query("ALTER TABLE users ADD COLUMN assignment_status ENUM('none', 'pending', 'approved', 'rejected') DEFAULT 'none'")) {
        echo "Added assignment_status to users.<br>";
    } else {
        echo "Error adding assignment_status: " . $conn->error . "<br>";
    }
} else {
    echo "assignment_status already exists.<br>";
}

// 2. Create trainer_ratings
$sqlRatings = "CREATE TABLE IF NOT EXISTS trainer_ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    client_id INT NOT NULL,
    rating DECIMAL(2,1) NOT NULL,
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sqlRatings)) {
    echo "Table trainer_ratings ready.<br>";
} else {
    echo "Error creating trainer_ratings: " . $conn->error . "<br>";
}

// 3. Create payments
$sqlPayments = "CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    user_id INT, 
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR'
)";
if ($conn->query($sqlPayments)) {
    echo "Table payments ready.<br>";
} else {
    echo "Error creating payments: " . $conn->error . "<br>";
}

// 4. Insert some dummy reviews/payments for "Alen" (assuming current trainer has ID or just generally) to verify dashboard
// We won't insert blindly, but the dashboard needs to handle "0".

echo "Database updates complete.";
?>
