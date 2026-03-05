<?php
require 'db_connect.php';

$res = $conn->query("SELECT * FROM users LIMIT 1");
if ($res) {
    $user = $res->fetch_assoc();
    if ($user) {
        $userId = $user['user_id'];
        
        // Add a dummy review for product 1
        $productId = 1; 
        $rating = 5;
        $title = "Great Product!";
        $text = "Loved it. Fits perfectly.";
        $verified = 1;
        
        $sql = "INSERT INTO product_reviews (product_id, user_id, rating, review_title, review_text, verified_purchase) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iiissi", $productId, $userId, $rating, $title, $text, $verified);
            if ($stmt->execute()) {
                echo "Added review for product $productId by user $userId.\n";
            } else {
                echo "Error adding review: " . $stmt->error . "\n";
            }
            $stmt->close();
        }
    } else {
        echo "No users found to add review.\n";
    }
} else {
    echo "Error fetching users: " . $conn->error . "\n";
}
?>
