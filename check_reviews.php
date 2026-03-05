<?php
require 'db_connect.php';

$res = $conn->query("SELECT * FROM product_reviews LIMIT 5");
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No reviews found.";
}
?>
