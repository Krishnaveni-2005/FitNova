<?php
ob_start(); // Buffer any PHP warnings/notices so they don't break JSON
session_start();
require "db_connect.php";

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Auto-create wishlist table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, product_id)
)");

ob_end_clean(); // Clear any PHP warnings before outputting JSON
header('Content-Type: application/json');

if ($action === 'toggle') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }

    $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    if (!$check) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
        exit;
    }
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $del->bind_param("ii", $user_id, $product_id);
        $del->execute();
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from favourites']);
    } else {
        $ins = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        if (!$ins) {
            echo json_encode(['success' => false, 'message' => 'Insert error: ' . $conn->error]);
            exit;
        }
        $ins->bind_param("ii", $user_id, $product_id);
        if ($ins->execute()) {
            echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to favourites!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $ins->error]);
        }
    }

} elseif ($action === 'get_ids') {
    $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $ids = [];
    while ($row = $res->fetch_assoc()) {
        $ids[] = (int)$row['product_id'];
    }
    echo json_encode(['success' => true, 'ids' => $ids]);

} elseif ($action === 'get_all') {
    $stmt = $conn->prepare("
        SELECT p.product_id, p.name, p.price, p.image_url, p.category,
               p.has_sizes, p.is_bestseller, p.is_sale, p.is_new,
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(*) as review_count,
               w.added_at
        FROM wishlist w
        JOIN products p ON w.product_id = p.product_id
        LEFT JOIN product_reviews r ON r.product_id = p.product_id
        WHERE w.user_id = ?
        GROUP BY p.product_id, p.name, p.price, p.image_url, p.category,
                 p.has_sizes, p.is_bestseller, p.is_sale, p.is_new, w.added_at
        ORDER BY w.added_at DESC
    ");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Query error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $products = [];
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode(['success' => true, 'products' => $products]);

} else {
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
?>
