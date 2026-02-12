<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch all receipts for this user
$sql = "SELECT * FROM payment_receipts WHERE user_id = ? ORDER BY transaction_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$receipts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - FitNova</title>
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
            --white: #FFFFFF;
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
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .back-btn {
            padding: 12px 24px;
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .receipts-grid {
            display: grid;
            gap: 20px;
        }

        .receipt-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 30px;
            align-items: center;
        }

        .receipt-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .receipt-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-light);
            font-weight: 600;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .info-value.highlight {
            color: var(--primary-color);
            font-size: 1.3rem;
        }

        .receipt-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            border: none;
        }

        .btn-view {
            background: var(--primary-color);
            color: white;
        }

        .btn-view:hover {
            background: #0a1f3f;
            transform: translateX(4px);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .empty-state h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--text-light);
            margin-bottom: 30px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            background: #d4edda;
            color: #155724;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }

            .receipt-card {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .receipt-info {
                grid-template-columns: 1fr;
            }

            .receipt-actions {
                flex-direction: row;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-file-invoice"></i> Payment History</h1>
            <a href="<?php echo strtolower($_SESSION['user_role']); ?>user_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="receipts-grid">
            <?php if (empty($receipts)): ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <h2>No Payment History</h2>
                    <p>You haven't made any payments yet.</p>
                    <a href="subscription_plans.php" class="btn btn-view">
                        <i class="fas fa-crown"></i> View Plans
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($receipts as $receipt): ?>
                    <div class="receipt-card">
                        <div class="receipt-info">
                            <div class="info-item">
                                <span class="info-label">Receipt Number</span>
                                <span class="info-value" style="font-family: 'Courier New', monospace;">
                                    <?php echo htmlspecialchars($receipt['receipt_number']); ?>
                                </span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Plan</span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($receipt['plan_name']); ?>
                                </span>
                                <span style="font-size: 0.85rem; color: var(--text-light);">
                                    <?php echo ucfirst($receipt['billing_cycle']); ?> Billing
                                </span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Date</span>
                                <span class="info-value">
                                    <?php echo date('M d, Y', strtotime($receipt['transaction_date'])); ?>
                                </span>
                                <span style="font-size: 0.85rem; color: var(--text-light);">
                                    <?php echo date('h:i A', strtotime($receipt['transaction_date'])); ?>
                                </span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Amount Paid</span>
                                <span class="info-value highlight">
                                    ₹<?php echo number_format($receipt['total_amount'], 2); ?>
                                </span>
                                <span class="status-badge">
                                    <i class="fas fa-check-circle"></i> Completed
                                </span>
                            </div>
                        </div>

                        <div class="receipt-actions">
                            <a href="payment_receipt.php?id=<?php echo $receipt['receipt_id']; ?>" class="btn btn-view">
                                <i class="fas fa-eye"></i> View Receipt
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
