<?php
require "db_connect.php";

$sql = "CREATE TABLE IF NOT EXISTS client_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    gender VARCHAR(20),
    dob DATE,
    height_cm DECIMAL(5,2),
    weight_kg DECIMAL(5,2),
    target_weight_kg DECIMAL(5,2),
    primary_goal VARCHAR(100),
    activity_level VARCHAR(100),
    injuries TEXT,
    medical_conditions TEXT,
    allergies TEXT,
    sleep_hours_avg INT,
    diet_preference VARCHAR(100),
    water_intake_liters DECIMAL(4,2),
    workout_days_per_week INT,
    equipment_access VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'client_profiles' created or verified successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}
$conn->close();
?>
