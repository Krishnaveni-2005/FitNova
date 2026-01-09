<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? 'guest';
$userName = $_SESSION['user_name'] ?? 'User';

// Determine dashboard link based on role
$dashboardLink = 'login.php';
if ($isLoggedIn) {
    switch ($userRole) {
        case 'admin': $dashboardLink = 'admin_dashboard.php'; break;
        case 'trainer': $dashboardLink = 'trainer_dashboard.php'; break;
        case 'pro': $dashboardLink = 'prouser_dashboard.php'; break;
        case 'elite': $dashboardLink = 'eliteuser_dashboard.php'; break;
        default: $dashboardLink = 'freeuser_dashboard.php'; break;
    }
}
?>
<style>
    :root {
        --primary-color: #0F2C59;
        --accent-color: #E63946;
        --text-dark: #1A1A1A;
        --text-light: #555;
        --bg-light: #F8F9FA;
        --white: #FFFFFF;
        --transition: all 0.3s ease;
        --font-main: 'Outfit', sans-serif;
    }

    .navbar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 15px 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        width: 100%;
    }

    .nav-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        font-family: 'Outfit', sans-serif;
        font-size: 28px;
        font-weight: 900;
        color: var(--primary-color);
        letter-spacing: -0.5px;
        text-decoration: none;
    }

    .nav-links {
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .nav-link {
        font-family: 'Outfit', sans-serif;
        font-weight: 500;
        font-size: 0.95rem;
        color: var(--text-dark);
        transition: var(--transition);
        text-decoration: none;
        position: relative;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -4px;
        left: 0;
        background-color: var(--accent-color);
        transition: width 0.3s ease;
    }

    .nav-link:hover {
        color: var(--primary-color);
    }

    .nav-link:hover::after {
        width: 100%;
    }

    .btn-signup {
        font-family: 'Outfit', sans-serif;
        background: var(--primary-color);
        color: white;
        padding: 10px 25px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: var(--transition);
        text-decoration: none;
        display: inline-block;
    }

    .btn-signup:hover {
        background: #0a1f40;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(15, 44, 89, 0.3);
        color: white;
    }

    @media (max-width: 768px) {
        .nav-links {
            gap: 15px;
        }
        .nav-link {
            font-size: 0.85rem;
        }
    }
</style>
<!-- Nav -->
<nav class="navbar">
    <div class="container nav-container">
        <div class="logo"><a href="home.php" style="color: inherit;">FitNova</a></div>
        <div class="nav-links">
            <a href="home.php" class="nav-link">Home</a>
            <a href="gym.php" class="nav-link">Gym</a>
            <a href="fitshop.php" class="nav-link">Fitshop</a>
            <?php if ($isLoggedIn): ?>
                <?php if (in_array(basename($_SERVER['PHP_SELF']), ['fitshop.php', 'shop_checkout.php'])): ?>
                <a href="#" class="nav-link" id="ordersIcon" title="My Orders" style="margin-right: 15px;">
                    <i class="fas fa-clipboard-list" style="font-size: 1.2rem;"></i>
                </a>
                <a href="#" class="nav-link" id="cartIcon" style="position: relative;">
                    <i class="fas fa-shopping-cart" style="font-size: 1.2rem;"></i>
                    <span id="cartCount" style="position: absolute; top: -8px; right: -8px; background: var(--accent-color); color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 50%; display: none;">0</span>
                </a>
                <?php endif; ?>
                <a href="<?php echo $dashboardLink; ?>" class="nav-link">Dashboard</a>
                <?php if ((isset($isHomePage) && $isHomePage) || basename($_SERVER['PHP_SELF']) == 'home.php'): ?>
                    <a href="signup.php" class="btn-signup">Sign up</a>
                <?php else: ?>
                    <a href="logout.php" class="btn-signup" style="background: var(--accent-color);">Logout</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="#" onclick="handleTalkToExperts(event)" class="nav-link">Talk with Experts</a>
                <a href="signup.php" class="btn-signup">Sign up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    // Cart Logic
    const cartIcon = document.getElementById('cartIcon');
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    const cartItemsContainer = document.getElementById('cartItems');
    const cartTotalElement = document.getElementById('cartTotal');

    if (cartIcon) {
        cartIcon.addEventListener('click', (e) => {
            e.preventDefault();
            openCart();
        });
    }

    function openCart() {
        updateCartDisplay();
        cartSidebar.classList.add('active');
        cartOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeCart() {
        cartSidebar.classList.remove('active');
        cartOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCart);
    }

    // Orders Logic
    const ordersIcon = document.getElementById('ordersIcon');
    const ordersModal = document.getElementById('ordersModal');
    
    if (ordersIcon) {
        ordersIcon.addEventListener('click', (e) => {
            e.preventDefault();
            ordersModal.style.display = 'block';
            fetchOrders();
        });
    }

    function fetchOrders() {
        const container = document.getElementById('ordersListContainer');
        container.innerHTML = '<div style="text-align:center; padding:20px;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        fetch('get_orders.php')
            .then(res => res.json())
            .then(data => {
                if (!data || data.length === 0) {
                    container.innerHTML = '<div style="text-align:center; padding:40px; color:#888;">No orders found.</div>';
                    return;
                }

                let html = '';
                data.forEach(order => {
                    let itemsHtml = '';
                    order.items.forEach(item => {
                        itemsHtml += `
                            <div style="display:flex; gap:10px; margin-top:10px; align-items:center; border-top:1px dashed #eee; padding-top:10px;">
                                <img src="${item.image_url}" style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                                <div style="flex:1;">
                                    <div style="font-size:0.9rem; font-weight:600;">${item.product_name}</div>
                                    <div style="font-size:0.8rem; color:#666;">Size: ${item.size} | Qty: ${item.quantity}</div>
                                </div>
                                <div style="font-weight:600;">₹${item.price}</div>
                            </div>
                        `;
                    });

                    // Format Date
                    const date = new Date(order.order_date).toLocaleDateString();

                    html += `
                        <div style="border:1px solid #eee; padding:15px; border-radius:8px; margin-bottom:15px; background:#fafafa;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid #ddd; padding-bottom:5px;">
                                <span style="font-weight:700; color:var(--primary-color);">Order #${order.order_id}</span>
                                <span style="font-size:0.85rem; color:#666;">${date}</span>
                            </div>
                            <div style="font-size:0.9rem; margin-bottom:5px;">
                                <strong>Status:</strong> <span style="color:orange; font-weight:600;">${order.order_status}</span> 
                                <span style="color:#aaa;">|</span> 
                                <strong>Total:</strong> ₹${parseFloat(order.total_amount).toLocaleString()}
                                <span style="color:#aaa;">|</span> 
                                <strong>Est. Delivery:</strong> ${order.delivery_date}
                            </div>
                            <div style="background:white; padding:10px; border-radius:6px; border:1px solid #eee;">
                                ${itemsHtml}
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<div style="color:red; text-align:center;">Failed to load orders.</div>';
            });
    }

    // Close logic for orders modal
    // Close logic for orders modal
    window.onclick = function(event) {
        if (event.target == document.getElementById('globalCheckoutModal')) {
            document.getElementById('globalCheckoutModal').style.display = "none";
        }
        if (event.target == document.getElementById('ordersModal')) {
            document.getElementById('ordersModal').style.display = "none";
        }
    }

    function updateCartDisplay() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const countSpan = document.getElementById('cartCount');
        
        // Update Badge
        if (countSpan) {
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            countSpan.textContent = totalItems;
            countSpan.style.display = totalItems > 0 ? 'block' : 'none';
        }

        // Render Items
        if (cartItemsContainer) {
            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '<div style="text-align:center; padding: 40px; color: #888;">Your cart is empty</div>';
                cartTotalElement.innerText = '₹0';
                return;
            }

            let html = '';
            let total = 0;

            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                html += `
                    <div class="cart-item">
                        <img src="${item.image}" alt="${item.name}">
                        <div class="item-details">
                            <h4>${item.name}</h4>
                            <p style="font-size: 0.85rem; color: #666;">Size: ${item.size || 'N/A'}</p>
                            <div class="item-controls">
                                <div class="qty-control">
                                    <button onclick="updateCartItem(${index}, -1)">-</button>
                                    <span>${item.quantity}</span>
                                    <button onclick="updateCartItem(${index}, 1)">+</button>
                                </div>
                                <span style="font-weight: 600;">₹${itemTotal.toLocaleString()}</span>
                            </div>
                        </div>
                        <button onclick="removeCartItem(${index})" style="background:none; border:none; color: #ff6b6b; cursor: pointer; padding: 5px;"><i class="fas fa-trash"></i></button>
                    </div>
                `;
            });

            cartItemsContainer.innerHTML = html;
            cartTotalElement.innerText = '₹' + total.toLocaleString();
        }
    }

    window.updateCartItem = function(index, delta) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const item = cart[index];
        if (item) {
            item.quantity += delta;
            if (item.quantity <= 0) {
                cart.splice(index, 1);
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
        }
    };

    window.removeCartItem = function(index) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartDisplay();
    };

    // Re-use logic for init
    document.addEventListener('DOMContentLoaded', updateCartDisplay);

    function startCartCheckout() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        if (cart.length === 0) {
            alert("Your cart is empty!");
            return;
        }
        document.getElementById('globalCheckoutModal').style.display = 'block';
    }

    function submitGlobalCheckout() {
         const cart = JSON.parse(localStorage.getItem('cart') || '[]');
         const addr = document.getElementById('gAddr').value;
         const city = document.getElementById('gCity').value;
         const zip = document.getElementById('gZip').value;

         if (!addr || !city || !zip) {
             alert('Please fill all address fields');
             return;
         }

         // Calculate date
         const today = new Date();
         const deliveryStart = new Date(today);
         deliveryStart.setDate(today.getDate() + 3);
         const options = { weekday: 'short', month: 'short', day: 'numeric' };
         // simplified date range
         const dateStr = deliveryStart.toLocaleDateString('en-US', options);

         // Post to shop_checkout.php
         const form = document.createElement('form');
         form.method = 'POST';
         form.action = 'shop_checkout.php';

         const inputs = {
             'cart_data': JSON.stringify(cart),
             'address': addr,
             'city': city,
             'zip': zip,
             'delivery_date': dateStr
         };

         for (const key in inputs) {
             const input = document.createElement('input');
             input.type = 'hidden';
             input.name = key;
             input.value = inputs[key];
             form.appendChild(input);
         }

         document.body.appendChild(form);
         
         // Clear cart after submitting? usually yes, but let's clear it on success page? 
         // For now, let's keep it until payment is confirmed.
         // Actually, most sites clear it. But since this is a pseudo-checkout, let's clear it here for better UX flow assuming success.
         localStorage.removeItem('cart'); 

         form.submit();
    }

    // Expert Talk Logic (Global)
    function handleTalkToExperts(event) {
        if(event) event.preventDefault(); 
        
        if (confirm("Are you sure you want to talk to an expert? This will initiate a call.")) {
            window.location.href = 'tel:9495868854';
        }
    }
</script>

<!-- Add Styles for Cart Sidebar and Modal -->
<style>
    .cart-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); z-index: 1001;
        opacity: 0; visibility: hidden; transition: 0.3s;
    }
    .cart-overlay.active { opacity: 1; visibility: visible; }

    .cart-sidebar {
        position: fixed; top: 0; right: -400px; width: 350px; height: 100%;
        background: white; z-index: 1002; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: -5px 0 30px rgba(0,0,0,0.1);
        display: flex; flex-direction: column;
    }
    .cart-sidebar.active { right: 0; }

    .cart-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .cart-header h3 { font-size: 1.2rem; margin: 0; color: var(--primary-color); }
    .close-cart { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #888; }

    .cart-items { flex: 1; overflow-y: auto; padding: 20px; }
    .cart-item { display: flex; gap: 15px; margin-bottom: 20px; border-bottom: 1px solid #f9f9f9; padding-bottom: 15px; }
    .cart-item img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; }
    .item-details { flex: 1; }
    .item-details h4 { font-size: 0.95rem; margin-bottom: 4px; line-height: 1.3; }
    .item-controls { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; }
    .qty-control { display: flex; align-items: center; gap: 8px; background: #f0f0f0; border-radius: 4px; padding: 2px 5px; }
    .qty-control button { border: none; background: none; cursor: pointer; font-weight: bold; width: 20px; }

    .cart-footer { padding: 20px; border-top: 1px solid #eee; background: #fcfcfc; }
    .cart-total-row { display: flex; justify-content: space-between; margin-bottom: 20px; font-weight: 700; font-size: 1.1rem; }
    .btn-checkout { width: 100%; padding: 15px; background: var(--primary-color); color: white; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; transition: 0.3s; }
    .btn-checkout:hover { background: #0a1f3f; }

    /* Global Modal (Checkout Address) */
    .g-modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); }
    .g-modal-content { background: white; margin: 5% auto; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; position: relative; animation: slideDown 0.3s; }
    @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .g-input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 15px; }
</style>

<!-- Cart Sidebar HTML -->
<div class="cart-overlay" id="cartOverlay"></div>
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
        <h3>Shopping Cart</h3>
        <button class="close-cart" onclick="closeCart()">&times;</button>
    </div>
    <div class="cart-items" id="cartItems">
        <!-- Items injected here -->
    </div>
    <div class="cart-footer">
        <div class="cart-total-row">
            <span>Total</span>
            <span id="cartTotal">₹0</span>
        </div>
        <button class="btn-checkout" onclick="startCartCheckout()">Proceed to Checkout</button>
    </div>
</div>

<!-- Global Checkout Modal -->
<div id="globalCheckoutModal" class="g-modal" onclick="if(event.target===this)this.style.display='none'">
    <div class="g-modal-content">
        <h3 style="margin-bottom: 20px; color: var(--primary-color);">Delivery Details</h3>
        <input type="text" id="gAddr" class="g-input" placeholder="Street Address">
        <div style="display:flex; gap: 10px;">
            <input type="text" id="gCity" class="g-input" placeholder="City">
            <input type="text" id="gZip" class="g-input" placeholder="Zip Code">
        </div>
        <button class="btn-checkout" onclick="submitGlobalCheckout()">Pay & Place Order</button>
    </div>
</div>

<!-- Orders Modal -->
<div id="ordersModal" class="g-modal">
    <div class="g-modal-content" style="max-width: 600px; max-height: 80vh; display: flex; flex-direction: column;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; color: var(--primary-color);">My Orders</h3>
            <span onclick="document.getElementById('ordersModal').style.display='none'" style="cursor:pointer; font-size:1.5rem;">&times;</span>
        </div>
        <div id="ordersListContainer" style="overflow-y:auto; flex:1;">
            <!-- content -->
        </div>
    </div>
</div>

<script>
    // Close logic for modals
    window.onclick = function(event) {
        if (event.target == document.getElementById('globalCheckoutModal')) {
            document.getElementById('globalCheckoutModal').style.display = "none";
        }
        if (event.target == document.getElementById('ordersModal')) {
            document.getElementById('ordersModal').style.display = "none";
        }
    }

    function updateCartDisplay() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const countSpan = document.getElementById('cartCount');
        const cartItemsContainer = document.getElementById('cartItems');
        const cartTotalElement = document.getElementById('cartTotal');
        
        // Update Badge
        if (countSpan) {
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            countSpan.textContent = totalItems;
            countSpan.style.display = totalItems > 0 ? 'block' : 'none';
        }

        // Render Items if container exists
        if (cartItemsContainer) {
            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '<div style="text-align:center; padding: 40px; color: #888;">Your cart is empty</div>';
                if(cartTotalElement) cartTotalElement.innerText = '₹0';
                return;
            }

            let html = '';
            let total = 0;

            cart.forEach((item, index) => {
                let itemTotal = 0;
                if(item.price && item.quantity) {
                    itemTotal = item.price * item.quantity;
                    total += itemTotal;
                }
                
                html += `
                    <div class="cart-item">
                        <img src="${item.image}" alt="${item.name}">
                        <div class="item-details">
                            <h4>${item.name}</h4>
                            <p style="font-size: 0.85rem; color: #666;">Size: ${item.size || 'N/A'}</p>
                            <div class="item-controls">
                                <div class="qty-control">
                                    <button onclick="updateCartItem(${index}, -1)">-</button>
                                    <span>${item.quantity}</span>
                                    <button onclick="updateCartItem(${index}, 1)">+</button>
                                </div>
                                <span style="font-weight: 600;">₹${itemTotal.toLocaleString()}</span>
                            </div>
                        </div>
                        <button onclick="removeCartItem(${index})" style="background:none; border:none; color: #ff6b6b; cursor: pointer; padding: 5px;"><i class="fas fa-trash"></i></button>
                    </div>
                `;
            });

            cartItemsContainer.innerHTML = html;
            if(cartTotalElement) cartTotalElement.innerText = '₹' + total.toLocaleString();
        }
    }

    window.updateCartItem = function(index, delta) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const item = cart[index];
        if (item) {
            item.quantity += delta;
            if (item.quantity <= 0) {
                cart.splice(index, 1);
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
        }
    };

    window.removeCartItem = function(index) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartDisplay();
    };

    // Re-use logic for init
    document.addEventListener('DOMContentLoaded', updateCartDisplay);
</script>
