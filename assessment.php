<?php session_start(); include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Profile - FitNova</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --secondary-color: #DAC0A3;
            --accent-color: #E63946;
            --bg-color: #F8F9FA;
            --text-color: #333333;
            --text-light: #6C757D;
            --border-radius: 12px;
            --shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
        }

        .setup-container {
            width: 100%;
            max-width: 800px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin: 40px auto;
            overflow: hidden;
        }

        .setup-header {
            background-color: var(--primary-color);
            padding: 40px;
            text-align: center;
            color: white;
        }

        .setup-header h1 {
            font-family: 'Outfit', sans-serif;
            margin-bottom: 10px;
        }

        .setup-header p {
            opacity: 0.8;
        }

        .setup-body {
            padding: 40px;
        }

        .form-section {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 30px;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(15, 44, 89, 0.1);
        }

        .option-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .option-card {
            position: relative;
        }

        .option-card input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .option-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            height: 100%;
        }

        .option-card input:checked+.option-content {
            border-color: var(--secondary-color);
            background-color: rgba(218, 192, 163, 0.1);
            color: var(--primary-color);
        }

        .option-icon {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--text-light);
        }

        .option-card input:checked+.option-content .option-icon {
            color: var(--accent-color);
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 10px 20px rgba(230, 57, 70, 0.2);
        }

        .btn-submit:hover {
            background-color: #d62828;
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(230, 57, 70, 0.3);
        }

        @media (max-width: 768px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>

    <div class="setup-container">
        <div class="setup-header">
            <h1>Let's Build Your Plan</h1>
            <p>Tell us a bit about yourself so we can personalize your FitNova experience.</p>
        </div>

        <form class="setup-body" action="freeuser_dashboard.php">
            <!-- Basic Metrics -->
            <div class="form-section">
                <h3 class="section-title"><i class="fas fa-ruler-combined"></i> Body Metrics</h3>
                <div class="grid-3">
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Age</label>
                        <input type="number" class="form-input" name="age" placeholder="25" min="10" max="100" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Height (cm)</label>
                        <input type="number" class="form-input" name="height" placeholder="175" min="100" max="250"
                            required>
                    </div>
                </div>
                <!-- ... other fields ... -->
            </div>

            <!-- Health History Example -->
            <div class="form-section">
                <h3 class="section-title"><i class="fas fa-notes-medical"></i> Medical History</h3>
                <div class="option-grid">
                    <label class="option-card">
                        <input type="checkbox" name="health[]" value="none">
                        <div class="option-content">
                            <span>None of the Above</span>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-submit">Generate My Plan <i class="fas fa-arrow-right"></i></button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
    <script>
        // Form handling logic...
    </script>
</body>

</html>
