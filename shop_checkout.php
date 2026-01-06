<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Retrieve Data
$cartData = isset($_POST['cart_data']) ? json_decode($_POST['cart_data'], true) : null;
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$zip = $_POST['zip'] ?? '';
$deliveryDate = $_POST['delivery_date'] ?? '';

$items = [];
$subtotal = 0;

if ($cartData) {
    // Bulk Checkout Mode
    $items = $cartData;
    foreach ($items as $item) {
        $subtotal += floatval($item['price']) * intval($item['quantity']);
    }
} else {
    // Single Item Checkout Mode
    $productId = $_POST['product_id'] ?? null;
    if (!$productId) {
        echo "Invalid Request";
        exit();
    }
    $items[] = [
        'name' => $_POST['name'] ?? 'Product',
        'price' => floatval($_POST['price'] ?? 0),
        'image' => $_POST['image'] ?? '',
        'quantity' => intval($_POST['qty'] ?? 1),
        'size' => $_POST['size'] ?? 'N/A'
    ];
    $subtotal = $items[0]['price'] * $items[0]['quantity'];
}

// Calculations
$shipping = $subtotal > 2000 ? 0 : 99;
$tax = $subtotal * 0.18;
$total = $subtotal + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - FitNova</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #E63946;
            --bg-color: #F8F9FA;
            --text-color: #333333;
            --white: #FFFFFF;
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-color); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }

        .checkout-container { display: flex; gap: 40px; max-width: 1100px; width: 100%; margin: 0 auto; flex-wrap: wrap; }
        .checkout-left { flex: 1.5; background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05); min-width: 300px; }
        .checkout-right { flex: 1; min-width: 300px; }

        h2 { font-family: 'Outfit', sans-serif; color: var(--primary-color); margin-bottom: 30px; font-size: 1.8rem; }
        h3 { font-family: 'Outfit', sans-serif; margin-bottom: 20px; font-size: 1.2rem; color: #444; }

        .payment-methods { display: flex; gap: 15px; margin-bottom: 30px; }
        .payment-method { flex: 1; border: 2px solid #E0E0E0; border-radius: 10px; padding: 15px; text-align: center; cursor: pointer; transition: all 0.3s; color: #666; font-weight: 500; display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .payment-method:hover { border-color: var(--primary-color); background: rgba(15, 44, 89, 0.02); }
        .payment-method.active { border-color: var(--primary-color); background: rgba(15, 44, 89, 0.05); color: var(--primary-color); }
        .payment-method i { font-size: 1.6rem; margin-bottom: 5px; }

        .form-section { display: none; margin-bottom: 30px; }
        .form-section.active { display: block; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .form-input { width: 100%; padding: 14px; border: 1px solid #E0E0E0; border-radius: 8px; margin-bottom: 15px; font-size: 1rem; }
        .form-input:focus { border-color: var(--primary-color); outline: none; }

        .summary-card { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05); }
        .product-summary { display: flex; gap: 15px; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .product-summary:last-of-type { border-bottom: none; }
        .product-summary img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .prod-info h4 { font-family: 'Outfit', sans-serif; margin-bottom: 5px; color: var(--primary-color); }
        .prod-info p { font-size: 0.9rem; color: #777; margin-bottom: 2px; }

        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem; color: #555; }
        .summary-row.total { font-weight: 800; font-size: 1.4rem; color: var(--primary-color); border-top: 1px dashed #DDD; padding-top: 15px; margin-top: 15px; margin-bottom: 0; }

        .address-box { background: #f8f9fa; padding: 15px; border-radius: 8px; font-size: 0.9rem; color: #555; margin-bottom: 20px; border: 1px solid #eee; }
        .address-box strong { display: block; color: var(--primary-color); margin-bottom: 5px; }

        .btn-pay { width: 100%; padding: 18px; background: var(--primary-color); color: white; border: none; border-radius: 50px; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: all 0.3s; margin-top: 20px; }
        .btn-pay:hover { background: #0a1f3f; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(15, 44, 89, 0.3); }

        @media (max-width: 900px) { .checkout-container { flex-direction: column; } .checkout-right { order: -1; } }
    </style>
</head>

<body>

    <div class="checkout-container">
        <!-- Payment Details -->
        <div class="checkout-left">
            <h2>Select Payment Method</h2>
            <div class="payment-methods">
                <div class="payment-method active" onclick="selectMethod('card')">
                    <i class="fas fa-credit-card"></i> Card
                </div>
                <div class="payment-method" onclick="selectMethod('gpay')">
                    <i class="fab fa-google-pay"></i> UPI / GPay
                </div>
                <div class="payment-method" onclick="selectMethod('cod')">
                    <i class="fas fa-money-bill-wave"></i> Cash on Delivery
                </div>
            </div>

            <!-- Card Form -->
            <div id="card-form" class="form-section active">
                <h3>Card Details</h3>
                <input type="text" class="form-input" placeholder="Card Number" maxlength="19">
                <div style="display: flex; gap: 20px;">
                    <input type="text" class="form-input" placeholder="MM/YY" maxlength="5">
                    <input type="text" class="form-input" placeholder="CVV" maxlength="3">
                </div>
                <input type="text" class="form-input" placeholder="Cardholder Name">
            </div>

            <!-- UPI Form -->
            <div id="gpay-form" class="form-section">
                <h3>UPI Payment</h3>
                <input type="text" class="form-input" placeholder="Enter UPI ID (e.g. mobile@upi)">
                <p style="font-size: 0.9rem; color: #666;">A payment request will be sent to your UPI app.</p>
            </div>

            <!-- COD Form -->
            <div id="cod-form" class="form-section">
                <h3>Cash on Delivery</h3>
                <p style="font-size: 0.95rem; color: #666; line-height: 1.6;">Pay securely with cash when your order arrives. Please ensure you have the exact amount ready.</p>
            </div>

            <button type="button" class="btn-pay" id="payBtn" onclick="processPayment()">
                Pay <?php echo '₹' . number_format($total, 2); ?>
            </button>
        </div>

        <!-- Order Summary -->
        <div class="checkout-right">
            <h2 style="font-size: 1.5rem; margin-bottom: 20px;">Order Summary</h2>
            <div class="summary-card">
                <?php foreach ($items as $item): ?>
                <div class="product-summary">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Product">
                    <div class="prod-info">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p>Qty: <?php echo $item['quantity']; ?> | Size: <?php echo htmlspecialchars($item['size'] ?? 'N/A'); ?></p>
                        <p style="font-weight: 600; color: var(--primary-color);">₹<?php echo number_format($item['price']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php endforeach; ?>

                <div class="address-box">
                    <strong><i class="fas fa-map-marker-alt"></i> Delivery Address:</strong>
                    <?php echo htmlspecialchars($address . ', ' . $city . ' ' . $zip); ?><br>
                    <span style="color: #28a745; font-size: 0.85rem; margin-top: 5px; display: inline-block;">
                        <i class="fas fa-truck"></i> <?php echo htmlspecialchars($deliveryDate); ?>
                    </span>
                </div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span><?php echo $shipping == 0 ? '<span style="color:var(--accent-color)">FREE</span>' : '₹' . $shipping; ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax (18%)</span>
                    <span>₹<?php echo number_format($tax, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total To Pay</span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pass PHP data to JS -->
    <script>
        const checkoutItems = <?php echo json_encode($items); ?>;
        const deliveryDetails = {
            address: "<?php echo addslashes($address); ?>",
            city: "<?php echo addslashes($city); ?>",
            zip: "<?php echo addslashes($zip); ?>",
            delivery_date: "<?php echo addslashes($deliveryDate); ?>"
        };
    </script>

    <script>
        let selectedPaymentMethod = 'card';

        function selectMethod(method) {
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active'));
            
            // Find clicked element (handling child clicks)
            const clicked = event.target.closest('.payment-method');
            clicked.classList.add('active');
            selectedPaymentMethod = method;
            
            document.getElementById(method + '-form').classList.add('active');

            const btn = document.getElementById('payBtn');
            const amt = "<?php echo '₹' . number_format($total, 2); ?>";
            if(method === 'cod') {
                btn.innerText = "Place Order " + amt;
            } else {
                btn.innerText = "Pay " + amt;
            }
        }

        function processPayment() {
            const btn = document.getElementById('payBtn');
            const originalText = btn.innerText;
            
            // Basic validation
            if (selectedPaymentMethod === 'card') {
                // ... validate card inputs ...
            }
            // ...

            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';
            btn.style.opacity = '0.8';
            btn.disabled = true;

            const payload = {
                items: checkoutItems,
                payment_method: selectedPaymentMethod,
                ...deliveryDetails
            };

            fetch('place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Clear cart if it was a bulk order (can do this by clearing localStorage if we are confirmed it was a cart checkout, 
                    // or just always clear cart for simplicity since user likely just bought everything in their cart or bought a single item)
                    // Let's assume we clear cart to be safe/clean.
                    localStorage.removeItem('cart');
                    
                    alert('Order Placed Successfully! Your gear is on its way.');
                    window.location.href = "fitshop.php?order_success=true"; 
                } else {
                    alert('Order Failed: ' + data.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during payment.');
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.style.opacity = '1';
            });
        }
    </script>
</body>
</html>
