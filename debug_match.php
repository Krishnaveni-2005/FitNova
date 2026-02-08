<?php
require 'db_connect.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "COLUMNS:\n";
$res = $conn->query("SHOW COLUMNS FROM users");
if (!$res) die($conn->error);
while($r = $res->fetch_assoc()) {
    echo $r['Field'] . "\n";
}
?>
