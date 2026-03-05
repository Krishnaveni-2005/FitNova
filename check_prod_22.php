<?php
require 'db_connect.php';
$id=22;
$stmt=$conn->prepare("SELECT name FROM products WHERE product_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res=$stmt->get_result();
if($res->num_rows > 0){
    $row=$res->fetch_assoc();
    echo "Product 22: " . $row['name'];
} else {
    echo "Product 22 not found.";
}
?>
