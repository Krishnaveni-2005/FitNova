<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? 'free';
    switch ($role) {
        case "admin": header("Location: admin_dashboard.php"); break;
        case "trainer": header("Location: trainer_dashboard.php"); break;
        case "pro": header("Location: prouser_dashboard.php"); break;
        case "lite": header("Location: liteuser_dashboard.php"); break;
        default: header("Location: freeuser_dashboard.php"); break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FitNova</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            background: white;
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 900;
            color: var(--primary-color);
            margin-bottom: 30px;
            display: inline-block;
            text-decoration: none;
        }

        .logo span {
            color: var(--primary-color);
        }

        h2 {
            font-family: 'Outfit', sans-serif;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        p.subtitle {
            color: var(--text-light);
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(15, 44, 89, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: #0a1f3f;
            transform: translateY(-2px);
        }

        .links {
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .links a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .divider {
            margin: 25px 0;
            position: relative;
            text-align: center;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 1px;
            background-color: #ddd;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: var(--text-light);
            font-size: 0.85rem;
        }

        .social-login {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .social-btn {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            color: var(--text-color);
        }

        .social-btn:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <style>
        .simple-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 15px 40px;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .header-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .header-menu a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }
        .header-menu a:hover {
            color: var(--primary-color);
        }
        
        body { padding-top: 80px; } /* Space for header */
    </style>

    <header class="simple-header">
        <a href="home.php" class="header-logo"><i class="fas fa-dumbbell"></i> Fit<span style="color: var(--primary-color);">Nova</span></a>
        <nav class="header-menu">
            <a href="home.php"><i class="fas fa-home"></i> Home</a>
        </nav>
    </header>

    <div class="login-container">
        <!-- Logo removed from inside container since it's in header now, but user might want it both places? 
             User said "header with home like needed menus". 
             Let's keep the logo inside the form as "Welcome" text context but maybe smaller or just remove it if it's redundant.
             Actually common UX is to have header logo + form logo or just header logo. 
             The existing code has a logo inside .login-container. I will keep it but maybe user wants it removed?
             "add header with home like needed menus only".
             I will keep the internal logo as it serves as the brand anchor for the form. -->
        <a href="home.php" class="logo"><i class="fas fa-dumbbell"></i> Fit<span>Nova</span></a>
        <h2>Welcome Back</h2>
        <p class="subtitle">Enter your details to access your account</p>

        <form id="loginForm">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" class="form-input" required>
                <div style="text-align: right; margin-top: 5px;">
                    <a href="forgot_password.php"
                        style="font-size: 0.85rem; color: var(--text-light); text-decoration: none;">Forgot
                        Password?</a>
                </div>
            </div>

            <button type="submit" class="btn-login">Log In</button>
        </form>

        <div class="divider">
            <span>OR CONTINUE WITH</span>
        </div>

        <div class="social-login">
            <button type="button" class="social-btn" id="customGoogleSignInBtn">
                <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google"
                    style="width: 20px;">
                Sign in with Google
            </button>
        </div>

        <div class="links">
            Don't have an account? <a href="signup.php">Sign up</a>
        </div>
    </div>

    <!-- Google Library -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            const password = this.querySelector('input[type="password"]').value;
            const btn = this.querySelector('.btn-login');

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            btn.disabled = true;

            fetch('login_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message);
                        btn.innerHTML = 'Log In';
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    btn.innerHTML = 'Log In';
                    btn.disabled = false;
                });
        });

        let tokenClient;

        function initGoogleAuth() {
            if (typeof google === "undefined") {
                setTimeout(initGoogleAuth, 100);
                return;
            }

            tokenClient = google.accounts.oauth2.initTokenClient({
                client_id: '631731614532-4ia642th7cd76bm4u15qlsrkcjm50l3a.apps.googleusercontent.com',
                scope: 'openid email profile',
                prompt: 'select_account',
                callback: (response) => {
                    if (response.access_token) {
                        fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
                            headers: { Authorization: `Bearer ${response.access_token}` }
                        })
                            .then(res => res.json())
                            .then(user => handleGoogleUserData(user))
                            .catch(err => alert("Failed to fetch user info from Google."));
                    }
                },
            });

            document.getElementById('customGoogleSignInBtn').onclick = () => {
                tokenClient.requestAccessToken();
            };
        }

        initGoogleAuth();

        function handleGoogleUserData(user) {
            const btn = document.getElementById('customGoogleSignInBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';

            fetch('signup_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'google_auth',
                    source: 'login',
                    firstName: user.given_name,
                    lastName: user.family_name,
                    email: user.email,
                    emailVerified: user.email_verified,
                    sub: user.sub,
                    picture: user.picture
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = data.redirect;
                    } else {
                        alert('Google Login Failed: ' + data.message);
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Connection error during Google authentication.');
                    btn.innerHTML = originalText;
                });
        }
    </script>
</body>

</html>
