<?php
// check_names.php
require 'db_connect.php';

echo "<h3>Current Product Names in DB</h3>";
$cats = ['men', 'women', 'supplements'];

foreach ($cats as $cat) {
    echo "<h4>Category: $cat</h4><ul>";
    $res = $conn->query("SELECT product_id, name, image_url FROM products WHERE category = '$cat'");
    while ($row = $res->fetch_assoc()) {
        // Show ID, Name (in brackets to see whitespace), and current Image
        echo "<li>ID: " . $row['product_id'] . " | Name: [" . $row['name'] . "] | Img: " . substr($row['image_url'], 0, 30) . "...</li>";
    }
    echo "</ul>";
}
?>
