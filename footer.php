<?php
if (
    isset($_REQUEST['embed']) || 
    strpos($_SERVER['REQUEST_URI'] ?? '', 'embed') !== false ||
    strpos($_SERVER['QUERY_STRING'] ?? '', 'embed') !== false ||
    (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'iframe')
) {
    return;
}
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
    .radial-menu {
        position: relative;
        width: 40px;
        height: 40px;
    }
    .share-btn {
        width: 40px;
        height: 40px;
        background: #222;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        z-index: 101;
    }
    .radial-menu.active .share-btn {
        background: #4FACFE;
        transform: rotate(360deg);
    }
    .share-icon {
        position: absolute;
        top: 0;
        left: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white !important;
        text-decoration: none;
        opacity: 0;
        transform: translate(0, 0) scale(0.5);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        z-index: 100;
    }
    .radial-menu.active .share-icon {
        opacity: 1;
    }
    .radial-menu.active .share-icon:nth-child(1) { /* Facebook */
        transform: translate(65px, 0px) scale(1);
    }
    .radial-menu.active .share-icon:nth-child(2) { /* WhatsApp */
        transform: translate(45px, 45px) scale(1);
    }
    .radial-menu.active .share-icon:nth-child(3) { /* Telegram */
        transform: translate(0px, 65px) scale(1);
    }
</style>
<footer class="footer">
    <div class="container">
        <div class="footer-top" style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 60px; padding-bottom: 60px;">
            <div>
                <div class="f-logo" style="font-size: 32px; font-weight: 900; margin-bottom: 25px; color: white;">FitNova</div>
                <p class="f-desc" style="color: #999; font-size: 1rem; margin-bottom: 30px; line-height: 1.6; max-width: 350px;">A unified digital ecosystem for your health and wellness journey.</p>
                <div class="f-socials" style="display: flex; gap: 15px; position: relative;">
                    <div id="radialMenu" class="radial-menu">
                        <a href="#" class="share-icon" onclick="shareSocial('facebook')" style="background: #3b5998 !important;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="share-icon" onclick="shareSocial('whatsapp')" style="background: #25D366 !important;"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="share-icon" onclick="shareSocial('telegram')" style="background: #0088cc !important;"><i class="fab fa-telegram-plane"></i></a>
                        <button id="shareBtn" class="share-btn"><i class="fas fa-share-alt"></i></button>
                    </div>
                </div>
                <script>
                    document.getElementById('shareBtn').addEventListener('click', function(e) {
                        e.preventDefault();
                        document.getElementById('radialMenu').classList.toggle('active');
                    });
                    
                    function shareSocial(platform) {
                        var url = encodeURIComponent('https://tinyurl.com/2nna9j7w');
                        var text = encodeURIComponent('Hey, do you want to make your body fit? Join with us!');
                        var shareUrl = '';
                        if (platform === 'facebook') {
                            shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + url;
                        } else if (platform === 'whatsapp') {
                            shareUrl = 'https://api.whatsapp.com/send?text=' + text + ' ' + url;
                        } else if (platform === 'telegram') {
                            shareUrl = 'https://t.me/share/url?url=' + url + '&text=' + text;
                        }
                        window.open(shareUrl, '_blank', 'width=600,height=400');
                    }
                </script>
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
                    <li style="margin-bottom: 18px;"><a href="#" onclick="if(typeof handleTalkToExperts === 'function') handleTalkToExperts(event); else { event.preventDefault(); alert('Please open Talk with Experts from the navigation menu.'); }" style="color: #999;">Contact Us</a></li>
                    <li style="margin-bottom: 18px;"><a href="privacy_policy.php" style="color: #999;">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
