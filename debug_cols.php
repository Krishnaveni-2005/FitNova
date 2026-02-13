<?php
require 'db_connect.php';
$cols = $conn->query("SHOW COLUMNS FROM products");
echo "Columns in products table:\n";
while ($row = $cols->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
