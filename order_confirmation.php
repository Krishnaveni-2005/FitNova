<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require 'db_connect.php';

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($orderId <= 0) {
    die("Invalid Order ID");
}

// Fetch Order
$stmt = $conn->prepare("SELECT * FROM shop_orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found or access denied.");
}

// Fetch Items
$stmt = $conn->prepare("SELECT * FROM shop_order_items WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$itemsResult = $stmt->get_result();
$items = [];
while ($row = $itemsResult->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - FitNova</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --secondary-color: #3498DB;
            --accent-color: #E63946;
            --success-color: #2ECC71;
            --bg-light: #F8F9FA;
            --text-dark: #1A1A1A;
            --text-gray: #555;
            --border-radius: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-light); color: var(--text-dark); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }

        .confirmation-card {
            background: white;
            max-width: 800px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
        }

        /* success Section */
        .success-section {
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid #f0f0f0;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2ECC71, #27ae60);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
            animation: popIn 0.5s ease;
        }

        .icon-circle i {
            color: white;
            font-size: 2.5rem;
        }

        @keyframes popIn {
            0% { transform: scale(0); }
            80% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            line-height: 1.1;
        }

        .order-number {
            font-size: 1.1rem;
            color: var(--text-gray);
            margin-bottom: 20px;
        }

        .delivery-info {
            background: #f8fbff;
            border: 1px solid #e1eaf5;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .delivery-info h4 {
            color: var(--primary-color);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-group {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        .btn-primary:hover {
            box-shadow: 0 5px 15px rgba(15, 44, 89, 0.3);
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 2px solid #ddd;
            color: var(--text-dark);
        }
        .btn-outline:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        /* Order Details Section */
        .details-section {
            background: #fafafa;
            padding: 40px;
            display: flex;
            flex-direction: column;
        }

        .details-header {
            font-family: 'Outfit', sans-serif;
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--primary-color);
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .order-items {
            flex: 1;
            overflow-y: auto;
            max-height: 300px;
            padding-right: 10px;
        }

        .order-item {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .order-item:last-child {
            border-bottom: none;
        }

        .item-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            background: white;
            border: 1px solid #eee;
        }

        .item-info {
            flex: 1;
        }
        .item-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 2px;
        }
        .item-meta {
            font-size: 0.8rem;
            color: #777;
        }

        .total-row {
            margin-top: auto;
            border-top: 1px dashed #ccc;
            padding-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total-label { font-size: 0.9rem; color: #555; }
        .total-val { font-size: 1.4rem; font-weight: 800; color: var(--primary-color); }

        @media (max-width: 768px) {
            .confirmation-card { grid-template-columns: 1fr; }
            .success-section { border-right: none; border-bottom: 1px solid #eee; }
        }
    </style>
</head>

<body>
    <div class="confirmation-card">
        <div class="success-section">
            <div class="icon-circle">
                <i class="fas fa-check"></i>
            </div>
            <h1>Order Placed!</h1>
            <p class="order-number">Order #<?php echo str_pad($orderId, 6, '0', STR_PAD_LEFT); ?></p>
            
            <p style="margin-bottom: 25px; color: #555; line-height: 1.6;">
                Thank you for shopping with FitNova. We've received your order and are getting it ready. You will receive a confirmation email shortly.
            </p>

            <div class="delivery-info">
                <h4><i class="fas fa-truck"></i> Estimated Delivery</h4>
                <p style="font-size: 1.1rem; font-weight: 600; color: #222;"><?php echo htmlspecialchars($order['delivery_date']); ?></p>
                <p style="font-size: 0.9rem; color: #666; margin-top: 5px;">
                    To: <?php echo htmlspecialchars($order['address'] . ', ' . $order['city']); ?>
                </p>
            </div>

            <div class="btn-group">
                <a href="fitshop.php" class="btn btn-primary"><i class="fas fa-shopping-bag"></i> Continue Shopping</a>
                <a href="home.php" class="btn btn-outline">Go Home</a>
            </div>
        </div>

        <div class="details-section">
            <h3 class="details-header">Order Summary</h3>
            <div class="order-items">
                <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="product" class="item-img">
                        <div class="item-info">
                            <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            <div class="item-meta">Qty: <?php echo $item['quantity']; ?> | Size: <?php echo $item['size']; ?></div>
                        </div>
                        <div style="font-weight: 600;">₹<?php echo number_format($item['price'] * $item['quantity']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="total-row">
                <span class="total-label">Total Amount Paid</span>
                <span class="total-val">₹<?php echo number_format($order['total_amount']); ?></span>
            </div>
        </div>
    </div>
</body>
</html>
