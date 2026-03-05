<?php
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['product_id'])) {
    echo json_encode([]);
    exit;
}

$product_id = intval($_GET['product_id']);

$sql = "SELECT r.rating, r.review_text, r.created_at, u.first_name, u.last_name 
        FROM product_reviews r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = [
        'user_name' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
        'rating' => intval($row['rating']),
        'text' => htmlspecialchars($row['review_text']),
        'date' => date('M j, Y', strtotime($row['created_at']))
    ];
}

echo json_encode($reviews);
?>
