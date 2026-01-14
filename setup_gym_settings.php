<?php
require 'db_connect.php';

// Create settings table
$sql = "CREATE TABLE IF NOT EXISTS gym_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table gym_settings created successfully<br>";
    
    // Insert defaults if not exist
    $defaults = [
        'gym_open_time' => '05:00 AM',
        'gym_close_time' => '10:00 PM',
        'gym_status' => 'open' // 'open' or 'closed'
    ];
    
    foreach ($defaults as $key => $val) {
        $check = $conn->query("SELECT setting_key FROM gym_settings WHERE setting_key = '$key'");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO gym_settings (setting_key, setting_value) VALUES ('$key', '$val')");
            echo "Inserted default for $key<br>";
        }
    }
} else {
    echo "Error creating table: " . $conn->error;
}
?>
