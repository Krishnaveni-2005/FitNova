<?php
require 'db_connect.php';

// Add experience_years column if not exists
$checkExp = "SHOW COLUMNS FROM users LIKE 'experience_years'";
$resExp = $conn->query($checkExp);
if ($resExp->num_rows == 0) {
    if ($conn->query("ALTER TABLE users ADD COLUMN experience_years INT DEFAULT 0") === TRUE) {
        echo "Added experience_years column.<br>";
    } else {
        echo "Error adding experience_years: " . $conn->error . "<br>";
    }
}

// Add certifications column if not exists
$checkCert = "SHOW COLUMNS FROM users LIKE 'certifications'";
$resCert = $conn->query($checkCert);
if ($resCert->num_rows == 0) {
    if ($conn->query("ALTER TABLE users ADD COLUMN certifications TEXT") === TRUE) {
        echo "Added certifications column.<br>";
    } else {
        echo "Error adding certifications: " . $conn->error . "<br>";
    }
}

// Now update the values
$sqlExp = "UPDATE users SET experience_years = 5 WHERE role = 'trainer' AND (experience_years IS NULL OR experience_years = 0)";
if ($conn->query($sqlExp) === TRUE) {
    echo "Updated experience for trainers.<br>";
}

$sqlCert = "UPDATE users SET certifications = 'Certified Personal Trainer, Nutrition Specialist' WHERE role = 'trainer' AND (certifications IS NULL OR certifications = '')";
if ($conn->query($sqlCert) === TRUE) {
    echo "Updated certifications for trainers.<br>";
}

$conn->close();
?>
