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
    die("Receipt not found");
}

$receipt = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Format date
$paymentDate = date('F d, Y', strtotime($receipt['transaction_date']));
$paymentTime = date('h:i A', strtotime($receipt['transaction_date']));

// Check if TCPDF is available
if (file_exists(__DIR__ . '/tcpdf/tcpdf.php')) {
    require_once(__DIR__ . '/tcpdf/tcpdf.php');
    
    // Create PDF using TCPDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('FitNova');
    $pdf->SetAuthor('FitNova');
    $pdf->SetTitle('Payment Receipt - ' . $receipt['receipt_number']);
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Generate HTML content
    $html = generateReceiptHTML($receipt, $paymentDate, $paymentTime);
    
    // Print text using writeHTMLCell()
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Close and output PDF document
    $filename = 'Receipt_' . $receipt['receipt_number'] . '.pdf';
    $pdf->Output($filename, 'D'); // D = force download
    
} else {
    // Fallback: Create a simple text-based PDF using basic PHP
    // This creates a simple PDF without external libraries
    
    $filename = 'Receipt_' . $receipt['receipt_number'] . '.pdf';
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Create basic PDF content
    $pdfContent = generateBasicPDF($receipt, $paymentDate, $paymentTime, $filename);
    echo $pdfContent;
}

function generateReceiptHTML($receipt, $paymentDate, $paymentTime) {
    return '
<style>
    body { 
        font-family: helvetica, arial, sans-serif; 
        font-size: 10pt; 
        color: #2c3e50;
    }
    .header-bg { 
        background-color: #0F2C59; 
        color: white; 
        padding: 20px;
    }
    .company-name { 
        font-size: 24pt; 
        font-weight: bold; 
        color: white;
        margin-bottom: 3px;
    }
    .tagline {
        font-size: 9pt;
        color: #E8EAF6;
        font-style: italic;
        margin-bottom: 10px;
    }
    .header-details { 
        font-size: 8pt; 
        color: #E8EAF6;
        line-height: 1.6;
    }
    .title-section {
        margin: 15px 0 10px 0;
    }
    .receipt-title { 
        font-size: 20pt; 
        font-weight: bold; 
        color: #0F2C59; 
        border-bottom: 3px solid #0F2C59;
        padding-bottom: 5px;
        margin-bottom: 8px;
    }
    .date-info {
        font-size: 9pt;
        color: #6c757d;
        margin-bottom: 15px;
    }
    .receipt-number-box { 
        background-color: #F5F5F5;
        padding: 15px;
        margin: 15px 0;
        border-left: 5px solid #0F2C59;
    }
    .receipt-label {
        font-size: 8pt;
        color: #666;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .receipt-value {
        font-size: 15pt;
        color: #0F2C59;
        font-family: courier, monospace;
        font-weight: bold;
    }
    .info-table {
        width: 100%;
        margin: 15px 0;
    }
    .info-box {
        background-color: #F8F9FA;
        padding: 12px;
        vertical-align: top;
    }
    .info-title {
        font-size: 9pt;
        font-weight: bold;
        color: #0F2C59;
        border-bottom: 2px solid #0F2C59;
        padding-bottom: 5px;
        margin-bottom: 10px;
    }
    .info-text {
        font-size: 9pt;
        color: #2c3e50;
        margin: 5px 0;
    }
    .status-badge {
        background-color: #D4EDDA;
        color: #155724;
        padding: 4px 10px;
        border: 1px solid #C3E6CB;
        font-size: 8pt;
        font-weight: bold;
    }
    .section-header {
        font-size: 11pt;
        font-weight: bold;
        color: #0F2C59;
        margin: 20px 0 10px 0;
        padding-bottom: 5px;
        border-bottom: 2px solid #DEE2E6;
    }
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
    }
    .items-table thead {
        background-color: #0F2C59;
        color: white;
    }
    .items-table th {
        padding: 10px;
        font-size: 8pt;
        font-weight: bold;
        text-align: left;
    }
    .items-table td {
        padding: 12px 10px;
        font-size: 9pt;
        border-bottom: 1px solid #E9ECEF;
    }
    .item-name {
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 3px;
    }
    .item-desc {
        font-size: 8pt;
        color: #6c757d;
        font-style: italic;
    }
    .totals-table {
        width: 280px;
        margin: 15px 0 15px auto;
        border-collapse: collapse;
    }
    .totals-row {
        background-color: #F8F9FA;
        padding: 10px;
    }
    .totals-row td {
        padding: 10px;
        font-size: 9pt;
        border-bottom: 1px solid #DEE2E6;
    }
    .grand-total {
        background-color: #0F2C59;
        color: white;
        font-weight: bold;
        font-size: 13pt;
    }
    .grand-total td {
        padding: 12px;
        border-bottom: none;
    }
    .transaction-box {
        background-color: #FFFBEC;
        border: 2px solid #FFD666;
        padding: 15px;
        margin: 15px 0;
    }
    .transaction-title {
        font-size: 9pt;
        color: #856404;
        font-weight: bold;
        margin-bottom: 10px;
        border-bottom: 2px solid #FFD666;
        padding-bottom: 5px;
    }
    .transaction-detail {
        background-color: white;
        padding: 6px 10px;
        margin: 5px 0;
        font-size: 8pt;
        color: #856404;
        font-family: courier, monospace;
        border-left: 3px solid #FFD666;
    }
    .footer-box {
        background-color: #F8F9FA;
        padding: 15px;
        margin-top: 20px;
        border-top: 3px solid #0F2C59;
        text-align: center;
    }
    .footer-text {
        font-size: 8pt;
        color: #6c757d;
        line-height: 1.8;
    }
    .footer-bold {
        font-weight: bold;
        color: #0F2C59;
        font-size: 9pt;
    }
    .highlight {
        color: #0F2C59;
        font-weight: bold;
    }
</style>

<div class="header-bg">
    <div class="company-name">FitNova</div>
    <div class="tagline">Your Premium Fitness Partner</div>
    <div class="header-details">
        123 Fitness Avenue, Wellness District, Mumbai, MH 400001, India<br>
        Phone: +91 98765 43210 | Email: support@fitnova.com | Web: www.fitnova.com<br>
        GSTIN: 27AABCU9603R1ZV
    </div>
</div>

<div class="title-section">
    <div class="receipt-title">PAYMENT RECEIPT</div>
    <div class="date-info"><strong>Date:</strong> ' . htmlspecialchars($paymentDate) . ' at ' . htmlspecialchars($paymentTime) . '</div>
</div>

<div class="receipt-number-box">
    <div class="receipt-label">RECEIPT NUMBER</div>
    <div class="receipt-value">' . htmlspecialchars($receipt['receipt_number']) . '</div>
</div>

<table class="info-table" cellpadding="0" cellspacing="5">
    <tr>
        <td width="48%" class="info-box">
            <div class="info-title">BILLED TO</div>
            <div class="info-text"><strong class="highlight">' . htmlspecialchars($receipt['first_name'] . ' ' . $receipt['last_name']) . '</strong></div>
            <div class="info-text">' . htmlspecialchars($receipt['email']) . '</div>
            ' . (!empty($receipt['phone']) ? '<div class="info-text">' . htmlspecialchars($receipt['phone']) . '</div>' : '') . '
        </td>
        <td width="4%"></td>
        <td width="48%" class="info-box">
            <div class="info-title">PAYMENT INFORMATION</div>
            <div class="info-text"><strong>Method:</strong> R azorpay Gateway</div>
            <div class="info-text"><strong>Currency:</strong> INR (Indian Rupee)</div>
            <div class="info-text"><span class="status-badge">✓ Payment Completed</span></div>
        </td>
    </tr>
</table>

<div class="section-header">ITEMIZED CHARGES</div>

<table class="items-table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th width="50%">Description</th>
            <th width="25%" align="center">Billing Cycle</th>
            <th width="25%" align="right">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <div class="item-name">' . htmlspecialchars($receipt['plan_name']) . ' Member Subscription</div>
                <div class="item-desc">Premium fitness membership with complete access to all features</div>
            </td>
            <td align="center" style="font-weight: bold; text-transform: capitalize;">' . htmlspecialchars($receipt['billing_cycle']) . '</td>
            <td align="right" style="font-weight: bold; color: #0F2C59;">₹' . number_format($receipt['base_amount'], 2) . '</td>
        </tr>
    </tbody>
</table>

<table class="totals-table" cellpadding="0" cellspacing="0">
    <tr class="totals-row">
        <td><strong>Subtotal</strong></td>
        <td align="right">₹' . number_format($receipt['base_amount'], 2) . '</td>
    </tr>
    <tr class="totals-row">
        <td><strong>GST (18%)</strong></td>
        <td align="right">₹' . number_format($receipt['tax_amount'], 2) . '</td>
    </tr>
    <tr class="grand-total">
        <td><strong>TOTAL PAID</strong></td>
        <td align="right"><strong>₹' . number_format($receipt['total_amount'], 2) . '</strong></td>
    </tr>
</table>

<div class="transaction-box">
    <div class="transaction-title">TRANSACTION REFERENCE DETAILS</div>
    ' . (!empty($receipt['razorpay_payment_id']) ? '<div class="transaction-detail"><strong>Payment ID:</strong> ' . htmlspecialchars($receipt['razorpay_payment_id']) . '</div>' : '') . '
    ' . (!empty($receipt['razorpay_order_id']) ? '<div class="transaction-detail"><strong>Order ID:</strong> ' . htmlspecialchars($receipt['razorpay_order_id']) . '</div>' : '') . '
    <div class="transaction-detail"><strong>Gateway:</strong> Razorpay Payment Solutions</div>
</div>

<div class="footer-box">
    <div class="footer-text">
        <span class="footer-bold">This is an official computer-generated receipt and does not require a physical signature.</span><br>
        Thank you for choosing FitNova. We are committed to helping you achieve your fitness goals.<br>
        Questions or concerns? Contact us at support@fitnova.com or call +91 98765 43210<br>
        <span style="font-size: 7pt;">© 2026 FitNova Technologies Pvt. Ltd. All rights reserved. | GST Registration No: 27AABCU9603R1ZV</span>
    </div>
</div>';
}

function generateBasicPDF($receipt, $paymentDate, $paymentTime, $filename) {
    // Create a very basic PDF structure
    // This is a minimal PDF that will work without any libraries
    
    $content = "
FitNova - Payment Receipt
=========================

Receipt Number: {$receipt['receipt_number']}
Date: $paymentDate at $paymentTime

BILLED TO:
{$receipt['first_name']} {$receipt['last_name']}
{$receipt['email']}
" . (!empty($receipt['phone']) ? $receipt['phone'] : '') . "

PAYMENT INFORMATION:
Method: Razorpay Gateway
Currency: INR (Indian Rupee)
Status: Completed

CHARGES:
{$receipt['plan_name']} Subscription ({$receipt['billing_cycle']})
Amount: ₹" . number_format($receipt['base_amount'], 2) . "

Subtotal: ₹" . number_format($receipt['base_amount'], 2) . "
GST (18%): ₹" . number_format($receipt['tax_amount'], 2) . "
TOTAL PAID: ₹" . number_format($receipt['total_amount'], 2) . "

TRANSACTION REFERENCE:
" . (!empty($receipt['razorpay_payment_id']) ? "Payment ID: {$receipt['razorpay_payment_id']}\n" : '') . "
" . (!empty($receipt['razorpay_order_id']) ? "Order ID: {$receipt['razorpay_order_id']}\n" : '') . "

---
This is an official computer-generated receipt.
Thank you for choosing FitNova.
Questions? Contact support@fitnova.com
© 2026 FitNova. All rights reserved. | GSTIN: 27AABCU9603R1ZV
";

    // Create basic PDF structure
    $pdf = "%PDF-1.4\n";
    $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [0 0 612 792] /Contents 5 0 R >>\nendobj\n";
    $pdf .= "4 0 obj\n<< /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Courier >> >> >>\nendobj\n";
    
    $contentLength = strlen($content);
    $pdf .= "5 0 obj\n<< /Length $contentLength >>\nstream\n";
    $pdf .= "BT /F1 10 Tf 50 700 Td (" . str_replace("\n", ") Tj T* (", addslashes($content)) . ") Tj ET\n";
    $pdf .= "endstream\nendobj\n";
    
    $pdf .= "xref\n0 6\n";
    $pdf .= "0000000000 65535 f \n";
    $pdf .= "0000000009 00000 n \n";
    $pdf .= "0000000056 00000 n \n";
    $pdf .= "0000000115 00000 n \n";
    $pdf .= "0000000214 00000 n \n";
    $pdf .= "0000000308 00000 n \n";
    $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . strlen($pdf) . "\n%%EOF";
    
    return $pdf;
}
?>
