<?php
// add_bulk_products.php
// Adds 20 new products to each of the 4 categories.

require 'db_connect.php';

echo "<h3>Adding Bulk Products...</h3>";

// Helper for random ratings
function rRating() { return number_format(lcg_value() * (5 - 4) + 4, 1); }
function rReview() { return rand(10, 500); }

// Generic Placeholders - ideally these would be distinct, but we'll reuse available images or generic ones
// Assuming we have some base images, or we can reuse the ones we just made
$images = [
    'men' => 'stringer_vest.png',
    'women' => 'https://images.unsplash.com/photo-1518865660601-e28318115599?auto=format&fit=crop&q=80&w=600',
    'equipment' => 'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&q=80&w=600',
    'supplements' => 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?auto=format&fit=crop&q=80&w=600'
];

// DATA SETS
// 1. MEN
$men_base = [
    ['HyperFlex T-Shirt', 1299], ['Stealth Gym Hoodie', 2499], ['Titan Cargo Joggers', 1899], ['Pro Compress Shorts', 999], 
    ['Core Tank Top', 799], ['Elite Training Jacket', 2999], ['VaporDry Running Tee', 1199], ['MuscleFit Polo', 1499],
    ['PowerLifting Singlet', 2199], ['Thermal Base Layer', 1599], ['Retro Short Shorts', 899], ['Heavy Duty Sweatshirt', 2299],
    ['Performance Tracksuit', 3499], ['Sleeveless Hoodie', 1699], ['Mesh Training Shorts', 1099], ['Compression Leggings', 1399],
    ['Graphic Gym Tee', 899], ['Seamless Long Sleeve', 1799], ['Windbreaker Jacket', 2799], ['Recover Slide Sandals', 1999]
];

// 2. WOMEN
$women_base = [
    ['Sculpt High-Waist Leggings', 1999], ['Impact Sports Bra', 1499], ['Seamless Crop Top', 1299], ['Flex Yoga Pants', 1699],
    ['Breathable Racerback', 999], ['Zip-Up Running Jacket', 2599], ['Power Short', 1199], ['Long Sleeve Crop', 1399],
    ['Performance Tank', 899], ['Booty Lift Shorts', 1099], ['Mesh Panel Leggings', 1899], ['Oversized Gym Tee', 1199],
    ['Support Tank Top', 949], ['Thermal Leggings', 2199], ['Training Bodysuit', 2499], ['Lightweight Hoodie', 1799],
    ['Yoga Biker Shorts', 1045], ['Strappy Sports Bra', 1249], ['Compression Calf Sleeves', 699], ['Recover Lounge Set', 2999]
];

// 3. EQUIPMENT
$equip_base = [
    ['Adjustable Dumbbells (Pair)', 8999], ['Resistance Bands Set', 1299], ['Yoga Mat Premium', 1499], ['Kettlebell 10kg', 2499],
    ['Ab Roller Wheel', 799], ['Jump Rope Speed', 499], ['Pull-Up Bar Doorway', 1899], ['Fitness Ball 65cm', 999],
    ['Weight Lifting Belt', 2199], ['Gym Gloves Pro', 699], ['Foam Roller Grid', 1199], ['Push-Up Bars', 599],
    ['Ankle Weights (2kg)', 899], ['Battle Rope 9m', 4999], ['Medicine Ball 5kg', 1999], ['Suspension Trainer', 3499],
    ['Wrist Wraps', 399], ['Knee Sleeves (Pair)', 1499], ['Liquid Chalk', 299], ['Shaker Bottle 700ml', 349]
];

// 4. SUPPLEMENTS
$supp_base = [
    ['Whey Protein Isolate 1kg', 2999], ['Creatine Monohydrate 250g', 999], ['BCAA Powder 300g', 1899], ['Pre-Workout Explode', 2499],
    ['Multivitamin Daily', 899], ['Omega-3 Fish Oil', 1299], ['ZMA Night Recovery', 1099], ['Glutamine Powder', 1499],
    ['Mass Gainer 3kg', 4499], ['L-Carnitine Liquid', 1599], ['Casein Protein 1kg', 3199], ['Vegan Pea Protein', 2699],
    ['Beta Alanine', 1199], ['Fat Burner Thermo', 1999], ['Joint Support Glucosamine', 999], ['Vitamin D3 + K2', 599],
    ['Electrolyte Powder', 799], ['Protein Bars (Box of 12)', 2199], ['Caffeine Pills 200mg', 399], ['Collagen Peptides', 2299]
];

// Insert Function
function insertProducts($conn, $items, $cat, $defaultImg) {
    echo "<h4>Adding $cat products...</h4>";
    $count = 0;
    foreach ($items as $item) {
        $name = $item[0];
        $price = $item[1];
        // Random attributes for variety
        $rating = rRating();
        $reviews = rReview();
        $is_new = (rand(0, 10) > 8) ? 1 : 0;
        $is_sale = (rand(0, 10) > 8) ? 1 : 0;
        $has_sizes = ($cat == 'men' || $cat == 'women') ? 1 : 0;
        
        // Prevent duplicates
        $check = $conn->query("SELECT * FROM products WHERE name = '$name'");
        if ($check->num_rows == 0) {
            $sql = "INSERT INTO products (name, category, price, image_url, rating, review_count, is_new, is_sale, has_sizes) 
                    VALUES ('$name', '$cat', $price, '$defaultImg', $rating, $reviews, $is_new, $is_sale, $has_sizes)";
            if ($conn->query($sql)) {
                $count++;
            } else {
                echo "Error: " . $conn->error . "<br>";
            }
        }
    }
    echo "✓ Added $count products to $cat.<br>";
}

// EXECUTE
insertProducts($conn, $men_base, 'men', $images['men']);
insertProducts($conn, $women_base, 'women', $images['women']);
insertProducts($conn, $equip_base, 'equipment', $images['equipment']);
insertProducts($conn, $supp_base, 'supplements', $images['supplements']);

echo "<hr><p>Done! <a href='fitshop.php'>Go to Shop</a></p>";

// Notify Admin of Bulk Operation
require_once 'admin_notifications.php';
$bulkMsg = "Bulk Import Complete: 80 new products added to the shop.";
if (function_exists('sendAdminNotification')) {
    sendAdminNotification($conn, $bulkMsg);
}
?>
