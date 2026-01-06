<?php
require 'db_connect.php';

// Get all products
$result = $conn->query("SELECT product_id, name, category, image_url FROM products ORDER BY category, name");

echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#0F2C59;color:white;}</style>";
echo "<h2>All Products in Database</h2>";
echo "<table>";
echo "<tr><th>ID</th><th>Name</th><th>Category</th><th>Current Image URL</th></tr>";

while($row = $result->fetch_assoc()) {
    $hasImage = !empty($row['image_url']) && $row['image_url'] != '#' ? '✓' : '✗ MISSING';
    $imageStatus = !empty($row['image_url']) && $row['image_url'] != '#' ? 
                   "<a href='{$row['image_url']}' target='_blank'>View</a>" : 
                   "<span style='color:red'>NO IMAGE</span>";
    
    echo "<tr>";
    echo "<td>{$row['product_id']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>" . ucfirst($row['category']) . "</td>";
    echo "<td>$imageStatus</td>";
    echo "</tr>";
}

echo "</table>";
$conn->close();
?>
