<?php
require_once 'config.php';
require_once 'twilio_helper.php';

// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Twilio Sandbox Connection Test</h1>";
echo "<p>Admin Number: " . ADMIN_WHATSAPP_NUMBER . "</p>";

$message = "Test message from FitNova! If you see this, the connection is working.";

$result = sendWhatsAppNotification($message);

if ($result) {
    echo "<h2 style='color:green'>Message Queued Successfully!</h2>";
    echo "<p>Please check your WhatsApp now.</p>";
} else {
    echo "<h2 style='color:red'>Message Failed!</h2>";
    echo "<p>Check php_errors.log for details.</p>";
}
?>
