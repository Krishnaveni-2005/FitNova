<?php
require 'db_connect.php';

echo "Database Check:\n";
$res = $conn->query("SHOW TABLES LIKE 'products'");
if ($res->num_rows > 0) {
    echo "✓ Products table exists.\n";
    $count = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
    echo "Count: $count\n";
    if ($count > 0) {
        $cats = $conn->query("SELECT DISTINCT category FROM products");
        echo "Categories: ";
        while($r = $cats->fetch_assoc()) echo $r['category'] . ", ";
        echo "\n";
    }
} else {
    echo "❌ Products table MISSING!\n";
}

echo "\nPHP Syntax Check for fitshop.php:\n";
passthru('php -l fitshop.php');
?>
