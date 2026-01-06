<?php
require 'db_connect.php';

// Update products with placeholder or missing images with proper stock photos from Unsplash
$imageUpdates = [
    // Men's Performance Wear
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=500&h=500&fit=crop' WHERE name LIKE '%Performance Hoodie%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?w=500&h=500&fit=crop' WHERE name LIKE '%Gym Joggers%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=500&h=500&fit=crop' WHERE name LIKE '%Training Shorts%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=500&h=500&fit=crop' WHERE name LIKE '%Tank Top%' AND category = 'men'",
    
    // Women's Performance Wear
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=500&h=500&fit=crop' WHERE name LIKE '%Compression Leggings%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=500&h=500&fit=crop' WHERE name LIKE '%Sports Bra%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=500&h=500&fit=crop' WHERE name LIKE '%Yoga Pants%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=500&h=500&fit=crop' WHERE name LIKE '%Fitness Top%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?w=500&h=500&fit=crop' WHERE name LIKE '%Tank Top%' AND category = 'women'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=500&h=500&fit=crop' WHERE name LIKE '%Crop Top%'",
    
    // Supplements
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?w=500&h=500&fit=crop' WHERE name LIKE '%Protein Powder%' OR name LIKE '%Whey%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=500&h=500&fit=crop' WHERE name LIKE '%Pre-Workout%' OR name LIKE '%Pre Workout%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?w=500&h=500&fit=crop' WHERE name LIKE '%BCAA%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=500&h=500&fit=crop' WHERE name LIKE '%Creatine%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1610648391607-7591a1e99b2e?w=500&h=500&fit=crop' WHERE name LIKE '%Multivitamin%' OR name LIKE '%Vitamin%'",
    
    // Equipment
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1517963879433-6ad2b056d712?w=500&h=500&fit=crop' WHERE name LIKE '%Dumbbell%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1598289431512-b97b0917affc?w=500&h=500&fit=crop' WHERE name LIKE '%Kettlebell%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=500&h=500&fit=crop' WHERE name LIKE '%Resistance Band%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=500&h=500&fit=crop' WHERE name LIKE '%Yoga Mat%' OR name LIKE '%Exercise Mat%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1519505907962-0a6cb0167c73?w=500&h=500&fit=crop' WHERE name LIKE '%Jump Rope%' OR name LIKE '%Skipping Rope%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1584735175315-9d5df23860e6?w=500&h=500&fit=crop' WHERE name LIKE '%Foam Roller%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=500&h=500&fit=crop' WHERE name LIKE '%Weight Belt%' OR name LIKE '%Lifting Belt%'",
    "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1579758629938-03607ccdbaba?w=500&h=500&fit=crop' WHERE name LIKE '%Gym Gloves%' OR name LIKE '%Weight Gloves%'",
];

echo "<h2>Updating Product Images</h2>";
$updateCount = 0;

foreach ($imageUpdates as $sql) {
    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            $updateCount++;
            echo "✓ Updated successfully<br>";
        }
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

echo "<br><strong>Total products updated: $updateCount</strong><br>";

$conn->close();
?>
