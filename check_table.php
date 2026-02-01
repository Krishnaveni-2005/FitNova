<?php
require 'db_connect.php';

echo "Checking 'payments' table structure:\n";
$result = $conn->query("DESCRIBE payments");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error checking payments: " . $conn->error . "\n";
}

echo "\nChecking 'users' table structure:\n";
$result = $conn->query("DESCRIBE users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Table 'trainer_attendance' does not exist.\n";
    echo "Error: " . $conn->error . "\n";
}
?>
