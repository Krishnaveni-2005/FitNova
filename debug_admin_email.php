<?php
require_once 'config.php';
require_once 'db_connect.php';

$sql = "SELECT user_id, email, first_name, last_name, role FROM users WHERE role = 'admin'";
$result = $conn->query($sql);

echo "<h1>List of Admins Debug</h1>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $email = $row['email'];
        echo "<p>ID: " . $row['user_id'] . "</p>";
        echo "<p>Email: '" . $email . "' (Length: " . strlen($email) . ")</p>";
        echo "<p>Expected: 'krishnavenirnair2005@gmail.com' (Length: " . strlen('krishnavenirnair2005@gmail.com') . ")</p>";
        if ($email === 'krishnavenirnair2005@gmail.com') {
            echo "<p style='color:green'>MATCH!</p>";
        } else {
            echo "<p style='color:red'>NO MATCH! Hex dump: " . bin2hex($email) . "</p>";
        }
    }
} else {
    echo "No admins found.";
}
?>
