<?php
require 'db_connect.php';

echo "<h2>Fixing Users Schema...</h2>";

// Add profile_picture
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
if ($check->num_rows == 0) {
    echo "Adding profile_picture... ";
    $conn->query("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT 'default_avatar.png'");
    echo "Done.<br>";
}

// Add bio
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'bio'");
if ($check->num_rows == 0) {
    echo "Adding bio... ";
    $conn->query("ALTER TABLE users ADD COLUMN bio TEXT");
    echo "Done.<br>";
}

// Create gym_equipment table if not exists (for gym admin dashboard)
$conn->query("CREATE TABLE IF NOT EXISTS gym_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    total_units INT DEFAULT 1,
    available_units INT DEFAULT 1,
    status ENUM('available', 'maintenance', 'unavailable') DEFAULT 'available',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Populate gym_equipment if empty
$res = $conn->query("SELECT COUNT(*) as count FROM gym_equipment");
$row = $res->fetch_assoc();
if ($row['count'] == 0) {
    echo "Populating gym_equipment... ";
    $sql = "INSERT INTO gym_equipment (name, total_units, available_units, status) VALUES 
    ('Treadmill', 10, 8, 'available'),
    ('Elliptical', 5, 5, 'available'),
    ('Bench Press', 4, 4, 'available'),
    ('Squat Rack', 3, 2, 'maintenance'),
    ('Dumbbell Set', 20, 20, 'available'),
    ('Cable Machine', 2, 2, 'available'),
    ('Rowing Machine', 4, 3, 'available')";
    $conn->query($sql);
    echo "Done.<br>";
}

// Create gym_settings table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS gym_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value VARCHAR(255)
)");

// Populate gym_settings
$res = $conn->query("SELECT count(*) as c FROM gym_settings");
if ($res->fetch_assoc()['c'] == 0) {
    $conn->query("INSERT INTO gym_settings (setting_key, setting_value) VALUES ('gym_open_time', '06:00 AM'), ('gym_close_time', '10:00 PM'), ('gym_status', 'open')");
}

// Create trainer_attendance table if not exists (for gym admin dashboard)
$conn->query("CREATE TABLE IF NOT EXISTS trainer_attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    check_in_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    check_out_time DATETIME,
    status ENUM('checked_in', 'checked_out') DEFAULT 'checked_in',
    FOREIGN KEY (trainer_id) REFERENCES users(user_id)
)");

// Create gym_check_ins table
$conn->query("CREATE TABLE IF NOT EXISTS gym_check_ins (
    checkin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    check_in_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)");

echo "<h3>Schema Updates Complete.</h3>";
?>
