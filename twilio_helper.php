<?php
require_once 'config.php';

function sendWhatsAppNotification($message, $recipient = null) {
    if (!defined('TWILIO_SID') || !defined('TWILIO_TOKEN')) {
        error_log("Twilio configuration missing.");
        return false;
    }

    $sid = TWILIO_SID;
    $token = TWILIO_TOKEN;
    $from = defined('TWILIO_WHATSAPP_FROM') ? TWILIO_WHATSAPP_FROM : 'whatsapp:+14155238886';
    // Use recipient if provided, otherwise default to Admin
    $to = $recipient ? $recipient : (defined('ADMIN_WHATSAPP_NUMBER') ? ADMIN_WHATSAPP_NUMBER : '');
    
    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    
    $data = [
        'From' => $from,
        'To' => $to,
        'Body' => $message
    ];
    
    $post = http_build_query($data);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev without proper certs
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        error_log("Twilio Curl Error: " . $error);
        return false;
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    } else {
        error_log("Twilio API Error: " . $response);
        return false;
    }
}
?>
