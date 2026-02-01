<?php
require 'db_connect.php';
$res = $conn->query("SELECT * FROM gym_equipment");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
