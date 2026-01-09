<?php
// fix_variety_images.php
// 1. Downloads a set of diverse images from Unsplash to the local server.
// 2. Assigns these images to products in a Round-Robin fashion to ensure variety.

require 'db_connect.php';

// Increase timeout for downloads
set_time_limit(300);

echo "<h3>Downloading and Assigning Diverse Images...</h3>";

// --- CONFIGURATION: IMAGE POOLS ---

$pools = [
    'supplements' => [
        ['url' => 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?auto=format&fit=crop&q=80&w=600', 'file' => 'supp_whey.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&q=80&w=600', 'file' => 'supp_pills.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1514782831304-632d847018d4?auto=format&fit=crop&q=80&w=600', 'file' => 'supp_shaker.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&q=80&w=600', 'file' => 'supp_prework.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1579722822173-ffae64857388?auto=format&fit=crop&q=80&w=600', 'file' => 'supp_powder.jpg']
    ],
    'women' => [
        ['url' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=600', 'file' => 'women_leggings.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1620799140408-ed5341cd2431?auto=format&fit=crop&q=80&w=600', 'file' => 'women_bra.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&q=80&w=600', 'file' => 'women_yoga.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1518865660601-e28318115599?auto=format&fit=crop&q=80&w=600', 'file' => 'women_runner.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&q=80&w=600', 'file' => 'women_hoodie.jpg']
    ],
    'equipment' => [
        ['url' => 'https://images.unsplash.com/photo-1638536532686-d610adfc8e5c?auto=format&fit=crop&q=80&w=600', 'file' => 'equip_dumbbells.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1517963652038-16cb0358826d?auto=format&fit=crop&q=80&w=600', 'file' => 'equip_kettlebell.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1599901860904-17e6ed7083a0?auto=format&fit=crop&q=80&w=600', 'file' => 'equip_mat.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1598289431512-b97b0917affc?auto=format&fit=crop&q=80&w=600', 'file' => 'equip_bands.jpg'],
        ['url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=600', 'file' => 'equip_gym.jpg']
    ],
    // For Men, we use our existing PNGs + some downloads to mix it up
    'men' => [
        ['url' => 'LOCAL', 'file' => 'stringer_vest.png'],
        ['url' => 'LOCAL', 'file' => 'performance_hoodie.png'],
        ['url' => 'LOCAL', 'file' => 'gym_joggers.png'],
        ['url' => 'LOCAL', 'file' => 'graphic_gym_tee.png'],
        ['url' => 'LOCAL', 'file' => 'compression_leggings.png'],
        ['url' => 'LOCAL', 'file' => 'mesh_shorts.png'],
        ['url' => 'https://images.unsplash.com/photo-1516666117442-31c18e3fa78d?auto=format&fit=crop&q=80&w=600', 'file' => 'men_runner.jpg']
    ]
];

// --- HELPER FUNCTIONS ---

function downloadFile($url, $saveTo) {
    if ($url === 'LOCAL') return true; // Skip download for local pointers
    if (file_exists($saveTo) && filesize($saveTo) > 0) return true; // Already downloaded

    $fp = fopen($saveTo, 'w+');
    if ($fp === false) return false;

    $ch = curl_init(str_replace(" ", "%20", $url));
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);
    // User agent to avoid being blocked
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);

    return ($code == 200) && (filesize($saveTo) > 0);
}

// --- EXECUTION ---

foreach ($pools as $category => $items) {
    echo "<h3>Processing Category: " . ucfirst($category) . "</h3>";
    
    // 1. Prepare the pool for this category
    $validImages = [];
    foreach ($items as $item) {
        if (downloadFile($item['url'], $item['file'])) {
            $validImages[] = $item['file'];
            echo "<span style='color:green'>✓ Ready: " . $item['file'] . "</span><br>";
        } else {
            echo "<span style='color:red'>✗ Failed download: " . $item['file'] . "</span><br>";
        }
    }

    // Fallback if absolutely everything fails (unlikely)
    if (empty($validImages)) {
        echo "⚠ No images available for $category. Skipping updates.<br>";
        continue;
    }

    // 2. Fetch all products in this category
    $stmt = $conn->prepare("SELECT product_id, name FROM products WHERE category = ? ORDER BY product_id ASC");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $count = 0;
    $poolSize = count($validImages);
    
    while ($prod = $result->fetch_assoc()) {
        // Round-robin assignment: 0, 1, 2, 3, 4, 0, 1...
        $imgToUse = $validImages[$count % $poolSize];
        
        $updateFor = $prod['product_id'];
        $conn->query("UPDATE products SET image_url = '$imgToUse' WHERE product_id = $updateFor");
        $count++;
    }
    echo "<strong>Updated $count products in $category with rotating images.</strong><br>";
}

echo "<hr><p>All products now have diverse, locally hosted images!</p>";
echo "<a href='fitshop.php' style='padding:10px 20px; background:#0F2C59; color:white; text-decoration:none; border-radius:5px;'>Return to FitShop</a>";

$conn->close();
?>
