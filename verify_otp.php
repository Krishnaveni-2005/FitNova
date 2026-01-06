<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - FitNova</title>
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

        .container {
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

        .otp-input-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 25px;
        }

        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 1.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }

        .otp-input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .btn-verify {
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
        }

        .btn-verify:hover {
            background-color: #0a1f3f;
            transform: translateY(-2px);
        }

        .timer {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-top: 20px;
        }

        .resend-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .disabled {
            color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="home.php" class="logo"><i class="fas fa-dumbbell"></i> Fit<span>Nova</span></a>
        <h2>Verify OTP</h2>
        <p class="subtitle">Enter the 6-digit code sent to <span id="userEmail" style="font-weight: 600;"></span></p>

        <form id="otpForm">
            <div class="otp-input-group">
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" required>
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" required>
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" required>
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" required>
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" required>
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" required>
            </div>

            <button type="submit" class="btn-verify" id="verifyBtn">Verify Code</button>
        </form>

        <p class="timer">Resend code in <span id="time">01:00</span></p>
        <a href="#" id="resendBtn" class="resend-link disabled">Resend Code</a>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const email = urlParams.get('email');
        if (email) document.getElementById('userEmail').innerText = email;

        const inputs = document.querySelectorAll('.otp-input');
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        // Timer
        let time = 60;
        const timerEl = document.getElementById('time');
        const resendBtn = document.getElementById('resendBtn');
        const timerInterval = setInterval(() => {
            time--;
            const mins = Math.floor(time / 60).toString().padStart(2, '0');
            const secs = (time % 60).toString().padStart(2, '0');
            timerEl.innerText = `${mins}:${secs}`;
            if (time <= 0) {
                clearInterval(timerInterval);
                resendBtn.classList.remove('disabled');
                timerEl.parentElement.style.display = 'none';
            }
        }, 1000);

        document.getElementById('otpForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const otp = Array.from(inputs).map(i => i.value).join('');
            if (otp.length !== 6) {
                alert('Please enter a 6-digit OTP');
                return;
            }

            const verifyBtn = document.getElementById('verifyBtn');
            verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
            verifyBtn.disabled = true;

            fetch('verify_otp_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, otp: otp })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message);
                        verifyBtn.innerHTML = 'Verify Code';
                        verifyBtn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An error occurred.');
                    verifyBtn.innerHTML = 'Verify Code';
                    verifyBtn.disabled = false;
                });
        });

        resendBtn.addEventListener('click', function (e) {
            if (this.classList.contains('disabled')) return;
            // Logic to resend OTP could be added here similar to forgot_password_handler.php
            alert('Resend functionality to be implemented or call forgot_password_handler.php again');
        });
    </script>
</body>

</html>