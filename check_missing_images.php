<?php
require 'db_connect.php';

$result = $conn->query('SELECT product_id, name, category, image_url FROM products');

echo "Products without images:\n\n";
while($row = $result->fetch_assoc()) {
    if (empty($row['image_url']) || $row['image_url'] == '' || $row['image_url'] == '#') {
        echo 'ID: ' . $row['product_id'] . ' | ' . $row['name'] . ' | Category: ' . $row['category'] . "\n";
    }
}

$conn->close();
?>
