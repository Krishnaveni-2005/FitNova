<?php
session_start();
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'fitnova';
$db_username = 'root';
$db_password = '';

// Admin credentials (you can also store these in database)
define('ADMIN_EMAIL', 'krishnavenirnair2005@gmail.com');
define('ADMIN_USERNAME', 'Krishnaraj R Nair');
define('ADMIN_PASSWORD', 'Ambadi@2005');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid request format');
    }
    
    $email = trim($input['email'] ?? '');
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit;
    }
    
    // Verify credentials
    if ($email === ADMIN_EMAIL && 
        $username === ADMIN_USERNAME && 
        $password === ADMIN_PASSWORD) {
        
        // Create database connection
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Log the admin login
        $stmt = $conn->prepare("INSERT INTO admin_login_logs (email, username, login_time, ip_address) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$email, $username, $_SERVER['REMOTE_ADDR']]);
        
        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_login_time'] = time();
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => 'admin_dashboard.php'
        ]);
        
    } else {
        // Log failed attempt
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("INSERT INTO admin_failed_logins (email, username, attempt_time, ip_address) VALUES (?, ?, NOW(), ?)");
            $stmt->execute([$email, $username, $_SERVER['REMOTE_ADDR']]);
        } catch (Exception $e) {
            // Silently fail if logging doesn't work
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials. Please check your email, username, and password.'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
