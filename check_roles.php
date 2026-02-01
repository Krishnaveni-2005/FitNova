<?php
require 'db_connect.php';
$result = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
while ($row = $result->fetch_assoc()) {
    echo $row['role'] . ": " . $row['count'] . "\n";
}
// Also check if there's any user with 'owner' in their name or email if no specific role exists
$result = $conn->query("SELECT * FROM users WHERE role = 'admin' OR email LIKE '%owner%' OR first_name LIKE '%owner%'");
echo "\nPotential Owners/Admins:\n";
while ($row = $result->fetch_assoc()) {
    echo $row['first_name'] . " " . $row['last_name'] . " (" . $row['role'] . ") - " . $row['email'] . "\n";
}
?>
