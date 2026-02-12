<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fitnova_db');

// SMTP configuration for PHPMailer
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'krishnavenirnair2028@mca.ajce.in');
define('SMTP_PASS', 'aqhastvzksloaxym');
define('SMTP_FROM_NAME', 'FitNova Support');

// Check for local config override
$localConfig = __DIR__ . '/config_local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

// Fallback / Defaults (Public Placeholders)
if (!defined('TWILIO_SID')) define('TWILIO_SID', 'YOUR_TWILIO_SID');
if (!defined('TWILIO_TOKEN')) define('TWILIO_TOKEN', 'YOUR_TWILIO_TOKEN');
if (!defined('ADMIN_WHATSAPP_NUMBER')) define('ADMIN_WHATSAPP_NUMBER', 'whatsapp:+19999999999');

if (!defined('TWILIO_WHATSAPP_FROM')) define('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886');

?>
