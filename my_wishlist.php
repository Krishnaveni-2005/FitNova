<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require "header.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favourites | FitNova FitShop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --secondary-color: #F4A261;
            --accent-color: #E63946;
            --text-dark: #1A1A1A;
            --text-gray: #555;
            --bg-light: #F0F4F8;
            --white: #fff;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a4a8a 100%);
            padding: 50px 0 40px;
            text-align: center;
            color: white;
        }

        .page-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .page-header h1 i { color: var(--accent-color); }

        .page-header p {
            font-size: 1rem;
            opacity: 0.8;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .breadcrumb {
            padding: 15px 0;
            font-size: 0.9rem;
            color: var(--text-gray);
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb a:hover { text-decoration: underline; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-state i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
            display: block;
        }

        .empty-state h2 {
            font-size: 1.5rem;
            color: var(--text-gray);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #aaa;
            margin-bottom: 25px;
        }

        .btn-shop {
            display: inline-block;
            padding: 14px 35px;
            background: var(--primary-color);
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
        }

        .btn-shop:hover {
            background: #0a1f40;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 44, 89, 0.3);
        }

        /* Product Grid */
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 25px;
            padding: 30px 0 60px;
        }

        .product-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid #f0f0f0;
            display: flex;
            flex-direction: column;
            position: relative;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }

        .product-img {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
            background: #f9f9f9;
        }

        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-img img {
            transform: scale(1.05);
        }

        .badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--accent-color);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            z-index: 2;
        }

        .remove-fav-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: white;
            color: var(--accent-color);
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .remove-fav-btn:hover {
            background: var(--accent-color);
            color: white;
            transform: scale(1.1);
        }

        .product-details {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-cat {
            font-size: 0.75rem;
            color: var(--secondary-color);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .product-title {
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.4;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .rating {
            color: #F4A261;
            font-size: 0.8rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid #f5f5f5;
        }

        .price {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--primary-color);
        }

        .btn-add-cart {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }

        .btn-add-cart:hover {
            background: #0a1f40;
        }

        /* Spinner */
        .loading-state {
            text-align: center;
            padding: 60px;
            color: var(--text-gray);
        }

        .loading-state i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            display: block;
        }
    </style>
</head>
<body>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-heart"></i> My Favourites</h1>
        <p>Items you've saved to your wishlist</p>
    </div>
</div>

<div class="container">
    <div class="breadcrumb">
        <a href="home.php">Home</a> &rsaquo; <a href="fitshop.php">FitShop</a> &rsaquo; My Favourites
    </div>

    <div id="wishlistContent">
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading your favourites...</p>
        </div>
    </div>
</div>

<!-- Product Modal (reused from fitshop) -->
<div id="productModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:10000; overflow-y:auto;">
    <div style="background:white; max-width:700px; margin:5% auto; border-radius:16px; overflow:hidden; position:relative;">
        <span onclick="document.getElementById('productModal').style.display='none'" style="position:absolute; top:15px; right:20px; font-size:1.8rem; cursor:pointer; color:#888; z-index:10;">&times;</span>
        <div style="display:flex; flex-wrap:wrap;">
            <div style="flex:1; min-width:250px; background:#f9f9f9;">
                <img id="modalImg" src="" style="width:100%; height:300px; object-fit:cover;">
            </div>
            <div style="flex:1; min-width:250px; padding:30px;">
                <span id="modalCat" style="font-size:0.75rem; color:#F4A261; text-transform:uppercase; font-weight:700;"></span>
                <h2 id="modalName" style="font-size:1.4rem; font-weight:800; margin:8px 0; color:#0F2C59;"></h2>
                <div id="modalPrice" style="font-size:1.6rem; font-weight:900; color:#0F2C59; margin-bottom:20px;"></div>
                <a href="fitshop.php" style="display:block; width:100%; padding:14px; background:#0F2C59; color:white; border:none; border-radius:8px; font-size:1rem; font-weight:700; text-align:center; text-decoration:none; margin-top:10px;">
                    <i class="fas fa-shopping-bag"></i> View in FitShop
                </a>
            </div>
        </div>
    </div>
</div>

<?php require "footer.php"; ?>

<script>
    const wishlistContent = document.getElementById('wishlistContent');
    let wishlistProducts = [];

    async function loadWishlist() {
        try {
            const res = await fetch('api_wishlist.php?action=get_all');
            const data = await res.json();

            if (!data.success || data.products.length === 0) {
                wishlistContent.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-heart-broken"></i>
                        <h2>Your Wishlist is Empty</h2>
                        <p>You haven't saved any products yet. Go to the shop and click the heart icon on products you love!</p>
                        <a href="fitshop.php" class="btn-shop"><i class="fas fa-shopping-bag"></i> Browse FitShop</a>
                    </div>`;
                return;
            }

            wishlistProducts = data.products;
            renderWishlist(wishlistProducts);

        } catch (err) {
            wishlistContent.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h2>Error loading wishlist</h2></div>`;
        }
    }

    function renderWishlist(products) {
        let html = '<div class="wishlist-grid">';
        products.forEach((p, index) => {
            let badge = '';
            if (p.is_bestseller) badge = `<span class="badge" style="background:#000;">Best Seller</span>`;
            else if (p.is_sale) badge = `<span class="badge" style="background:#E63946;">Sale</span>`;
            else if (p.is_new) badge = `<span class="badge" style="background:#F4A261;">New</span>`;

            const stars = generateStars(p.avg_rating);
            html += `
                <div class="product-card" onclick="openProductPreview(${index})">
                    <div class="product-img">
                        ${badge}
                        <button class="remove-fav-btn" title="Remove from favourites" onclick="event.stopPropagation(); removeFromWishlist(${p.product_id}, this)">
                            <i class="fas fa-heart"></i>
                        </button>
                        <img src="${p.image_url}" alt="${p.name}">
                    </div>
                    <div class="product-details">
                        <span class="product-cat">${p.category}</span>
                        <h3 class="product-title">${p.name}</h3>
                        <div class="rating">
                            ${stars}
                            <span style="color:#aaa; font-size:0.8em;">(${p.review_count})</span>
                        </div>
                        <div class="product-footer">
                            <span class="price">₹${Number(p.price).toLocaleString()}</span>
                            <button class="btn-add-cart" onclick="event.stopPropagation(); goToShop()">
                                <i class="fas fa-shopping-bag"></i> Shop
                            </button>
                        </div>
                    </div>
                </div>`;
        });
        html += '</div>';
        wishlistContent.innerHTML = html;
    }

    function generateStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (rating >= i) stars += '<i class="fas fa-star"></i>';
            else if (rating >= i - 0.5) stars += '<i class="fas fa-star-half-alt"></i>';
            else stars += '<i class="far fa-star"></i>';
        }
        return stars;
    }

    async function removeFromWishlist(productId, btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('product_id', productId);

        const res = await fetch('api_wishlist.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            showToast('Removed from favourites.', 'info');
            // Reload the wishlist after removal
            setTimeout(() => loadWishlist(), 400);
        }
    }

    function openProductPreview(index) {
        const p = wishlistProducts[index];
        document.getElementById('modalImg').src = p.image_url;
        document.getElementById('modalCat').textContent = p.category;
        document.getElementById('modalName').textContent = p.name;
        document.getElementById('modalPrice').textContent = '₹' + Number(p.price).toLocaleString();
        document.getElementById('productModal').style.display = 'block';
    }

    function goToShop() {
        window.location.href = 'fitshop.php';
    }

    // Close modal on background click
    document.getElementById('productModal').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });

    // Load wishlist on page load
    loadWishlist();
</script>

</body>
</html>
