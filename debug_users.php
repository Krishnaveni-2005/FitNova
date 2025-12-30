<?php
require "db_connect.php";
$res = $conn->query("SELECT email, first_name, role FROM users");
if ($res) {
    while($row = $res->fetch_assoc()) {
        echo $row["email"] . " - " . $row["first_name"] . " - Role: " . ($row["role"] ?? "NOT SET") . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}
?>
