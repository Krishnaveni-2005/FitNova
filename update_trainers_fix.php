<?php
require 'db_connect.php';

// Update trainers with missing experience
$sqlExp = "UPDATE users SET experience_years = 5 WHERE role = 'trainer' AND (experience_years IS NULL OR experience_years = 0)";
if ($conn->query($sqlExp) === TRUE) {
    echo "Updated experience for trainers.<br>";
} else {
    echo "Error updating experience: " . $conn->error . "<br>";
}

// Update trainers with missing certifications
$sqlCert = "UPDATE users SET certifications = 'Certified Personal Trainer' WHERE role = 'trainer' AND (certifications IS NULL OR certifications = '')";
if ($conn->query($sqlCert) === TRUE) {
    echo "Updated certifications for trainers.<br>";
} else {
    echo "Error updating certifications: " . $conn->error . "<br>";
}

$conn->close();
?>
