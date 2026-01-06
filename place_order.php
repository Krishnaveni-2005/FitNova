<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit();
}

// 1. Prepare Order Data
$items = $data['items'];
$paymentMethod = $data['payment_method'];
$address = $data['address'];
$city = $data['city'];
$zip = $data['zip'];
$deliveryDate = $data['delivery_date'];

// Calculate Total
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += floatval($item['price']) * intval($item['quantity']);
}
$shipping = $subtotal > 2000 ? 0 : 99;
$tax = $subtotal * 0.18;
$totalAmount = $subtotal + $shipping + $tax;

// 2. Insert Order
$stmt = $conn->prepare("INSERT INTO shop_orders (user_id, total_amount, address, city, zip, payment_method, delivery_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("idsssss", $userId, $totalAmount, $address, $city, $zip, $paymentMethod, $deliveryDate);

if ($stmt->execute()) {
    $orderId = $stmt->insert_id;
    $stmt->close();

    // 3. Insert Items
    $stmtItem = $conn->prepare("INSERT INTO shop_order_items (order_id, product_id, product_name, quantity, price, size, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($items as $item) {
        $pId = isset($item['id']) ? $item['id'] : (isset($item['product_id']) ? $item['product_id'] : 0); // Handle differnt keys
        // If product_id coming from checkout page logic, careful with keys
        // shop_checkout.php items have: name, price, image, quantity, size.
        // It might NOT have product_id if it came from the bulk cart JSON unless we added it.
        // Let's ensure cart items have 'id'. Header cart js uses 'id'.
        
        // Key mapping check:
        // cart item: {id, name, price, image, size, quantity}
        // single item from checkout post: we built $items array in shop_checkout.php without ID initially?
        // Wait, in shop_checkout.php I did "$productId = $_POST['product_id']" but when building $items array locally I didn't add it.
        // I need to fix shop_checkout.php to include 'id' in the $items array.
        
        $pId = intval($item['id'] ?? 0); 
        $name = $item['name'];
        $qty = intval($item['quantity']);
        $price = floatval($item['price']);
        $size = $item['size'];
        $image = $item['image'];

        $stmtItem->bind_param("iisidss", $orderId, $pId, $name, $qty, $price, $size, $image);
        $stmtItem->execute();
    }
    $stmtItem->close();

    echo json_encode(['status' => 'success', 'message' => 'Order placed successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
}

$conn->close();
?>
