<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$receiptId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userId = $_SESSION['user_id'];

// Fetch receipt details
$sql = "SELECT pr.*, u.first_name, u.last_name, u.email, u.phone
        FROM payment_receipts pr 
        JOIN users u ON pr.user_id = u.user_id 
        WHERE pr.receipt_id = ? AND pr.user_id = ?";
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

// Format date
$paymentDate = date('F d, Y', strtotime($receipt['transaction_date']));
$paymentTime = date('h:i A', strtotime($receipt['transaction_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo htmlspecialchars($receipt['receipt_number']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            color: #1a1a1a;
            line-height: 1.5;
            padding: 20px;
        }

        .receipt-wrapper {
            max-width: 850px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.08);
        }

        /* Compact Header */
        .receipt-header {
            background: linear-gradient(135deg, #0F2C59 0%, #1a4178 100%);
            padding: 25px 40px;
            color: white;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-info {
            flex: 1;
        }

        .company-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #0F2C59;
            font-weight: 900;
        }

        .company-name {
            font-size: 24px;
            font-weight: 700;
        }

        .company-details {
            font-size: 11px;
            opacity: 0.85;
            line-height: 1.6;
        }

        .header-meta {
            text-align: right;
            font-size: 12px;
            opacity: 0.9;
        }

        .header-meta h1 {
            font-size: 20px;
            margin-bottom: 8px;
        }

        /* Main Content - Compact */
        .receipt-content {
            padding: 30px 40px;
        }

        /* Receipt Number */
        .receipt-number {
            background: #f8f9fa;
            border-left: 4px solid #0F2C59;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 0 6px 6px 0;
        }

        .receipt-number label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .receipt-number strong {
            font-size: 18px;
            color: #0F2C59;
            font-family: 'Courier New', monospace;
            letter-spacing: 1.5px;
        }

        /* Info Grid - Compact */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .info-block h3 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0F2C59;
            font-weight: 700;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .info-block p {
            font-size: 13px;
            color: #333;
            margin: 5px 0;
        }

        .info-block .status {
            display: inline-block;
            padding: 4px 10px;
            background: #d4edda;
            color: #155724;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 5px;
        }

        /* Compact Table */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .invoice-table thead {
            background: #f8f9fa;
            border-top: 2px solid #dee2e6;
            border-bottom: 2px solid #dee2e6;
        }

        .invoice-table th {
            padding: 10px 15px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
            color: #495057;
        }

        .invoice-table th:last-child {
            text-align: right;
        }

        .invoice-table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
            color: #333;
        }

        .invoice-table tbody td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .description-detail {
            font-size: 11px;
            color: #6c757d;
            margin-top: 2px;
        }

        /* Compact Totals */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin: 20px 0;
        }

        .totals-box {
            width: 350px;
            background: #f8f9fa;
            border-radius: 6px;
            overflow: hidden;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
            font-size: 13px;
            border-bottom: 1px solid #dee2e6;
        }

        .total-row:last-child {
            border-bottom: none;
        }

        .total-row.grand-total {
            background: #0F2C59;
            color: white;
            font-weight: 700;
            font-size: 16px;
            padding: 15px 20px;
        }

        .total-row.grand-total .amount {
            font-size: 20px;
        }

        /* Compact Transaction Details */
        .transaction-details {
            background: #fffbec;
            border: 1px solid #ffd666;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }

        .transaction-details h4 {
            font-size: 11px;
            color: #856404;
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .transaction-details p {
            font-size: 11px;
            color: #856404;
            margin: 5px 0;
            font-family: 'Courier New', monospace;
        }

        .transaction-details strong {
            font-family: 'Inter', sans-serif;
            margin-right: 8px;
        }

        /* Compact Footer */
        .receipt-footer {
            background: #f8f9fa;
            padding: 20px 40px;
            border-top: 2px solid #dee2e6;
            font-size: 11px;
            color: #6c757d;
            text-align: center;
        }

        .footer-note {
            line-height: 1.6;
        }

        .footer-note strong {
            color: #0F2C59;
        }

        /* Compact Action Bar */
        .action-bar {
            background: white;
            padding: 20px 40px;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: #0F2C59;
            color: white;
        }

        .btn-primary:hover {
            background: #0a1f3f;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: white;
            color: #0F2C59;
            border: 2px solid #0F2C59;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
        }

        .btn-ghost {
            background: transparent;
            color: #6c757d;
            border: 2px solid #dee2e6;
        }

        .btn-ghost:hover {
            background: #f8f9fa;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-wrapper {
                box-shadow: none;
            }
            .action-bar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .receipt-header,
            .receipt-content,
            .receipt-footer,
            .action-bar {
                padding-left: 20px;
                padding-right: 20px;
            }

            .header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header-meta {
                text-align: left;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .totals-box {
                width: 100%;
            }

            .action-bar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-wrapper">
        <!-- Compact Header -->
        <div class="receipt-header">
            <div class="header-content">
                <div class="company-info">
                    <div class="company-logo">
                        <div class="logo-icon">FN</div>
                        <div class="company-name">FitNova</div>
                    </div>
                    <div class="company-details">
                        <i class="fas fa-map-marker-alt"></i> 123 Fitness Ave, Mumbai 400001
                        &nbsp;|&nbsp; <i class="fas fa-phone"></i> +91 98765 43210
                        &nbsp;|&nbsp; <i class="fas fa-envelope"></i> support@fitnova.com
                    </div>
                </div>
                <div class="header-meta">
                    <h1>PAYMENT RECEIPT</h1>
                    <div><i class="fas fa-calendar"></i> <?php echo $paymentDate; ?></div>
                    <div><i class="fas fa-clock"></i> <?php echo $paymentTime; ?></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="receipt-content">
            <!-- Receipt Number -->
            <div class="receipt-number">
                <label>RECEIPT NUMBER</label>
                <strong><?php echo htmlspecialchars($receipt['receipt_number']); ?></strong>
            </div>

            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-block">
                    <h3><i class="fas fa-user"></i> Billed To</h3>
                    <p><strong><?php echo htmlspecialchars($receipt['first_name'] . ' ' . $receipt['last_name']); ?></strong></p>
                    <p><?php echo htmlspecialchars($receipt['email']); ?></p>
                    <?php if (!empty($receipt['phone'])): ?>
                        <p><?php echo htmlspecialchars($receipt['phone']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="info-block">
                    <h3><i class="fas fa-credit-card"></i> Payment Info</h3>
                    <p><strong>Method:</strong> Razorpay Gateway</p>
                    <p><strong>Currency:</strong> INR (Indian Rupee)</p>
                    <p class="status"><i class="fas fa-check-circle"></i> Completed</p>
                </div>
            </div>

            <!-- Invoice Table -->
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: center;">Billing</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($receipt['plan_name']); ?> Subscription</strong>
                            <div class="description-detail">Premium fitness membership with full access</div>
                        </td>
                        <td style="text-align: center; text-transform: capitalize;">
                            <?php echo htmlspecialchars($receipt['billing_cycle']); ?>
                        </td>
                        <td>₹<?php echo number_format($receipt['base_amount'], 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="totals-section">
                <div class="totals-box">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($receipt['base_amount'], 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>GST (18%)</span>
                        <span>₹<?php echo number_format($receipt['tax_amount'], 2); ?></span>
                    </div>
                    <div class="total-row grand-total">
                        <span>TOTAL PAID</span>
                        <span class="amount">₹<?php echo number_format($receipt['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Transaction Details -->
            <div class="transaction-details">
                <h4><i class="fas fa-shield-alt"></i> Transaction Reference</h4>
                <?php if (!empty($receipt['razorpay_payment_id'])): ?>
                    <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($receipt['razorpay_payment_id']); ?></p>
                <?php endif; ?>
                <?php if (!empty($receipt['razorpay_order_id'])): ?>
                    <p><strong>Order ID:</strong> <?php echo htmlspecialchars($receipt['razorpay_order_id']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Compact Footer -->
        <div class="receipt-footer">
            <div class="footer-note">
                <strong>This is an official computer-generated receipt.</strong><br>
                Thank you for choosing FitNova. Questions? Contact support@fitnova.com<br>
                <small>© 2026 FitNova. All rights reserved. | GSTIN: 27AABCU9603R1ZV</small>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-bar">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="generate_receipt_pdf.php?id=<?php echo $receiptId; ?>" class="btn btn-secondary" download>
                <i class="fas fa-download"></i> Download
            </a>
            <a href="<?php echo strtolower($_SESSION['user_role']); ?>user_dashboard.php" class="btn btn-ghost">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </div>

    <script>
        // No additional scripts needed
    </script>
</body>
</html>
