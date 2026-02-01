<?php
require 'db_connect.php';
$result = $conn->query("DESCRIBE client_progress");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
