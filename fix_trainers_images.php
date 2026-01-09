<?php
// fix_trainers_images.php
// Downloads unique images for trainers to ensure they load and look correct.

set_time_limit(300); // 5 minutes

// Create directory
$dir = __DIR__ . '/assets/trainers';
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

function downloadImage($url, $savePath) {
    echo "Downloading $url to $savePath... ";
    $ch = curl_init($url);
    $fp = fopen($savePath, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // Mimic browser
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
    curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    fclose($fp);
    
    if ($error) {
        echo "Failed: $error<br>";
        return false;
    }
    
    if (filesize($savePath) < 1000) {
        echo "Failed: File too small (likely error page).<br>";
        @unlink($savePath);
        return false;
    }
    
    echo "Success!<br>";
    return true;
}

// Categories and Counts
$categories = [
    'strength' => 10,
    'yoga' => 10,
    'cardio' => 10
];

// Unsplash IDs (Curated to be valid and diverse)
// We will use a list of reliable IDs or search terms.
// Since search URLs might redirect, we'll try specific high-quality IDs.

// Reliable Image Sources (Hardcoded to avoid random failures/cats)
// Sources selected to look like Indian/South Asian fitness professionals
$images = [
    'strength' => [
        'https://images.unsplash.com/photo-1599058945522-28d584b6f0ff', // Indian Man Gym
        'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e', // Gym Man
        'https://images.unsplash.com/photo-1534438327276-14e5300c3a48', // Gym
        'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b', // Trainer
        'https://images.unsplash.com/photo-1611672585731-fa10603fb9c2', // Gym
    ],
    'yoga' => [
        'https://images.unsplash.com/photo-1599901860904-17e6ed7083a0', // Yoga Woman
        'https://images.unsplash.com/photo-1545205597-3d9d02c29597', // Yoga
        'https://images.unsplash.com/photo-1588286840111-20b4601e96a1', // Yoga
        'https://images.unsplash.com/photo-1593811167562-9cef47bfc4d7', // Yoga
        'https://images.unsplash.com/photo-1506126613408-eca07ce68773', // Yoga
    ],
    'cardio' => [
        'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8', // Runner
        'https://images.unsplash.com/photo-1552674605-1e95fdd54343', // Runner
        'https://images.unsplash.com/photo-1538354645224-cca62808b8b3', // Runner
        'https://images.unsplash.com/photo-1486739985632-d482aa806e53', // Runner
        'https://images.unsplash.com/photo-1502224562085-639556652f33', // Runner
    ]
];

foreach ($images as $cat => $urls) {
    echo "Processing category: $cat<br>";
    $count = 0;
    // We need 10 images per category. We will cycle through the 5 good ones twice.
    for ($i = 0; $i < 10; $i++) {
        $filename = "{$cat}_{$i}.jpg";
        $savePath = $dir . '/' . $filename;
        
        $urlIndex = $i % count($urls);
        $baseUrl = $urls[$urlIndex];
        // Append size params
        $downloadUrl = $baseUrl . "?auto=format&fit=crop&q=80&w=400";
        
        echo "Downloading $filename... ";
        if (downloadImage($downloadUrl, $savePath)) {
             echo "Success<br>";
        } else {
             echo "Failed<br>";
        }
    }
}

echo "All Done!";
?>
