<?php
require 'db_connect.php';

$res = $conn->query("SHOW TABLES");
if ($res) {
    echo "Tables:\n";
    while ($row = $res->fetch_array()) {
        echo $row[0] . "\n";
    }
}
?>
