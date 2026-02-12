<?php
require_once 'config.php';
require_once 'db_connect.php';

$res = $conn->query("SHOW COLUMNS FROM users");
while($row = $res->fetch_assoc()){
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}
?>
