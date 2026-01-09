<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnova_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-check and add role column if missing to prevent "Unknown column" errors
// Enable error logging but disable display to prevent breaking JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1); 
ini_set('error_log', 'php_errors.log'); 

