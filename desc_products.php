<?php
require 'db_connect.php';

$res = $conn->query("SHOW COLUMNS FROM products");
if ($res) {
    echo "Columns in products:\n";
    while ($row = $res->fetch_array()) {
        echo $row['Field'] . "\n";
    }
}
?>
