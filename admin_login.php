<?php
session_start();
// If already logged in as admin, redirect
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0F2C59 0%, #164282 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
        }
        .logo-section { text-align: center; margin-bottom: 40px; }
        .logo-section h1 {
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, #0F2C59 0%, #164282 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        .admin-badge {
            display: inline-block;
            background: #0F2C59;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; color: #333; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        .input-wrapper input {
            width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 12px; font-size: 15px;
        }
        .login-btn {
            width: 100%; padding: 16px; background: #0F2C59; color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;
        }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(15, 44, 89, 0.3); }
        .error-message { background: #fee; color: #c33; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; display: none; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <h1>FitNova</h1>
            <p>Admin Portal</p>
            <span class="admin-badge">🔒 SECURE ACCESS</span>
        </div>

        <div id="errorMessage" class="error-message"></div>

        <form id="adminLoginForm">
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" required placeholder="admin@fitnova.com">
                </div>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" required placeholder="Username">
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
            </div>
            <button type="submit" class="login-btn">Access Admin Dashboard</button>
        </form>
    </div>

    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const submitBtn = document.querySelector('.login-btn');
            
            submitBtn.textContent = 'Verifying...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('admin_auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, username, password })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = 'admin_dashboard.php';
                } else {
                    const err = document.getElementById('errorMessage');
                    err.textContent = data.message || 'Invalid credentials';
                    err.style.display = 'block';
                    submitBtn.textContent = 'Access Admin Dashboard';
                    submitBtn.disabled = false;
                }
            } catch (error) {
                alert('An error occurred.');
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
