<?php
require 'db_connect.php';

$names = [
    'Joshua joseph', 'Abner sam', 'David John', 'albin jojo', 'melbin james',
    'nevin benny', 'rajesh r', 'alen thomas', 'elis reji', 'reibin chacko thomas',
    'sharon shibu', 'agnus sabu'
];

echo "Checking for names in database...\n";

foreach ($names as $fullName) {
    // Split name for potential first/last check, though simple LIKE is safer for now
    $sql = "SELECT user_id, first_name, last_name, email, role, account_status FROM users WHERE CONCAT(first_name, ' ', last_name) LIKE '%" . $conn->real_escape_string($fullName) . "%' OR first_name LIKE '%" . $conn->real_escape_string($fullName) . "%'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "FOUND: " . $row['first_name'] . " " . $row['last_name'] . " | Role: " . $row['role'] . " | Status: " . $row['account_status'] . "\n";
        }
    } else {
        echo "NOT FOUND: $fullName\n";
    }
}
$conn->close();
?>
