<?php
require 'db_connect.php';
$table = 'users';
$result = $conn->query("DESCRIBE $table");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
