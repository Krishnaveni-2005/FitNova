<?php
// restore_database.php
// MASTER RESTORATION SCRIPT
// This script recreates the database and all tables from scratch.

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnova_db";

// 1. Connect
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Create Database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "<h3>Step 1: Database Check</h3> <p style='color:green'>✓ Database '$dbname' is ready.</p>";
} else {
    die("Error creating database: " . $conn->error);
}

$conn->select_db($dbname);

// 3. Create USERS Table (Full Schema)
$usersSql = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NULL, 
    role ENUM('free', 'pro', 'trainer', 'admin') DEFAULT 'free',
    auth_provider ENUM('local', 'google', 'facebook') DEFAULT 'local',
    oauth_provider_id VARCHAR(255),
    is_email_verified BOOLEAN DEFAULT FALSE,
    account_status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'active',
    trainer_specialization VARCHAR(100),
    trainer_experience INT,
    trainer_certification VARCHAR(255),
    assigned_trainer_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($usersSql) === TRUE) {
    echo "<h3>Step 2: Users Table</h3> <p style='color:green'>✓ Users table created successfully.</p>";
} else {
    echo "<p style='color:red'>✗ Error creating users table: " . $conn->error . "</p>";
}

// 4. Create other tables
$tables = [
    'gym_equipment' => "CREATE TABLE IF NOT EXISTS gym_equipment (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        total_units INT NOT NULL DEFAULT 0,
        available_units INT NOT NULL DEFAULT 0,
        status VARCHAR(50) DEFAULT 'Available',
        icon VARCHAR(50) DEFAULT 'fas fa-dumbbell',
        color_class VARCHAR(20) DEFAULT 'success',
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    'products' => "CREATE TABLE IF NOT EXISTS products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category ENUM('men', 'women', 'equipment', 'supplements') NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        rating DECIMAL(3, 1) DEFAULT 4.5,
        review_count INT DEFAULT 0,
        is_new BOOLEAN DEFAULT FALSE,
        is_sale BOOLEAN DEFAULT FALSE,
        is_bestseller BOOLEAN DEFAULT FALSE,
        has_sizes BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    'shop_orders' => "CREATE TABLE IF NOT EXISTS shop_orders (
        order_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(100) NOT NULL,
        zip VARCHAR(20) NOT NULL,
        payment_method VARCHAR(50) DEFAULT 'card',
        order_status VARCHAR(50) DEFAULT 'Placed',
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        delivery_date VARCHAR(50)
    )",
    'shop_order_items' => "CREATE TABLE IF NOT EXISTS shop_order_items (
        item_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255),
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        size VARCHAR(20),
        image_url VARCHAR(255),
        FOREIGN KEY (order_id) REFERENCES shop_orders(order_id) ON DELETE CASCADE
    )",
    'trainer_profiles' => "CREATE TABLE IF NOT EXISTS client_profiles (
        profile_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        gender VARCHAR(20),
        dob DATE,
        height_cm DECIMAL(5,2),
        weight_kg DECIMAL(5,2),
        target_weight_kg DECIMAL(5,2),
        primary_goal VARCHAR(50),
        activity_level VARCHAR(50),
        injuries TEXT,
        medical_conditions TEXT,
        allergies TEXT,
        sleep_hours_avg INT,
        diet_preference VARCHAR(50),
        water_intake_liters DECIMAL(3,1),
        workout_days_per_week INT,
        equipment_access VARCHAR(50),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )",
    'trainer_workouts' => "CREATE TABLE IF NOT EXISTS trainer_workouts (
        workout_id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_id INT NOT NULL,
        plan_name VARCHAR(255),
        client_name VARCHAR(255),
        duration_weeks INT,
        exercises TEXT,
        difficulty VARCHAR(50) DEFAULT 'beginner',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trainer_id) REFERENCES users(user_id) ON DELETE CASCADE
    )",
    'trainer_diets' => "CREATE TABLE IF NOT EXISTS trainer_diet_plans (
        diet_id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_id INT NOT NULL,
        plan_name VARCHAR(255),
        client_name VARCHAR(255),
        target_calories INT,
        diet_type VARCHAR(100),
        meal_details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trainer_id) REFERENCES users(user_id) ON DELETE CASCADE
    )",
    'trainer_schedules' => "CREATE TABLE IF NOT EXISTS trainer_schedules (
        schedule_id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_id INT NOT NULL,
        client_name VARCHAR(255),
        session_time DATETIME, 
        session_date DATE,
        session_type VARCHAR(100),
        status VARCHAR(50) DEFAULT 'upcoming',
        FOREIGN KEY (trainer_id) REFERENCES users(user_id) ON DELETE CASCADE
    )",
    'trainer_attendance' => "CREATE TABLE IF NOT EXISTS trainer_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_id INT NOT NULL,
        check_in_time DATETIME NOT NULL,
        check_out_time DATETIME DEFAULT NULL,
        zone VARCHAR(100) DEFAULT 'General Gym Floor',
        status ENUM('checked_in', 'checked_out') DEFAULT 'checked_in',
        FOREIGN KEY (trainer_id) REFERENCES users(user_id) ON DELETE CASCADE
    )",
    'messages' => "CREATE TABLE IF NOT EXISTS messages (
        message_id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message_text TEXT,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE
    )"
];

echo "<h3>Step 3: Creating Tables</h3>";
foreach ($tables as $name => $query) {
    if ($conn->query($query) === TRUE) {
        echo "<span style='color:green'>✓ $name </span> ";
    } else {
        echo "<br><span style='color:red'>✗ Error $name: " . $conn->error . "</span><br>";
    }
}

// 5. Seed Data (Admin & Products)
echo "<h3>Step 4: Seeding Data</h3>";

// Admin
$adminEmail = "krishnavenirnair2005@gmail.com";
$check = $conn->query("SELECT * FROM users WHERE email='$adminEmail'");
if ($check->num_rows == 0) {
    $pass = password_hash("Ambadi@2005", PASSWORD_DEFAULT);
    $adminSql = "INSERT INTO users (first_name, last_name, email, password_hash, role, is_email_verified) 
                 VALUES ('Krishnaraj', 'R Nair', '$adminEmail', '$pass', 'admin', 1)";
    if($conn->query($adminSql)) echo "✓ Admin Account Created.<br>";
} else {
    echo "✓ Admin Account Exists.<br>";
}

// Equipment Seed
$eqCheck = $conn->query("SELECT COUNT(*) as c FROM gym_equipment");
$eqRow = $eqCheck->fetch_assoc();
if ($eqRow['c'] == 0) {
    $conn->query("INSERT INTO gym_equipment (name, total_units, available_units, status, icon, color_class) VALUES
    ('Treadmills', 12, 8, 'High Availability', 'fas fa-running', 'success'),
    ('Free Weights', 20, 4, 'Busy Session', 'fas fa-dumbbell', 'warning'),
    ('Bench Press', 5, 0, 'Full Capacity', 'fas fa-weight-hanging', 'danger'),
    ('Squat Racks', 4, 2, 'Moderate', 'fas fa-child', 'warning')");
    echo "✓ Equipment Data Seeded.<br>";
}

// Products Seed (Minimal)
$prodCheck = $conn->query("SELECT COUNT(*) as c FROM products");
$prodRow = $prodCheck->fetch_assoc();
if ($prodRow['c'] == 0) {
    // Simply reading the file content if available, else manual insert
    if (file_exists('update_products.sql')) {
        $sqlProd = file_get_contents('update_products.sql');
        // Rough split
        $parts = explode('INSERT', $sqlProd);
        if(count($parts) > 1) {
            $finalSql = "INSERT " . $parts[1]; 
            // Trim semicolon if multiple statements
            $finalSql = explode(';', $finalSql)[0];
            $conn->query($finalSql);
            echo "✓ Products Data Imported.<br>";
        }
    }
}

echo "<hr><h1>SUCCESS! Website Restored.</h1>";
echo "<a href='home.php' style='background:green; color:white; padding:15px; text-decoration:none; border-radius:5px;'>Go to Website Home</a>";

$conn->close();
?>
