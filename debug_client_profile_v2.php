<?php
require 'db_connect.php';

$email = 'krishnavenirnair2028@mca.ajce.in';

echo "<h2>Searching for user: $email</h2>";
$sql = "SELECT * FROM users WHERE email = '$email'";
$res = $conn->query($sql);

if($res && $res->num_rows > 0) {
    $u = $res->fetch_assoc();
    echo "Found User ID: " . $u['user_id'] . "<br>";
    echo "<pre>"; print_r($u); echo "</pre>";
    $uid = $u['user_id'];
    
    // Check tables
    $tables = ['client_profiles', 'user_profiles', 'profiles'];
    foreach($tables as $tbl) {
        echo "<h3>Checking table: $tbl</h3>";
        $check = $conn->query("SHOW TABLES LIKE '$tbl'");
        if($check->num_rows > 0) {
            echo "Table $tbl EXISTS.<br>";
            // Check content
            $content = $conn->query("SELECT * FROM $tbl WHERE user_id = $uid");
            if($content && $content->num_rows > 0) {
                echo "<b>Data found in $tbl:</b><br>";
                echo "<pre>"; print_r($content->fetch_assoc()); echo "</pre>";
            } else {
                echo "No data for user_id $uid in $tbl.<br>";
            }
            // Columns
            echo "Columns in $tbl:<br>";
            $cols = $conn->query("SHOW COLUMNS FROM $tbl");
            while($c = $cols->fetch_assoc()) {
                echo $c['Field'] . " ";
            }
        } else {
            echo "Table $tbl DOES NOT EXIST.<br>";
        }
    }

} else {
    echo "User not found with email $email";
}
?>
