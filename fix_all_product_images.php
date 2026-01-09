<?php
// fix_all_product_images.php
// Updates ALL product images with varied, high-quality Unsplash URLs (randomized per category).

require 'db_connect.php';

echo "<h3>Randomizing Product Images...</h3>";

// 1. Define Image Pools (Unsplash Source URLs directly would be easier, but direct image links are faster)
// These are real Unsplash photo IDs relevant to each category.

// MEN'S WEAR
$menImages = [
    'https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&q=80&w=600', // White tee
    'https://images.unsplash.com/photo-1618354691438-25bc04584c23?auto=format&fit=crop&q=80&w=600', // Black tee
    'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&q=80&w=600', // Running shirt
    'https://images.unsplash.com/photo-1552160753-117159d79631?auto=format&fit=crop&q=80&w=600', // Joggers
    'https://images.unsplash.com/photo-1590400902521-4d9432bb9a69?auto=format&fit=crop&q=80&w=600', // Vest
    'https://images.unsplash.com/photo-1516666117442-31c18e3fa78d?auto=format&fit=crop&q=80&w=600', // Man working out
    'https://images.unsplash.com/photo-1506629082955-511b1aa002c9?auto=format&fit=crop&q=80&w=600', // Male model
    'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?auto=format&fit=crop&q=80&w=600', // Shirtless/active
    'https://images.unsplash.com/photo-1483721310020-03333e577078?auto=format&fit=crop&q=80&w=600', // Training
    'https://images.unsplash.com/photo-1560272564-c83b66b1ad12?auto=format&fit=crop&q=80&w=600'  // Lifestyle
];

// WOMEN'S WEAR
$womenImages = [
    'https://images.unsplash.com/photo-1574914629385-46448b767bb1?auto=format&fit=crop&q=80&w=600', // Leggings
    'https://images.unsplash.com/photo-1620799140408-ed5341cd2431?auto=format&fit=crop&q=80&w=600', // Sports Bra
    'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&q=80&w=600', // Activewear
    'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=600', // Yoga set
    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&q=80&w=600', // Hoodie
    'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&q=80&w=600', // Trainer
    'https://images.unsplash.com/photo-1596356453261-0d265ae2520a?auto=format&fit=crop&q=80&w=600', // Stretching
    'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?auto=format&fit=crop&q=80&w=600', // Gym girl
    'https://images.unsplash.com/photo-1518865660601-e28318115599?auto=format&fit=crop&q=80&w=600', // Runner
    'https://images.unsplash.com/photo-1434682881908-b43d0467b798?auto=format&fit=crop&q=80&w=600'  // Shoes/Legs
];

// SUPPLEMENTS
$suppImages = [
    'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?auto=format&fit=crop&q=80&w=600', // Whey
    'https://plus.unsplash.com/premium_photo-1675806655184-7a353683f218?auto=format&fit=crop&q=80&w=600', // Creatine
    'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&q=80&w=600', // Pre-workout
    'https://images.unsplash.com/photo-1579722822173-ffae64857388?auto=format&fit=crop&q=80&w=600', // BCAA
    'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&q=80&w=600', // Pills
    'https://images.unsplash.com/photo-1471864190281-a93a3070b6de?auto=format&fit=crop&q=80&w=600', // Medicine
    'https://images.unsplash.com/photo-1550572017-edd951aa8f72?auto=format&fit=crop&q=80&w=600', // Protein Shake
    'https://images.unsplash.com/photo-1626959247167-1601735d18ba?auto=format&fit=crop&q=80&w=600', // Bottle
    'https://images.unsplash.com/photo-1514782831304-632d847018d4?auto=format&fit=crop&q=80&w=600', // Shaker
    'https://images.unsplash.com/photo-1594381898411-846e7d193883?auto=format&fit=crop&q=80&w=600'  // Jars
];

// EQUIPMENT
$equipImages = [
    'https://images.unsplash.com/photo-1638536532686-d610adfc8e5c?auto=format&fit=crop&q=80&w=600', // Dumbbells
    'https://images.unsplash.com/photo-1598289431512-b97b0917affc?auto=format&fit=crop&q=80&w=600', // Bands
    'https://images.unsplash.com/photo-1517963652038-16cb0358826d?auto=format&fit=crop&q=80&w=600', // Kettlebell
    'https://images.unsplash.com/photo-1599901860904-17e6ed7083a0?auto=format&fit=crop&q=80&w=600', // Yoga Mat
    'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&q=80&w=600', // Diverse equipment
    'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=600', // Gym room
    'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&q=80&w=600', // Weights
    'https://images.unsplash.com/photo-1597452485669-2c7bb5fef90d?auto=format&fit=crop&q=80&w=600', // Treadmill/Blur
    'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?auto=format&fit=crop&q=80&w=600'  // Weights
];

// Helper to update products of a specific category
function randomizeCategory($conn, $cat, $images) {
    echo "<h4>Updating $cat...</h4>";
    
    // Get all product IDs for this category
    $ids = [];
    $res = $conn->query("SELECT product_id FROM products WHERE category = '$cat'");
    while($row = $res->fetch_assoc()) {
        $ids[] = $row['product_id'];
    }

    $count = 0;
    foreach ($ids as $pid) {
        $randImg = $images[array_rand($images)];
        $sql = "UPDATE products SET image_url = '$randImg' WHERE product_id = $pid AND image_url NOT LIKE '%.png'"; 
        // Note: The NOT LIKE '%.png' check preserves our custom generated images (which are .png)
        // while overwriting the repetitive placeholders or Unsplash links.
        
        if ($conn->query($sql)) $count++;
    }
    echo "• Updated $count products in $cat.<br>";
}

randomizeCategory($conn, 'men', $menImages);
randomizeCategory($conn, 'women', $womenImages);
randomizeCategory($conn, 'supplements', $suppImages);
randomizeCategory($conn, 'equipment', $equipImages);

// Explicitly ensure the hero items keep their generated PNGs if they exist in file system
// (The SQL query above protects them, but being safe)
$conn->query("UPDATE products SET image_url='stringer_vest.png' WHERE name='Bodybuilding Stringer Vest'");
$conn->query("UPDATE products SET image_url='performance_hoodie.png' WHERE name='Performance Hoodie'");
$conn->query("UPDATE products SET image_url='gym_joggers.png' WHERE name='Gym Joggers'");

echo "<hr><p>All product images have been randomized and updated!</p>";
echo "<a href='fitshop.php' style='padding:10px 20px; background:#0F2C59; color:white; text-decoration:none; border-radius:5px;'>Return to FitShop</a>";

$conn->close();
?>
