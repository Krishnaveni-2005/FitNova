<?php
require 'db_connect.php';
$table = 'gym_equipment';
$check = $conn->query("SHOW TABLES LIKE '$table'");
if($check->num_rows > 0) {
    $result = $conn->query("DESCRIBE $table");
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Table $table does not exist.";
}
?>
