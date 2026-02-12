<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$receiptId = isset($_GET['receipt_id']) ? intval($_GET['receipt_id']) : 0;
$userId = $_SESSION['user_id'];

// Fetch basic receipt info to show on success page
$sql = "SELECT plan_name, total_amount, receipt_number FROM payment_receipts WHERE receipt_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $receiptId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: home.php');
    exit();
}

$receipt = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #E63946;
            --success-color: #2ECC71;
            --bg-color: #F8F9FA;
            --text-color: #333333;
            --text-light: #6C757D;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-header {
            background: linear-gradient(135deg, var(--success-color) 0%, #27ae60 100%);
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .success-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .checkmark-circle {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s both;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }
            100% {
                transform: scale(1);
            }
        }

        .checkmark-circle i {
            font-size: 60px;
            color: var(--success-color);
            animation: checkPop 0.4s ease 0.6s both;
        }

        @keyframes checkPop {
            0% {
                transform: scale(0) rotate(-45deg);
            }
            50% {
                transform: scale(1.2) rotate(0deg);
            }
            100% {
                transform: scale(1) rotate(0deg);
            }
        }

        .success-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            color: white;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .success-header p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.95);
            position: relative;
            z-index: 1;
        }

        .success-body {
            padding: 50px 40px;
            text-align: center;
        }

        .payment-info {
            background: linear-gradient(135deg, #f6f8fb 0%, #e9ecf3 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 40px;
            border-left: 4px solid var(--success-color);
        }

        .payment-info h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .amount-display {
            font-size: 3rem;
            font-weight: 900;
            color: var(--primary-color);
            margin-bottom: 15px;
            font-family: 'Outfit', sans-serif;
        }

        .plan-name {
            font-size: 1.2rem;
            color: var(--text-color);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .receipt-number-box {
            background: white;
            border: 2px dashed var(--primary-color);
            border-radius: 12px;
            padding: 15px;
            margin-top: 20px;
        }

        .receipt-number-box label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-light);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .receipt-number-box strong {
            font-size: 1.2rem;
            color: var(--primary-color);
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }

        .message-box {
            background: #fff9e6;
            border-left: 4px solid #ffc107;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 40px;
            text-align: left;
        }

        .message-box h3 {
            font-size: 1.1rem;
            color: #856404;
            margin-bottom: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message-box p {
            font-size: 0.95rem;
            color: #856404;
            line-height: 1.6;
            margin: 8px 0;
        }

        .message-box ul {
            margin-top: 15px;
            padding-left: 20px;
        }

        .message-box li {
            margin: 8px 0;
            font-size: 0.95rem;
            color: #856404;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 18px 40px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .btn-receipt {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 5px 20px rgba(15, 44, 89, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-receipt::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-receipt:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-receipt:hover {
            background: #0a1f3f;
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(15, 44, 89, 0.4);
        }

        .btn-receipt i {
            font-size: 1.3rem;
            position: relative;
            z-index: 1;
        }

        .btn-receipt span {
            position: relative;
            z-index: 1;
        }

        .btn-dashboard {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-dashboard:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #f0f;
            animation: confetti-fall 3s linear infinite;
        }

        @keyframes confetti-fall {
            to {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        @media (max-width: 768px) {
            .success-header {
                padding: 50px 30px;
            }

            .success-header h1 {
                font-size: 2rem;
            }

            .success-body {
                padding: 40px 30px;
            }

            .amount-display {
                font-size: 2.5rem;
            }

            .checkmark-circle {
                width: 100px;
                height: 100px;
            }

            .checkmark-circle i {
                font-size: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <!-- Success Header -->
        <div class="success-header">
            <div class="checkmark-circle">
                <i class="fas fa-check"></i>
            </div>
            <h1>Payment Successful!</h1>
            <p>Your subscription has been activated</p>
        </div>

        <!-- Success Body -->
        <div class="success-body">
            <!-- Payment Info -->
            <div class="payment-info">
                <h2>Amount Paid</h2>
                <div class="amount-display">₹<?php echo number_format($receipt['total_amount'], 2); ?></div>
                <div class="plan-name"><?php echo htmlspecialchars($receipt['plan_name']); ?></div>
                
                <div class="receipt-number-box">
                    <label>Your Receipt Number</label>
                    <strong><?php echo htmlspecialchars($receipt['receipt_number']); ?></strong>
                </div>
            </div>

            <!-- Message Box -->
            <div class="message-box">
                <h3><i class="fas fa-info-circle"></i> What's Next?</h3>
                <p><strong>✓ Your subscription is now active!</strong></p>
                <p>You can now access all premium features included in your plan.</p>
                <ul>
                    <li>Click below to view/download your receipt</li>
                    <li>Keep your receipt number for your records</li>
                    <li>A confirmation email has been sent to your inbox</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="payment_receipt.php?id=<?php echo $receiptId; ?>" class="btn btn-receipt">
                    <i class="fas fa-file-invoice"></i>
                    <span>Click Here to View Receipt</span>
                </a>
                
                <a href="<?php echo strtolower($_SESSION['user_role']); ?>user_dashboard.php" class="btn btn-dashboard">
                    <i class="fas fa-home"></i>
                    <span>Go to Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Confetti animation
        function createConfetti() {
            const colors = ['#E63946', '#2ECC71', '#0F2C59', '#F39C12', '#9B59B6'];
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 3 + 's';
                confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                document.body.appendChild(confetti);
                
                setTimeout(() => confetti.remove(), 5000);
            }
        }

        // Trigger confetti on load
        window.addEventListener('load', () => {
            setTimeout(createConfetti, 500);
        });
    </script>
</body>
</html>
