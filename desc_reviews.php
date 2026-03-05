<?php
require 'db_connect.php';

$res = $conn->query("SHOW COLUMNS FROM product_reviews");
if ($res) {
    echo "Columns in product_reviews:\n";
    while ($row = $res->fetch_array()) {
        echo $row['Field'] . "\n";
    }
}
?>
