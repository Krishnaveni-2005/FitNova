<?php
// diagnose_whatsapp.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'twilio_helper.php';

echo "<h1>WhatsApp Diagnostic Tool</h1>";

// 1. Check Configuration
echo "<h2>1. Configuration Check</h2>";
if (defined('TWILIO_SID') && defined('TWILIO_TOKEN') && defined('ADMIN_WHATSAPP_NUMBER')) {
    echo "<p style='color:green'>✅ Constants Defined</p>";
    echo "<ul>";
    echo "<li>SID: " . substr(TWILIO_SID, 0, 5) . "..." . substr(TWILIO_SID, -5) . "</li>";
    echo "<li>Token: " . substr(TWILIO_TOKEN, 0, 5) . "..." . substr(TWILIO_TOKEN, -5) . "</li>";
    echo "<li>Admin Number: " . ADMIN_WHATSAPP_NUMBER . "</li>";
    echo "<li>From Number: " . (defined('TWILIO_WHATSAPP_FROM') ? TWILIO_WHATSAPP_FROM : 'Default') . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color:red'>❌ Missing Constants in config.php / config_local.php</p>";
    die();
}

// 2. Test Connection
echo "<h2>2. Sending Test Message</h2>";
$message = "Test/Debug from FitNova Diagnostic: " . date('Y-m-d H:i:s');
$sid = TWILIO_SID;
$token = TWILIO_TOKEN;
$from = defined('TWILIO_WHATSAPP_FROM') ? TWILIO_WHATSAPP_FROM : 'whatsapp:+14155238886';
$to = ADMIN_WHATSAPP_NUMBER;

$url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
$data = ['From' => $from, 'To' => $to, 'Body' => $message];
$post = http_build_query($data);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Status:</strong> $httpCode</p>";

if ($error) {
    echo "<p style='color:red'>❌ CURL Error: $error</p>";
} else {
    $json = json_decode($response, true);
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "<p style='color:green'>✅ Request Accepted by Twilio</p>";
        echo "<pre>" . print_r($json, true) . "</pre>";
        echo "<p><strong>Message SID:</strong> " . ($json['sid'] ?? 'N/A') . "</p>";
        echo "<p><strong>Status:</strong> " . ($json['status'] ?? 'N/A') . "</p>";
    } else {
        echo "<p style='color:red'>❌ Twilio API Error</p>";
        echo "<pre>" . print_r($json, true) . "</pre>";
        if (isset($json['code'])) {
            echo "<p><strong>Error Code:</strong> " . $json['code'] . "</p>";
            echo "<p><strong>More Info:</strong> <a href='" . $json['more_info'] . "' target='_blank'>Twilio Docs</a></p>";
        }
    }
}
?>
