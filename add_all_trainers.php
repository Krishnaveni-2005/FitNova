<?php
require 'db_connect.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Adding Trainers to Database</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; background: #f5f7fa; }
        h2 { color: #0F2C59; border-bottom: 3px solid #4FACFE; padding-bottom: 10px; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .summary { background: white; padding: 25px; border-radius: 10px; margin: 30px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .summary h3 { color: #0F2C59; margin-top: 0; }
        .btn { display: inline-block; padding: 12px 25px; background: #0F2C59; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #1a4d8f; }
        table { width: 100%; border-collapse: collapse; background: white; margin: 20px 0; }
        th { background: #0F2C59; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
<h2>🏋️ Adding Trainers to FitNova Database</h2>";

// Complete trainer data with all details
$trainersData = [
    // Strength & Conditioning Trainers
    [
        'first_name' => 'Arjun',
        'last_name' => 'Kapoor',
        'email' => 'arjun.kapoor@fitnova.com',
        'phone' => '9876543210',
        'specialization' => 'Strength & Conditioning',
        'image' => 'strength_0.jpg',
        'bio' => 'Certified strength coach with 8 years of experience. Specialized in powerlifting and functional training. Helped 200+ clients achieve their strength goals.',
        'experience_years' => 8,
        'certifications' => 'NSCA-CPT, CSCS, Precision Nutrition Level 1'
    ],
    [
        'first_name' => 'Vikram',
        'last_name' => 'Singh',
        'email' => 'vikram.singh@fitnova.com',
        'phone' => '9876543211',
        'specialization' => 'Strength & Conditioning',
        'image' => 'strength_1.jpg',
        'bio' => 'Former national-level athlete turned fitness coach. Expert in Olympic weightlifting and athletic performance training.',
        'experience_years' => 10,
        'certifications' => 'ISSA-CPT, Olympic Weightlifting Coach, Sports Nutrition Specialist'
    ],
    [
        'first_name' => 'Rahul',
        'last_name' => 'Dravid',
        'email' => 'rahul.dravid@fitnova.com',
        'phone' => '9876543212',
        'specialization' => 'Strength & Conditioning',
        'image' => 'strength_2.jpg',
        'bio' => 'Bodybuilding champion with expertise in muscle building and body transformation. Passionate about helping clients build their dream physique.',
        'experience_years' => 7,
        'certifications' => 'ACE-CPT, Bodybuilding Specialist, Advanced Nutrition Coach'
    ],
    [
        'first_name' => 'Karan',
        'last_name' => 'Shergill',
        'email' => 'karan.shergill@fitnova.com',
        'phone' => '9876543213',
        'specialization' => 'Strength & Conditioning',
        'image' => 'strength_3.jpg',
        'bio' => 'Military fitness expert specializing in functional strength and endurance training. Trains clients for peak physical performance.',
        'experience_years' => 9,
        'certifications' => 'NASM-CPT, Tactical Strength Coach, CrossFit Level 2'
    ],
    [
        'first_name' => 'Rohan',
        'last_name' => 'Bopanna',
        'email' => 'rohan.bopanna@fitnova.com',
        'phone' => '9876543214',
        'specialization' => 'Strength & Conditioning',
        'image' => 'strength_5.jpg',
        'bio' => 'Sports conditioning coach with experience training professional athletes. Focus on explosive power and agility development.',
        'experience_years' => 6,
        'certifications' => 'CSCS, Sports Performance Specialist, Mobility Coach'
    ],
    [
        'first_name' => 'Sanjay',
        'last_name' => 'Dutt',
        'email' => 'sanjay.dutt@fitnova.com',
        'phone' => '9876543215',
        'specialization' => 'Strength & Conditioning',
        'image' => 'strength_6.jpg',
        'bio' => 'Veteran strength coach known for transforming beginners into advanced lifters. Specializes in progressive overload training.',
        'experience_years' => 12,
        'certifications' => 'ISSA-CPT, Strength & Conditioning Specialist, Rehabilitation Expert'
    ],
    [
        'first_name' => 'Varun',
        'last_name' => 'Dhawan',
        'email' => 'varun.dhawan@fitnova.com',
        'phone' => '9876543216',
        'specialization' => 'Strength & Conditioning',
        'image' => 'strength_7.jpg',
        'bio' => 'Dynamic trainer focusing on high-intensity strength training and metabolic conditioning for rapid results.',
        'experience_years' => 5,
        'certifications' => 'ACE-CPT, HIIT Specialist, Functional Training Expert'
    ],
    [
        'first_name' => 'John',
        'last_name' => 'Abraham',
        'email' => 'john.abraham@fitnova.com',
        'phone' => '9876543217',
        'specialization' => 'Strength & Conditioning',
        'image' => 'strength_8.jpg',
        'bio' => 'Fitness model and coach specializing in aesthetic bodybuilding and lean muscle development.',
        'experience_years' => 11,
        'certifications' => 'NASM-CPT, Bodybuilding Coach, Posing Specialist'
    ],
    [
        'first_name' => 'Tiger',
        'last_name' => 'Shroff',
        'email' => 'tiger.shroff@fitnova.com',
        'phone' => '9876543218',
        'specialization' => 'Strength & Conditioning',
        'image' => 'strength_0.jpg',
        'bio' => 'Martial arts expert and strength coach. Combines traditional strength training with martial arts conditioning.',
        'experience_years' => 8,
        'certifications' => 'Martial Arts Instructor, ISSA-CPT, Flexibility Coach'
    ],
    [
        'first_name' => 'Vidyut',
        'last_name' => 'Jammwal',
        'email' => 'vidyut.jammwal@fitnova.com',
        'phone' => '9876543219',
        'specialization' => 'Strength & Conditioning',
        'image' => 'strength_1.jpg',
        'bio' => 'Action fitness specialist focusing on functional strength, agility, and combat conditioning.',
        'experience_years' => 9,
        'certifications' => 'Kalaripayattu Master, CSCS, Parkour Instructor'
    ],
    
    // Yoga & Flexibility Trainers
    [
        'first_name' => 'Isha',
        'last_name' => 'Koppikar',
        'email' => 'isha.koppikar@fitnova.com',
        'phone' => '9876543220',
        'specialization' => 'Yoga & Flexibility',
        'image' => 'yoga_0.jpg',
        'bio' => 'Certified yoga instructor with expertise in Hatha and Vinyasa yoga. Helps clients improve flexibility and mental wellness.',
        'experience_years' => 7,
        'certifications' => 'RYT-500, Yoga Alliance Certified, Meditation Teacher'
    ],
    [
        'first_name' => 'Shilpa',
        'last_name' => 'Shetty',
        'email' => 'shilpa.shetty@fitnova.com',
        'phone' => '9876543221',
        'specialization' => 'Yoga & Flexibility',
        'image' => 'yoga_1.jpg',
        'bio' => 'Renowned yoga expert and wellness advocate. Specializes in therapeutic yoga and stress management.',
        'experience_years' => 15,
        'certifications' => 'RYT-500, Ayurveda Practitioner, Wellness Coach'
    ],
    [
        'first_name' => 'Malaika',
        'last_name' => 'Arora',
        'email' => 'malaika.arora@fitnova.com',
        'phone' => '9876543222',
        'specialization' => 'Yoga & Flexibility',
        'image' => 'yoga_3.jpg',
        'bio' => 'Power yoga specialist focusing on strength, flexibility, and body toning through dynamic yoga practices.',
        'experience_years' => 10,
        'certifications' => 'Power Yoga Instructor, Pilates Certified, Nutrition Coach'
    ],
    [
        'first_name' => 'Bipasha',
        'last_name' => 'Basu',
        'email' => 'bipasha.basu@fitnova.com',
        'phone' => '9876543223',
        'specialization' => 'Yoga & Flexibility',
        'image' => 'yoga_4.jpg',
        'bio' => 'Fitness and yoga expert combining traditional yoga with modern fitness techniques for holistic wellness.',
        'experience_years' => 12,
        'certifications' => 'RYT-200, Fitness Trainer, Holistic Health Coach'
    ],
    [
        'first_name' => 'Kareena',
        'last_name' => 'Kapoor',
        'email' => 'kareena.kapoor@fitnova.com',
        'phone' => '9876543224',
        'specialization' => 'Yoga & Flexibility',
        'image' => 'yoga_5.jpg',
        'bio' => 'Prenatal and postnatal yoga specialist. Helps women maintain fitness through all stages of life.',
        'experience_years' => 8,
        'certifications' => 'Prenatal Yoga Certified, RYT-200, Women\'s Health Specialist'
    ],
    [
        'first_name' => 'Anushka',
        'last_name' => 'Sharma',
        'email' => 'anushka.sharma@fitnova.com',
        'phone' => '9876543225',
        'specialization' => 'Yoga & Flexibility',
        'image' => 'yoga_6.jpg',
        'bio' => 'Mindfulness and yoga instructor focusing on mental clarity, flexibility, and overall well-being.',
        'experience_years' => 6,
        'certifications' => 'RYT-200, Mindfulness Coach, Stress Management Expert'
    ],
    [
        'first_name' => 'Deepika',
        'last_name' => 'Padukone',
        'email' => 'deepika.padukone@fitnova.com',
        'phone' => '9876543226',
        'specialization' => 'Yoga & Flexibility',
        'image' => 'yoga_8.jpg',
        'bio' => 'Mental health advocate and yoga instructor. Specializes in yoga for anxiety and depression management.',
        'experience_years' => 9,
        'certifications' => 'RYT-500, Mental Health First Aid, Therapeutic Yoga'
    ],
    [
        'first_name' => 'Priyanka',
        'last_name' => 'Chopra',
        'email' => 'priyanka.chopra@fitnova.com',
        'phone' => '9876543227',
        'specialization' => 'Yoga & Flexibility',
        'image' => 'yoga_9.jpg',
        'bio' => 'International yoga and wellness coach. Combines Eastern and Western approaches to holistic fitness.',
        'experience_years' => 11,
        'certifications' => 'RYT-500, International Wellness Coach, Lifestyle Medicine'
    ],
    [
        'first_name' => 'Alia',
        'last_name' => 'Bhatt',
        'email' => 'alia.bhatt@fitnova.com',
        'phone' => '9876543228',
        'specialization' => 'Yoga & Flexibility',
        'image' => 'yoga_0.jpg',
        'bio' => 'Young and energetic yoga instructor specializing in beginner-friendly yoga and flexibility training.',
        'experience_years' => 5,
        'certifications' => 'RYT-200, Beginner Yoga Specialist, Flexibility Coach'
    ],
    [
        'first_name' => 'Katrina',
        'last_name' => 'Kaif',
        'email' => 'katrina.kaif@fitnova.com',
        'phone' => '9876543229',
        'specialization' => 'Yoga & Flexibility',
        'image' => 'yoga_1.jpg',
        'bio' => 'Dance and yoga fusion expert. Creates unique programs combining flexibility, strength, and grace.',
        'experience_years' => 10,
        'certifications' => 'RYT-200, Dance Instructor, Body Conditioning Specialist'
    ],
    
    // Cardio & HIIT Trainers
    [
        'first_name' => 'Milind',
        'last_name' => 'Soman',
        'email' => 'milind.soman@fitnova.com',
        'phone' => '9876543230',
        'specialization' => 'Cardio & HIIT',
        'image' => 'cardio_0.jpg',
        'bio' => 'Ultra-marathon runner and endurance coach. Specializes in building cardiovascular fitness and stamina.',
        'experience_years' => 20,
        'certifications' => 'Running Coach, Endurance Specialist, Ironman Certified'
    ],
    [
        'first_name' => 'Ranveer',
        'last_name' => 'Singh',
        'email' => 'ranveer.singh@fitnova.com',
        'phone' => '9876543231',
        'specialization' => 'Cardio & HIIT',
        'image' => 'cardio_4.jpg',
        'bio' => 'High-energy HIIT specialist known for intense, results-driven workouts. Expert in fat loss and conditioning.',
        'experience_years' => 7,
        'certifications' => 'HIIT Certified, Metabolic Conditioning Coach, Fat Loss Specialist'
    ],
    [
        'first_name' => 'Hrithik',
        'last_name' => 'Roshan',
        'email' => 'hrithik.roshan@fitnova.com',
        'phone' => '9876543232',
        'specialization' => 'Cardio & HIIT',
        'image' => 'cardio_5.jpg',
        'bio' => 'Transformation specialist combining cardio, HIIT, and dance for complete body conditioning.',
        'experience_years' => 14,
        'certifications' => 'ACE-CPT, Dance Fitness Instructor, Body Transformation Coach'
    ],
    [
        'first_name' => 'Shahid',
        'last_name' => 'Kapoor',
        'email' => 'shahid.kapoor@fitnova.com',
        'phone' => '9876543233',
        'specialization' => 'Cardio & HIIT',
        'image' => 'cardio_9.jpg',
        'bio' => 'Cardio kickboxing and HIIT expert. Creates fun, challenging workouts for maximum calorie burn.',
        'experience_years' => 9,
        'certifications' => 'Kickboxing Instructor, HIIT Specialist, Cardio Coach'
    ],
    [
        'first_name' => 'Farhan',
        'last_name' => 'Akhtar',
        'email' => 'farhan.akhtar@fitnova.com',
        'phone' => '9876543234',
        'specialization' => 'Cardio & HIIT',
        'image' => 'cardio_0.jpg',
        'bio' => 'Athletic conditioning coach specializing in sprint training and explosive cardio workouts.',
        'experience_years' => 8,
        'certifications' => 'Sprint Coach, Athletic Performance, HIIT Certified'
    ],
    [
        'first_name' => 'Akshay',
        'last_name' => 'Kumar',
        'email' => 'akshay.kumar@fitnova.com',
        'phone' => '9876543235',
        'specialization' => 'Cardio & HIIT',
        'image' => 'cardio_4.jpg',
        'bio' => 'Martial arts and cardio conditioning expert. Focuses on functional fitness and cardiovascular health.',
        'experience_years' => 18,
        'certifications' => 'Martial Arts Master, Cardio Specialist, Functional Fitness Coach'
    ],
    [
        'first_name' => 'Sunil',
        'last_name' => 'Chhetri',
        'email' => 'sunil.chhetri@fitnova.com',
        'phone' => '9876543236',
        'specialization' => 'Cardio & HIIT',
        'image' => 'cardio_5.jpg',
        'bio' => 'Professional athlete and cardio coach. Specializes in sports-specific conditioning and endurance.',
        'experience_years' => 12,
        'certifications' => 'Sports Conditioning Coach, Endurance Specialist, Athletic Trainer'
    ],
    [
        'first_name' => 'Neeraj',
        'last_name' => 'Chopra',
        'email' => 'neeraj.chopra@fitnova.com',
        'phone' => '9876543237',
        'specialization' => 'Cardio & HIIT',
        'image' => 'cardio_9.jpg',
        'bio' => 'Olympic athlete and performance coach. Expert in explosive power and cardiovascular conditioning.',
        'experience_years' => 6,
        'certifications' => 'Olympic Coach, Performance Specialist, Speed & Agility Coach'
    ],
    [
        'first_name' => 'Virat',
        'last_name' => 'Kohli',
        'email' => 'virat.kohli@fitnova.com',
        'phone' => '9876543238',
        'specialization' => 'Cardio & HIIT',
        'image' => 'cardio_0.jpg',
        'bio' => 'Elite athlete fitness coach focusing on peak performance, stamina, and cardiovascular excellence.',
        'experience_years' => 10,
        'certifications' => 'Athletic Performance Coach, Cardio Specialist, Recovery Expert'
    ],
    [
        'first_name' => 'MS',
        'last_name' => 'Dhoni',
        'email' => 'ms.dhoni@fitnova.com',
        'phone' => '9876543239',
        'specialization' => 'Cardio & HIIT',
        'image' => 'cardio_4.jpg',
        'bio' => 'Sports fitness legend specializing in mental conditioning, endurance, and cardiovascular training.',
        'experience_years' => 16,
        'certifications' => 'Sports Psychology, Endurance Coach, Leadership in Fitness'
    ],
];

$added = 0;
$skipped = 0;
$errors = 0;

// Default password for all trainers
$defaultPassword = password_hash('FitNova2026', PASSWORD_DEFAULT);

foreach ($trainersData as $trainer) {
    $checkQuery = "SELECT user_id FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $trainer['email']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo "<div class='warning'>⚠️ <strong>{$trainer['first_name']} {$trainer['last_name']}</strong> ({$trainer['email']}) - Already exists, skipped</div>";
        $skipped++;
        $checkStmt->close();
        continue;
    }
    $checkStmt->close();
    
    // Insert trainer
    $imagePath = 'assets/trainers/' . $trainer['image'];
    
    $insertQuery = "INSERT INTO users (
        first_name, last_name, email, phone, password_hash, role, 
        trainer_specialization, profile_picture, account_status, bio, 
        trainer_certification, trainer_experience, created_at
    ) VALUES (?, ?, ?, ?, ?, 'trainer', ?, ?, 'active', ?, ?, ?, NOW())";
    
    $insertStmt = $conn->prepare($insertQuery);
    // Fixed bind_param string: 10 params (ssssisssss => wait count 10?)
    // Params: first(s), last(s), email(s), phone(s), pass(s), spec(s), pic(s), bio(s), cert(s), exp(i)
    // 9 strings, 1 int. "sssssssssi"
    $insertStmt->bind_param(
        "sssssssssi",
        $trainer['first_name'],
        $trainer['last_name'],
        $trainer['email'],
        $trainer['phone'],
        $defaultPassword,
        $trainer['specialization'],
        $imagePath,
        $trainer['bio'],
        $trainer['certifications'],
        $trainer['experience_years']
    );
    
    if ($insertStmt->execute()) {
        echo "<div class='success'>✅ <strong>{$trainer['first_name']} {$trainer['last_name']}</strong> - Added successfully ({$trainer['specialization']})</div>";
        $added++;
    } else {
        echo "<div class='error'>❌ <strong>{$trainer['first_name']} {$trainer['last_name']}</strong> - Error: " . $insertStmt->error . "</div>";
        $errors++;
    }
    
    $insertStmt->close();
}

echo "<div class='summary'>";
echo "<h3>📊 Summary</h3>";
echo "<table>";
echo "<tr><th>Status</th><th>Count</th></tr>";
echo "<tr><td>✅ Successfully Added</td><td><strong>$added</strong> trainers</td></tr>";
echo "<tr><td>⚠️ Skipped (Already Exist)</td><td><strong>$skipped</strong> trainers</td></tr>";
echo "<tr><td>❌ Errors</td><td><strong>$errors</strong> trainers</td></tr>";
echo "<tr style='background: #e3f2fd;'><td><strong>📊 Total Processed</strong></td><td><strong>" . count($trainersData) . "</strong> trainers</td></tr>";
echo "</table>";

echo "<div style='margin-top: 25px; padding: 20px; background: #e8f5e9; border-radius: 8px;'>";
echo "<h4 style='margin-top: 0; color: #2e7d32;'>🔑 Login Credentials</h4>";
echo "<p><strong>Default Password for all trainers:</strong> <code style='background: white; padding: 5px 10px; border-radius: 4px; font-size: 16px;'>FitNova2026</code></p>";
echo "<p>Trainers can log in using their email address and this password.</p>";
echo "</div>";

echo "<div style='margin-top: 25px;'>";
echo "<a href='show_database_users.php' class='btn'>📋 View All Users in Database</a>";
echo "<a href='trainers.php' class='btn' style='background: #4FACFE;'>👨‍🏫 View Trainers Page</a>";
echo "<a href='home.php' class='btn' style='background: #2ecc71;'>🏠 Go to Home</a>";
echo "</div>";

echo "</div>";

$conn->close();

echo "</body></html>";
?>
