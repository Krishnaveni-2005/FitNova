<?php
require 'db_connect.php';

echo "<h3>Tables:</h3>";
$tables = $conn->query("SHOW TABLES");
while($t = $tables->fetch_array()) {
    echo $t[0] . "<br>";
}

echo "<h3>Describe USERS:</h3>";
$desc = $conn->query("DESCRIBE users");
while($row = $desc->fetch_assoc()) {
    echo $row['Field'] . " | ";
}

echo "<h3>Describe client_profiles (if exists):</h3>";
$desc = $conn->query("DESCRIBE client_profiles");
if($desc) {
    while($row = $desc->fetch_assoc()) {
        echo $row['Field'] . " | ";
    }
} else {
    echo "client_profiles does not exist.";
}

echo "<h3>Describe user_profiles (if exists):</h3>";
$desc = $conn->query("DESCRIBE user_profiles");
if($desc) {
    while($row = $desc->fetch_assoc()) {
        echo $row['Field'] . " | ";
    }
} else {
    echo "user_profiles does not exist.";
}

echo "<h3>User Data (Krishnaveni):</h3>";
$user = $conn->query("SELECT * FROM users WHERE first_name LIKE 'KRISH%' OR last_name LIKE 'KRISH%'");
if($user && $user->num_rows > 0) {
    $u = $user->fetch_assoc();
    echo "<pre>";
    print_r($u);
    echo "</pre>";

    $uid = $u['user_id'];
    
    // Check if profile exists in user_profiles
    echo "<h4>Profile Data (user_profiles):</h4>";
    $p = $conn->query("SELECT * FROM user_profiles WHERE user_id = $uid");
    if($p && $p->num_rows > 0) {
        print_r($p->fetch_assoc());
    } else {
        echo "No data in user_profiles";
    }

    // Check if profile exists in client_profiles
    echo "<h4>Profile Data (client_profiles):</h4>";
    $cp = $conn->query("SELECT * FROM client_profiles WHERE user_id = $uid");
    if($cp && $cp->num_rows > 0) {
        print_r($cp->fetch_assoc());
    } else {
        echo "No data in client_profiles";
    }

} else {
    echo "User not found.";
}
?>
