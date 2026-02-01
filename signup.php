<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? 'free';
    switch ($role) {
        case "admin": header("Location: admin_dashboard.php"); break;
        case "trainer": header("Location: trainer_dashboard.php"); break;
        case "pro": header("Location: eliteuser_dashboard.php"); break;
        case "lite": header("Location: prouser_dashboard.php"); break;
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
    <title>Sign Up - FitNova</title>
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
            --text-color: #333333;
            --text-light: #6C757D;
            --light-color: #F8F9FA;
            --light-gray: #E9ECEF;
            --gray: #ADB5BD;
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: white;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }

        /* Left Branding Section */
        .signup-left {
            flex: 1.2;
            background-color: #0F2C59;
            color: white;
            padding: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 36px;
            margin-bottom: 60px;
            color: white;
            text-decoration: none;
        }

        .logo span {
            color: #DAC0A3;
        }

        .signup-left h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 48px;
            margin-bottom: 25px;
            line-height: 1.1;
        }

        .signup-left p.desc {
            font-size: 1.1rem;
            opacity: 0.8;
            margin-bottom: 50px;
            max-width: 500px;
        }

        .benefits {
            list-style: none;
        }

        .benefit-item {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            align-items: flex-start;
        }

        .benefit-item i {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-top: 3px;
        }

        .benefit-item h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .benefit-item p {
            font-size: 0.95rem;
            opacity: 0.7;
        }

        /* Right Form Section */
        .signup-right {
            flex: 1;
            padding: 60px 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        .signup-container {
            width: 100%;
            max-width: 550px;
            margin: 0 auto;
        }

        .signup-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .signup-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 36px;
            color: #0F2C59;
            margin-bottom: 10px;
        }

        .signup-header p {
            color: var(--text-light);
            font-size: 1rem;
        }

        .signup-header a {
            color: #E63946;
            text-decoration: none;
            font-weight: 600;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            flex: 1;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            font-size: 0.95rem;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 14px 20px;
            border: 1px solid #F0F0F0;
            border-radius: 10px;
            font-size: 1rem;
            background-color: #FAFAFA;
            transition: var(--transition);
        }

        .form-control::placeholder {
            color: #AAA;
        }

        .form-control:focus {
            outline: none;
            border-color: #0F2C59;
            background-color: white;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #AAA;
            font-size: 0.9rem;
        }

        .terms {
            display: flex;
            gap: 12px;
            margin: 30px 0;
            font-size: 0.9rem;
            color: #666;
            line-height: 1.5;
        }

        .terms input {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            cursor: pointer;
        }

        .terms a {
            color: #0F2C59;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background-color: #0F2C59;
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 30px;
        }

        .btn-submit:hover {
            background-color: #0a1f3f;
            transform: translateY(-2px);
        }

        .divider {
            position: relative;
            text-align: center;
            margin-bottom: 30px;
        }

        .divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 1px;
            background-color: #EEE;
        }

        .divider span {
            background-color: white;
            padding: 0 15px;
            color: #888;
            font-size: 0.9rem;
            position: relative;
        }

        .social-row {
            display: flex;
            gap: 20px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border: 1px solid #EEE;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 600;
            cursor: pointer;
            color: #333;
            transition: var(--transition);
        }

        .social-btn:hover {
            background-color: #F9F9F9;
        }

        .social-btn img {
            width: 22px;
        }

        .error-message {
            color: var(--accent-color);
            font-size: 0.8rem;
            margin-top: 5px;
            display: none;
        }

        .form-group.invalid .error-message {
            display: block;
        }

        .form-group.invalid .form-control {
            border-color: var(--accent-color);
        }

        .success-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.98);
            display: none;
            align-items: center;
            justify-content: center;
            text-align: center;
            z-index: 100;
        }

        .success-overlay i {
            font-size: 5rem;
            color: #2ECC71;
            margin-bottom: 20px;
        }

        @media (max-width: 1024px) {
            body {
                flex-direction: column;
            }

            .signup-left {
                padding: 40px;
            }

            .signup-right {
                padding: 40px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="signup-left">
        <a href="home.php" class="logo">Fit<span>Nova</span></a>
        <h1>Join the Future of Fitness</h1>
        <p class="desc">Start your wellness journey today with personalized coaching, expert nutrition plans, and a
            community that supports you.</p>

        <ul class="benefits">
            <li class="benefit-item">
                <i class="fas fa-check"></i>
                <div>
                    <h3>Expert Guidance</h3>
                    <p>Connect with certified trainers and nutritionists.</p>
                </div>
            </li>
            <li class="benefit-item">
                <i class="fas fa-check"></i>
                <div>
                    <h3>24/7 Support</h3>
                    <p>Access our community and expert coaches anytime, anywhere.</p>
                </div>
            </li>
            <li class="benefit-item">
                <i class="fas fa-check"></i>
                <div>
                    <h3>Track Your Progress</h3>
                    <p>Monitor your fitness journey with our advanced tracking tools.</p>
                </div>
            </li>
        </ul>
    </div>

    <div class="signup-right">
        <div class="signup-container">
            <div class="signup-header">
                <h2>Create Your Account</h2>
                <p>Already have an account? <a href="login.php">Log In</a></p>
            </div>

            <form id="signupForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" id="firstName" class="form-control" placeholder="Enter your first name"
                            required>
                        <div class="error-message">Enter a valid name (letters only)</div>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" id="lastName" class="form-control" placeholder="Enter your last name"
                            required>
                        <div class="error-message">Enter a valid name (letters only)</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="email" class="form-control" placeholder="Enter your email address" required>
                    <div class="error-message">Please enter a valid email</div>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" id="phone" class="form-control" placeholder="Enter your 10-digit phone number"
                        required pattern="\d{10}" maxlength="10">
                    <div class="error-message">Phone number must be exactly 10 digits</div>
                </div>

                <!-- Trainer Registration Section -->
                <div class="form-group">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                        <input type="checkbox" id="isTrainer" style="width: 18px; height: 18px; cursor: pointer;">
                        <label for="isTrainer" style="margin: 0; cursor: pointer;">Apply as a Trainer</label>
                    </div>
                </div>

                <div id="trainerFields" style="display: none;">
                    <div class="form-group">
                        <label>Trainer Type</label>
                        <div style="display: flex; gap: 20px; align-items: center; padding: 10px 0;">
                            <label style="font-weight: normal; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="trainerType" value="online" checked style="width: 18px; height: 18px;"> 
                                Online Trainer
                            </label>
                            <label style="font-weight: normal; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="trainerType" value="offline" style="width: 18px; height: 18px;"> 
                                Offline Trainer
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Specialization</label>
                        <select id="trainerSpecialization" class="form-control">
                            <option value="">Select Specialization</option>
                            <option value="Weight Loss">Weight Loss</option>
                            <option value="Muscle Building">Muscle Building</option>
                            <option value="Yoga & Flexibility">Yoga & Flexibility</option>
                            <option value="Cardio & Endurance">Cardio & Endurance</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="error-message">Please select a specialization</div>
                    </div>

                    <div class="form-group">
                        <label>Experience (Years)</label>
                        <input type="number" id="trainerExperience" class="form-control" placeholder="Years of experience" min="0" max="50">
                        <div class="error-message">Please enter valid experience</div>
                    </div>

                    <div class="form-group">
                        <label>Certification (Upload File)</label>
                        <input type="file" id="trainerCertification" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="error-message">Please upload your certification (PDF/JPG/PNG)</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="password-container">
                        <input type="password" id="password" class="form-control" placeholder="Create a password"
                            required>
                        <i class="fas fa-eye-slash toggle-password" onclick="togglePassword('password')"></i>
                    </div>
                    <div class="error-message">Min 8 chars, 1 uppercase, 1 number</div>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="password-container">
                        <input type="password" id="confirmPassword" class="form-control"
                            placeholder="Confirm your password" required>
                        <i class="fas fa-eye-slash toggle-password" onclick="togglePassword('confirmPassword')"></i>
                    </div>
                    <div class="error-message">Passwords do not match</div>
                </div>

                <div class="terms">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy
                            Policy</a>. I understand that my data will be processed in accordance with FitNova's
                        policies.</label>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">Create Account</button>

                <div class="divider">
                    <span>Or sign up with</span>
                </div>

                <div class="social-container" style="width: 100%;">
                    <button type="button" class="social-btn" id="googleBtn" style="width: 100%;">
                        <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google"
                            style="width: 20px;">
                        Signup with Google
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="success-overlay" id="successOverlay">
        <div>
            <i class="fas fa-check-circle"></i>
            <h2>Account Created!</h2>
            <p>Welcome to FitNova. Redirecting to login...</p>
        </div>
    </div>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = event.target;
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("signupForm");
            const submitBtn = document.getElementById("submitBtn");
            const overlay = document.getElementById("successOverlay");

            const inputs = {
                firstName: {
                    el: document.getElementById("firstName"),
                    validate: v => /^[a-zA-Z\s]{2,50}$/.test(v.trim())
                },
                lastName: {
                    el: document.getElementById("lastName"),
                    validate: v => /^[a-zA-Z\s]{1,50}$/.test(v.trim())
                },
                email: {
                    el: document.getElementById("email"),
                    validate: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)
                },
                phone: {
                    el: document.getElementById("phone"),
                    validate: v => /^\d{10}$/.test(v.trim())
                },
                password: {
                    el: document.getElementById("password"),
                    validate: v => /^(?=.*[A-Z])(?=.*\d).{8,}$/.test(v)
                },
                confirmPassword: {
                    el: document.getElementById("confirmPassword"),
                    validate: v => v === document.getElementById("password").value && v !== ""
                },
                trainerSpecialization: {
                    el: document.getElementById("trainerSpecialization"),
                    validate: v => !document.getElementById("isTrainer").checked || v !== ""
                },
                trainerExperience: {
                    el: document.getElementById("trainerExperience"),
                    validate: v => !document.getElementById("isTrainer").checked || (v !== "" && !isNaN(v) && v >= 0)
                },
                trainerCertification: {
                    el: document.getElementById("trainerCertification"),
                    validate: v => !document.getElementById("isTrainer").checked || document.getElementById("trainerCertification").files.length > 0
                }
            };

            // Toggle Trainer Fields
            const trainerCheckbox = document.getElementById("isTrainer");
            const trainerFields = document.getElementById("trainerFields");
            
            trainerCheckbox.addEventListener("change", function() {
                if (this.checked) {
                    trainerFields.style.display = "block";
                } else {
                    trainerFields.style.display = "none";
                }
            });

            const validateField = (key, force = false) => {
                const config = inputs[key];
                if (!config.el) return true;

                const group = config.el.closest(".form-group");
                const value = config.el.value;
                // Special check for file input
                if (config.el.type === 'file') {
                    const isValid = config.validate();
                    if (!isValid && (document.getElementById("isTrainer").checked || force)) {
                         group.classList.add("invalid");
                    } else {
                         group.classList.remove("invalid");
                    }
                    return isValid;
                }
                const isValid = config.validate(value);

                if (!isValid && (value.trim().length > 0 || force)) {
                    group.classList.add("invalid");
                } else {
                    group.classList.remove("invalid");
                }
                return isValid;
            };

            Object.keys(inputs).forEach(key => {
                const config = inputs[key];
                if (!config.el) return;

                config.el.addEventListener("input", () => {
                    validateField(key);
                    if (key === "password" && inputs.confirmPassword.el.value !== "") {
                        validateField("confirmPassword");
                    }
                });
            });

            form.addEventListener("submit", function (e) {
                e.preventDefault();

                let allValid = true;
                Object.keys(inputs).forEach(key => {
                    if (!validateField(key, true)) {
                        allValid = false;
                    }
                });

                if (!document.getElementById("terms").checked) {
                    alert("Please agree to the Terms of Service.");
                    return;
                }

                if (!allValid) return;

                submitBtn.innerHTML = "Creating Account...";
                submitBtn.disabled = true;

                submitBtn.innerHTML = "Creating Account...";
                submitBtn.disabled = true;

                const formData = new FormData();
                formData.append('firstName', inputs.firstName.el.value);
                formData.append('lastName', inputs.lastName.el.value);
                formData.append('email', inputs.email.el.value);
                formData.append('phone', inputs.phone.el.value);
                formData.append('password', inputs.password.el.value);
                
                const isTrainer = document.getElementById("isTrainer").checked;
                if (isTrainer) {
                    formData.append('isTrainer', '1');
                    formData.append('trainerType', document.querySelector('input[name="trainerType"]:checked').value);
                    formData.append('trainerSpecialization', inputs.trainerSpecialization.el.value);
                    formData.append('trainerExperience', inputs.trainerExperience.el.value);
                    // Append file
                    if (inputs.trainerCertification.el.files.length > 0) {
                        formData.append('trainerCertification', inputs.trainerCertification.el.files[0]);
                    }
                }

                fetch("signup_handler.php", {
                    method: "POST",
                    body: formData // No Content-Type header (browser sets it for FormData)
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            overlay.style.display = "flex";
                            setTimeout(() => window.location.href = data.redirect, 2000);
                        } else {
                            alert(data.message);
                            submitBtn.innerHTML = "Create Account";
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(err => {
                        console.error("Signup error:", err);
                        submitBtn.innerHTML = "Create Account";
                        submitBtn.disabled = false;
                    });
            });

            let tokenClient;
            function initGoogleAuth() {
                if (typeof google === "undefined") {
                    setTimeout(initGoogleAuth, 100);
                    return;
                }
                tokenClient = google.accounts.oauth2.initTokenClient({
                    client_id: "631731614532-4ia642th7cd76bm4u15qlsrkcjm50l3a.apps.googleusercontent.com",
                    scope: "openid email profile",
                    callback: (response) => {
                        if (response.access_token) {
                            fetch("https://www.googleapis.com/oauth2/v3/userinfo", {
                                headers: { Authorization: `Bearer ${response.access_token}` }
                            })
                                .then(res => res.json())
                                .then(user => handleGoogleUserData(user));
                        }
                    },
                });
                document.getElementById("googleBtn").onclick = () => tokenClient.requestAccessToken();
            }

            initGoogleAuth();

            function handleGoogleUserData(user) {
                const googleBtn = document.getElementById("googleBtn");
                const originalText = googleBtn.innerHTML;
                googleBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';

                fetch("signup_handler.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        action: "google_auth",
                        source: "signup",
                        firstName: user.given_name,
                        lastName: user.family_name,
                        email: user.email,
                        emailVerified: user.email_verified,
                        sub: user.sub
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            overlay.style.display = "flex";
                            setTimeout(() => window.location.href = data.redirect, 2000);
                        } else {
                            alert("Google Signup Failed: " + data.message);
                            googleBtn.innerHTML = originalText;
                        }
                    })
                    .catch(err => {
                        console.error("Google Auth error:", err);
                        alert("Connection error during Google signup.");
                        googleBtn.innerHTML = originalText;
                    });
            }
        });
    </script>
</body>

</html>
