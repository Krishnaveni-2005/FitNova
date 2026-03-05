<?php
require "db_connect.php";

$query = $_GET['q'] ?? '';

if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

$searchTermStart = $query . '%';
$searchTermWord = '% ' . $query . '%';

$sql = "SELECT product_id, name, price, image_url, category, has_sizes, is_bestseller, is_sale, is_new,
        (SELECT COALESCE(AVG(rating), 0) FROM product_reviews pr WHERE pr.product_id = products.product_id) as avg_rating,
        (SELECT COUNT(*) FROM product_reviews pr WHERE pr.product_id = products.product_id) as review_count
        FROM products 
        WHERE name LIKE ? OR name LIKE ? OR category LIKE ? OR category LIKE ?
        ORDER BY is_bestseller DESC, name ASC 
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $searchTermStart, $searchTermWord, $searchTermStart, $searchTermWord);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $row['rating'] = number_format($row['avg_rating'], 1);
    $products[] = $row;
}

$stmt->close();
header('Content-Type: application/json');
echo json_encode($products);
?>
