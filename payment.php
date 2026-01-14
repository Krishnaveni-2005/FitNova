<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - FitNova</title>
    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #E63946;
            --bg-color: #F8F9FA;
            --text-color: #333333;
            --text-light: #6C757D;
            --white: #FFFFFF;
            --border-radius: 12px;
            --input-border: #E0E0E0;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkout-container {
            display: flex;
            gap: 40px;
            max-width: 1000px;
            width: 95%;
            margin: 60px auto;
        }

        .checkout-left,
        .checkout-right {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
        }

        .checkout-left {
            flex: 1.5;
        }

        .checkout-right {
            flex: 1;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        h2 {
            font-family: 'Outfit', sans-serif;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 1.8rem;
        }

        h3 {
            font-family: 'Outfit', sans-serif;
            margin-bottom: 20px;
            font-size: 1.2rem;
            color: #444;
        }

        .payment-methods {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .payment-method {
            flex: 1;
            border: 2px solid var(--input-border);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-light);
        }

        .payment-method:hover {
            border-color: var(--primary-color);
            background: rgba(15, 44, 89, 0.02);
        }

        .payment-method.active {
            border-color: var(--primary-color);
            background: rgba(15, 44, 89, 0.05);
            color: var(--primary-color);
        }

        .payment-method i {
            font-size: 1.6rem;
            margin-bottom: 5px;
        }

        .payment-method img {
            width: 40px;
        }

        .form-section {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .form-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #555;
        }

        .form-input {
            width: 100%;
            padding: 14px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background-color: #FAFAFA;
        }

        .form-input:focus {
            border-color: var(--primary-color);
            outline: none;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(15, 44, 89, 0.1);
        }

        .row-half {
            display: flex;
            gap: 20px;
        }

        .row-half .form-group {
            flex: 1;
        }

        .summary-card {
            background: #FAFAFA;
            border: 1px solid #EEE;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
            color: #555;
        }

        .summary-row.total {
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--primary-color);
            border-top: 1px dashed #DDD;
            padding-top: 15px;
            margin-top: 15px;
            margin-bottom: 0;
        }

        .btn-pay {
            width: 100%;
            padding: 18px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(15, 44, 89, 0.2);
        }

        .btn-pay:hover {
            background: #0a1f3f;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 44, 89, 0.3);
        }

        .secure-badge {
            text-align: center;
            margin-top: 20px;
            color: #777;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        @media (max-width: 900px) {
            .checkout-container {
                flex-direction: column;
            }

            .checkout-right {
                order: -1;
            }
        }
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
                    <i class="fab fa-google-pay" style="font-size: 2rem;"></i> Google Pay / UPI
                </div>
            </div>

            <!-- Card Form -->
            <div id="card-form" class="form-section active">
                <h3>Credit or Debit Card</h3>
                <form id="cardPaymentForm">
                    <div class="form-group">
                        <label class="form-label">Name on Card</label>
                        <input type="text" class="form-input" placeholder="e.g. Rahul Kumar" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Card Number</label>
                        <input type="text" class="form-input" placeholder="0000 0000 0000 0000" maxlength="19" required>
                    </div>
                </form>
            </div>

            <!-- UPI/Gpay Form -->
            <div id="gpay-form" class="form-section">
                <h3>Pay via UPI</h3>
                <p style="margin-bottom: 20px; color: #666; font-size: 0.95rem;">Enter your VPA (Virtual Payment
                    Address) to verify and pay.</p>
                <form id="upiPaymentForm">
                    <div class="form-group">
                        <label class="form-label">UPI ID</label>
                        <div style="position: relative;">
                            <input type="text" class="form-input" placeholder="username@oksbi" required>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/f/f2/Google_Pay_Logo.svg"
                                alt="GPay"
                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); height: 20px; opacity: 0.7;">
                        </div>
                    </div>
                </form>
            </div>

            <button type="button" class="btn-pay" id="payBtn">Pay ₹2499.00</button>
            <div class="secure-badge">
                <i class="fas fa-lock"></i> 100% Secure Transaction
            </div>
        </div>

        <!-- Order Summary -->
        <div class="checkout-right">
            <h2>Order Summary</h2>
            <div class="summary-card">
                <div class="summary-row">
                    <span id="planName">Pro Member</span>
                    <span id="planPrice">₹2,499.00</span>
                </div>
                <div class="summary-row">
                    <span id="billingCycle">Monthly</span>
                </div>
                <div class="summary-row">
                    <span>GST (18%)</span>
                    <span id="taxAmount">₹449.82</span>
                </div>
                <div class="summary-row total">
                    <span>Total Due</span>
                    <span id="totalPrice">₹2,948.82</span>
                </div>
            </div>

            <div style="font-size: 0.85rem; color: #777; line-height: 1.5;">
                <ul style="padding-left: 20px; margin-bottom: 20px;">
                    <li>Immediate access to all features</li>
                    <li>Cancel anytime from dashboard</li>
                    <li>Receipt sent to your email</li>
                </ul>
            </div>

            <a href="subscription_plans.php"
                style="display: block; text-align: center; color: var(--accent-color); text-decoration: none; font-weight: 600; font-size: 0.9rem;">Change
                Plan</a>
        </div>
    </div>

    <script>
        // Parse URL params
        const urlParams = new URLSearchParams(window.location.search);
        let plan = (urlParams.get('plan') || '').toLowerCase(); // Normalizing to lowercase
        const cycle = urlParams.get('billing') || 'monthly';
        
        // Setup Data (INR)
        const plans = {
            pro: { name: 'Pro Member', monthly: 2499, yearly: 7999 },
            elite: { name: 'Elite Member', monthly: 4999, yearly: 8999 }
        };

        // Validate Plan
        if (!plan || !plans[plan]) {
             // Default to 'pro' if invalid or missing, OR redirect. 
             // To be safe and avoid crash, let's default to 'pro' if invalid, 
             // or redirect if that's preferred. 
             // Given the user wants it to work, redirecting to options is safer UX.
             window.location.href = 'subscription_plans.php';
             // return; // Stop execution (though href redirect will handle it)
        }

        const selectedPlan = plans[plan];
        // Ensure selectedPlan exists before accessing properties
        if (selectedPlan) {
            const basePrice = cycle === 'yearly' ? selectedPlan.yearly : selectedPlan.monthly;
            const tax = basePrice * 0.18;
            const total = basePrice + tax;
            
            // Formatting currency
            const formatINR = (amt) => '₹' + amt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
            // Render Data
            document.getElementById('planName').innerText = selectedPlan.name;
            document.getElementById('billingCycle').innerText = cycle.charAt(0).toUpperCase() + cycle.slice(1) + ' Plan';
            document.getElementById('planPrice').innerText = formatINR(basePrice);
            document.getElementById('taxAmount').innerText = formatINR(tax);
            document.getElementById('totalPrice').innerText = formatINR(total);
            document.getElementById('payBtn').innerText = 'Pay ' + formatINR(total);
        }



        // Toggle Payment Method
        function selectMethod(method) {
            // UI Toggle
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
            if (method === 'card') document.querySelector('.payment-method:nth-child(1)').classList.add('active');
            else document.querySelector('.payment-method:nth-child(2)').classList.add('active');

            // Form Toggle
            document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active'));
            document.getElementById(method + '-form').classList.add('active');
        }

        // Handle Payment
        document.getElementById('payBtn').addEventListener('click', function () {
            const btn = this;
            const originalText = btn.innerText;

            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';
            btn.style.opacity = '0.8';
            btn.disabled = true;

            // Send actual update to backend
            fetch('payment_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    plan: plan,
                    billing: cycle,
                    trainer_id: urlParams.get('trainer_id')
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        btn.innerHTML = '<i class="fas fa-check-circle"></i> Payment Successful';
                        btn.style.backgroundColor = '#2ECC71';

                        setTimeout(() => {
                            alert('Payment Successful! Welcome to ' + selectedPlan.name);
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        alert('Error: ' + data.message);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        btn.style.opacity = '1';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during payment processing.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.style.opacity = '1';
                });
        });
    </script>
</body>

</html>
