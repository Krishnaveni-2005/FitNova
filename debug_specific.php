<?php
// debug_specific.php
require 'db_connect.php';

echo "<h3>Debug: Titan Cargo Joggers</h3>";
$res = $conn->query("SELECT product_id, name, image_url FROM products WHERE name LIKE '%Titan%'");

if ($res->num_rows > 0) {
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Name</th><th>Image URL</th></tr>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['product_id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . htmlspecialchars($row['image_url']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No products found with 'Titan' in the name.";
}

echo "<h3>Debug: Thermal Base Layer</h3>";
$res2 = $conn->query("SELECT product_id, name, image_url FROM products WHERE name LIKE '%Thermal Base%'");
if ($res2->num_rows > 0) {
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Name</th><th>Image URL</th></tr>";
    while ($row = $res2->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['product_id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . htmlspecialchars($row['image_url']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
