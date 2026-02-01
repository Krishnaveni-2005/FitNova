<?php
session_start();
require 'db_connect.php';

$showPhone = false;
$expertPhone = "9495868854";
$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    
    // Validate inputs
    if (empty($name) || empty($phone) || empty($email) || empty($reason)) {
        $errorMessage = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid email address!";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errorMessage = "Please enter a valid 10-digit phone number!";
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO expert_enquiries (name, phone, email, reason) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $phone, $email, $reason);
        
        if ($stmt->execute()) {
            $showPhone = true;
            $successMessage = "Thank you! Your enquiry has been submitted successfully.";
        } else {
            $errorMessage = "Error submitting enquiry. Please try again.";
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk with Experts - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #4FACFE;
            --success-color: #2ECC71;
            --error-color: #E74C3C;
            --text-dark: #1A1A1A;
            --text-light: #555;
            --bg-light: #F8F9FA;
            --white: #FFFFFF;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); }
        
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a4d8f 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 800;
        }
        
        .hero p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .container {
            max-width: 600px;
            margin: -50px auto 60px;
            padding: 0 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-size: 0.95rem;
        }
        
        .form-group label .required {
            color: var(--error-color);
            margin-left: 3px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Outfit', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(79, 172, 254, 0.1);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn-submit {
            width: 100%;
            background: var(--primary-color);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-submit:hover {
            background: #0a1f40;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(15, 44, 89, 0.3);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-error {
            background: #fee;
            color: var(--error-color);
            border: 1px solid var(--error-color);
        }
        
        .alert-success {
            background: #efffef;
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }
        
        .phone-display {
            background: linear-gradient(135deg, var(--success-color) 0%, #27ae60 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-top: 30px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.3);
        }
        
        .phone-display h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .phone-number {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 2px;
            margin: 15px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .phone-icon {
            font-size: 2.5rem;
            animation: ring 2s ease-in-out infinite;
        }
        
        @keyframes ring {
            0%, 100% { transform: rotate(0deg); }
            10%, 30% { transform: rotate(-10deg); }
            20%, 40% { transform: rotate(10deg); }
        }
        
        .call-btn {
            display: inline-block;
            background: white;
            color: var(--success-color);
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            margin-top: 15px;
            transition: all 0.3s ease;
        }
        
        .call-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .info-text {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="hero">
        <h1><i class="fas fa-headset"></i> Talk with Experts</h1>
        <p>Get personalized guidance from our fitness experts. Fill out the form below and we'll connect you!</p>
    </div>
    
    <div class="container">
        <div class="form-card">
            <?php if (!$showPhone): ?>
                <h2 style="margin-bottom: 25px; color: var(--primary-color); font-size: 1.5rem;">
                    <i class="fas fa-clipboard-list"></i> Enquiry Form
                </h2>
                
                <?php if ($errorMessage): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" 
                               placeholder="Enter your full name" required 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               placeholder="10-digit mobile number" required pattern="[0-9]{10}"
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="your.email@example.com" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Reason to Call <span class="required">*</span></label>
                        <textarea id="reason" name="reason" class="form-control" 
                                  placeholder="Please describe what you'd like to discuss with our expert..." 
                                  required><?php echo htmlspecialchars($_POST['reason'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Submit Enquiry
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
                
                <div class="phone-display">
                    <i class="fas fa-phone-alt phone-icon"></i>
                    <h3>Call Our Expert Now!</h3>
                    <div class="phone-number">
                        <i class="fas fa-phone"></i>
                        <?php echo htmlspecialchars($expertPhone); ?>
                    </div>
                    <a href="tel:<?php echo htmlspecialchars($expertPhone); ?>" class="call-btn">
                        <i class="fas fa-phone-volume"></i> Tap to Call
                    </a>
                    <p class="info-text">Our expert is available to assist you with your fitness journey!</p>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="talk_with_experts.php" style="color: var(--accent-color); text-decoration: none; font-weight: 600;">
                        <i class="fas fa-arrow-left"></i> Submit Another Enquiry
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>
