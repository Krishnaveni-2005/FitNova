<?php
require 'db_connect.php';

$res = $conn->query("SELECT product_id FROM products LIMIT 10");
if ($res) {
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $prodId = $row['product_id'];
        
        // Add review for this product id
        $userId = 1; // Assuming user_id 1 exists (admin usually) or fetch first user.
        $uRes = $conn->query("SELECT user_id FROM users LIMIT 1");
        if ($uRes && $uRes->num_rows > 0) {
            $userId = $uRes->fetch_assoc()['user_id'];
        }

        $rating = 5;
        $title = "Sample Review";
        $text = "This is a sample review added to test the rating stars.";
        $verified = 1;
        
        $sql = "INSERT INTO product_reviews (product_id, user_id, rating, review_title, review_text, verified_purchase) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iiissi", $prodId, $userId, $rating, $title, $text, $verified);
            if ($stmt->execute()) {
                echo "Added review for product ID $prodId by user ID $userId.\n";
            } else {
                echo "Error adding review: " . $stmt->error . "\n";
            }
            $stmt->close();
        } else {
            echo "Stmt Error: " . $conn->error . "\n";
        }
    } else {
        echo "No products found.\n";
    }
} else {
    echo "Query Error: " . $conn->error . "\n";
}
?>
