<?php
require 'db_connect.php';
$sql = "SELECT phone FROM users WHERE email = 'ashakayaplackal@gmail.com'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo $result->fetch_assoc()['phone'];
} else {
    echo "No phone found";
}
?>
