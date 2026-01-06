<?php
// footer.php
?>
<style>
    .footer {
        background: #111;
        color: white;
        padding: 80px 0 30px;
        font-family: 'Outfit', sans-serif;
    }

    .footer .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-top {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr;
        gap: 60px;
        padding-bottom: 60px;
        border-bottom: 1px solid #333;
    }

    .f-logo {
        font-size: 32px;
        font-weight: 900;
        margin-bottom: 25px;
        color: white;
    }

    .f-desc {
        color: #999;
        font-size: 1rem;
        margin-bottom: 30px;
        line-height: 1.6;
        max-width: 350px;
    }

    .f-socials {
        display: flex;
        gap: 15px;
    }

    .f-socials a {
        width: 40px;
        height: 40px;
        background: #222 !important;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        color: white !important;
        text-decoration: none;
    }

    .f-socials a:hover {
        background: #4FACFE !important;
        transform: translateY(-3px);
    }

    .footer h4 {
        margin-bottom: 30px;
        font-size: 1.2rem;
        font-weight: 700;
        color: white;
    }

    .f-links {
        list-style: none;
        padding: 0;
    }

    .f-links li {
        margin-bottom: 18px;
    }

    .f-links a {
        color: #999;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .f-links a:hover {
        color: white;
        padding-left: 5px;
    }

    .footer-bottom {
        padding-top: 40px;
        text-align: center;
        color: #666;
        font-size: 0.9rem;
    }

    @media (max-width: 992px) {
        .footer-top {
            grid-template-columns: 1fr;
            text-align: center;
            gap: 40px;
        }
        .f-desc {
            margin-left: auto;
            margin-right: auto;
        }
        .f-socials {
            justify-content: center;
        }
    }
</style>
<footer class="footer">
    <div class="container">
        <div class="footer-top" style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 60px; padding-bottom: 60px;">
            <div>
                <div class="f-logo" style="font-size: 32px; font-weight: 900; margin-bottom: 25px; color: white;">FitNova</div>
                <p class="f-desc" style="color: #999; font-size: 1rem; margin-bottom: 30px; line-height: 1.6; max-width: 350px;">A unified digital ecosystem for your health and wellness journey.</p>
                <div class="f-socials" style="display: flex; gap: 15px;">
                    <a href="#" style="width: 40px; height: 40px; background: #222; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" style="width: 40px; height: 40px; background: #222; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="width: 40px; height: 40px; background: #222; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div>
                <h4 style="margin-bottom: 30px; font-size: 1.2rem; font-weight: 700; color: white;">Platform</h4>
                <ul class="f-links" style="list-style: none;">
                    <li style="margin-bottom: 18px;"><a href="home.php" style="color: #999;">Home</a></li>
                    <li style="margin-bottom: 18px;"><a href="gym.php" style="color: #999;">Gym Status</a></li>
                    <li style="margin-bottom: 18px;"><a href="fitshop.php" style="color: #999;">Fitshop</a></li>
                    <li style="margin-bottom: 18px;"><a href="subscription_plans.php" style="color: #999;">Plans</a></li>
                </ul>
            </div>
            <div>
                <h4 style="margin-bottom: 30px; font-size: 1.2rem; font-weight: 700; color: white;">Support</h4>
                <ul class="f-links" style="list-style: none;">
                    <li style="margin-bottom: 18px;"><a href="#" style="color: #999;">Contact Us</a></li>
                    <li style="margin-bottom: 18px;"><a href="#" style="color: #999;">Privacy Policy</a></li>
                    <li style="margin-bottom: 18px;"><a href="admin_login.php" style="color: #999;">Admin Portal</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom" style="text-align: center; color: #666; font-size: 0.9rem; padding-top: 30px; border-top: 1px solid #333; margin-top: 40px;">
            &copy; <?php echo date('Y'); ?> FitNova. All rights reserved. Registered PHP/MySQL Backend.
        </div>
    </div>
</footer>
