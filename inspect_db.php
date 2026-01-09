<?php
require 'db_connect.php';

function describeTable($conn, $tableName) {
    echo "Structure for table '$tableName':\n";
    $result = $conn->query("DESCRIBE $tableName");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
    } else {
        echo "Table '$tableName' does not exist.\n";
    }
    echo "\n";
}

$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

echo "All Tables: " . implode(", ", $tables) . "\n\n";

describeTable($conn, 'users');
describeTable($conn, 'trainer_ratings'); // checking if exists
describeTable($conn, 'payments'); // checking if exists
describeTable($conn, 'client_trainer_requests'); // checking if exists
?>
