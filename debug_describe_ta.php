<?php
require 'db_connect.php';
$table = 'trainer_attendance';
$result = $conn->query("DESCRIBE $table");
if ($result) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Table does not exist or error: " . $conn->error;
}
?>
