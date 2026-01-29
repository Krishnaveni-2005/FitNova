<?php
require 'db_connect.php';
$conn->query("ALTER TABLE client_profiles ADD COLUMN custom_macros_json TEXT DEFAULT NULL");
echo "Update complete";
$conn->close();
?>
