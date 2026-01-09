<?php
require 'db_connect.php';

$table = 'users';
$result = $conn->query("SHOW COLUMNS FROM $table");

echo "<h3>Columns in '$table':</h3><ul>";
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
    }
}
echo "</ul>";

// Also check if we can insert a dummy google user
$test_google_id = "test_sub_" . time();
$test_email = "test_google_" . time() . "@example.com";
$sql = "INSERT INTO users (first_name, last_name, email, auth_provider, oauth_provider_id, is_email_verified) VALUES ('Test', 'Google', '$test_email', 'google', '$test_google_id', 1)";
echo "Test Insert (Google): ";
if ($conn->query($sql) === TRUE) {
    echo "SUCCESS (Inserted ID: " . $conn->insert_id . ")";
    // Clean up
    $conn->query("DELETE FROM users WHERE email='$test_email'");
} else {
    echo "FAILED: " . $conn->error;
}
?>
