<?php
require 'db_connect.php';
$res = $conn->query("SELECT * FROM users WHERE email='ashakayaplackal@gmail.com'");
print_r($res->fetch_assoc());
?>
