<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - FitNova</title>
    <!-- Fonts -->
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

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
        }
        .container {
            width: 100%; max-width: 450px; background: white; padding: 40px; border-radius: var(--border-radius); box-shadow: var(--shadow); text-align: center;
        }
        .logo { font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 900; color: var(--primary-color); margin-bottom: 30px; display: inline-block; text-decoration: none; }
        h2 { font-family: 'Outfit', sans-serif; margin-bottom: 10px; color: var(--primary-color); }
        p.subtitle { color: var(--text-light); margin-bottom: 30px; font-size: 0.95rem; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.9rem; }
        .form-input { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
        .btn-reset {
            width: 100%; padding: 14px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: var(--transition);
        }
        .btn-reset:hover { background-color: #0a1f3f; transform: translateY(-2px); }
        .success-message { display: none; }
    </style>
</head>

<body>

    <div class="container" id="resetContainer">
        <a href="home.php" class="logo"><i class="fas fa-dumbbell"></i> FitNova</a>
        <h2>Set New Password</h2>
        <p class="subtitle">Please enter your new password below.</p>

        <form id="resetForm">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" id="password" class="form-input" placeholder="Enter new password" required minlength="8">
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" id="confirmPassword" class="form-input" placeholder="Confirm new password" required>
            </div>
            <button type="submit" class="btn-reset" id="submitBtn">Update Password</button>
        </form>
    </div>

    <div class="container success-message" id="successBlock">
        <a href="home.php" class="logo"><i class="fas fa-dumbbell"></i> FitNova</a>
        <div style="font-size: 3rem; color: #28a745; margin-bottom: 20px;"><i class="fas fa-check-circle"></i></div>
        <h2>Password Updated!</h2>
        <p class="subtitle">Your password has been successfully reset.</p>
        <div style="margin-top: 20px;"><a href="login.php" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">Back to Login</a></div>
    </div>

    <script>
        window.onload = function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.get('email') || !urlParams.get('otp')) {
                alert("Missing parameters.");
                window.location.href = 'forgot_password.php';
            }
        };

        document.getElementById('resetForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirmPassword').value;
            const params = new URLSearchParams(window.location.search);
            const email = params.get('email');
            const otp = params.get('otp');

            if (password !== confirm) { alert("Passwords do not match!"); return; }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;

            fetch('reset_password_final_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, otp, password })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('resetContainer').style.display = 'none';
                    document.getElementById('successBlock').style.display = 'block';
                } else {
                    alert(data.message);
                    submitBtn.innerHTML = 'Update Password';
                    submitBtn.disabled = false;
                }
            })
            .catch(err => {
                alert('An error occurred.');
                submitBtn.disabled = false;
            });
        });
    </script>
</body>

</html>
