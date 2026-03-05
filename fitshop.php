<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitShop - Pro Gym Gear & Supplements</title>
    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --secondary-color: #3498DB;
            --accent-color: #E63946;
            --text-dark: #1A1A1A;
            --text-gray: #555;
            --bg-light: #F8F9FA;
            --white: #FFFFFF;
            --border-radius: 12px;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        th {
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Navbar handled by header.php but we need the CSS for common elements if not in a separate CSS file */
        .navbar { background: white; padding: 15px 0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03); }
        .container { max-width: 1300px; margin: 0 auto; padding: 0 20px; }
        .nav-container { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 24px; font-weight: 900; color: var(--primary-color); }
        .nav-links { display: flex; align-items: center; gap: 30px; }
        .nav-link { font-weight: 500; font-size: 0.95rem; color: var(--text-dark); }
        .btn-signup { background: var(--primary-color); color: white; padding: 10px 25px; border-radius: 50px; font-weight: 600; font-size: 0.9rem; }

        /* Hero */
        .shop-hero {
            background: linear-gradient(rgba(15, 44, 89, 0.8), rgba(15, 44, 89, 0.6)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=1500');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
            border-radius: 0 0 20px 20px;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .hero-text {
            font-size: 1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Main Layout */
        .shop-layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 40px;
            margin-bottom: 60px;
        }

        /* Sidebar (Right) */
        .sidebar-right {
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .sidebar-widget {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            border: 1px solid #eee;
        }

        .widget-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--primary-color);
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        /* Search Bar */
        .search-box {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            transition: var(--transition);
        }

        .search-input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        /* Search Dropdown */
        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 8px 8px;
            box-shadow: var(--shadow);
            z-index: 100;
            max-height: 300px;
            overflow-y: auto;
            display: none;
        }

        .search-result-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-result-item:hover {
            background: #f8f9fa;
        }

        .search-result-img {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
        }

        .search-result-info {
            flex: 1;
        }

        .search-result-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .search-result-price {
            font-size: 0.8rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        /* Category Menu */
        .cat-menu {
            list-style: none;
        }

        .cat-menu li {
            margin-bottom: 8px;
        }

        .cat-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-radius: 8px;
            color: var(--text-gray);
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
        }

        .cat-link:hover,
        .cat-link.active {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }

        .cat-count {
            background: #eee;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 10px;
            color: #777;
        }

        /* Filter Widget (sidebar - kept for structure) */
        .filter-options {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--text-gray);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            user-select: none;
        }

        .filter-label:hover {
            color: var(--primary-color);
            background: rgba(52, 152, 219, 0.1);
        }

        .filter-checkbox {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--primary-color);
        }


        /* ── Premium Horizontal Price Filter Bar ── */
        .price-filter-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px 10px 0;
            background: transparent;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .price-filter-bar .filter-label-text {
            font-size: 0.72rem;
            font-weight: 800;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px 8px 18px;
            background: linear-gradient(135deg, var(--primary-color), #1a3a6e);
            color: white;
            border-radius: 10px 0 0 10px;
            margin-right: 12px;
            height: 42px;
            box-shadow: 3px 0 12px rgba(15,44,89,0.18);
            clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 50%, calc(100% - 8px) 100%, 0 100%);
            padding-right: 24px;
        }

        .price-pills-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .price-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 18px;
            border: 1.5px solid #d8dde8;
            border-radius: 50px;
            font-size: 0.83rem;
            font-weight: 600;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            background: white;
            user-select: none;
            white-space: nowrap;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        .price-pill:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: #f0f4ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 14px rgba(15,44,89,0.15);
        }

        .price-pill.active {
            background: linear-gradient(135deg, var(--primary-color), #1a3a6e);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 5px 16px rgba(15,44,89,0.3);
            transform: translateY(-2px);
        }

        .price-pill input[type="checkbox"] {
            display: none;
        }

        .price-pill .pill-check {
            font-size: 0.65rem;
            display: none;
            background: rgba(255,255,255,0.25);
            border-radius: 50%;
            width: 14px;
            height: 14px;
            align-items: center;
            justify-content: center;
        }

        .price-pill.active .pill-check {
            display: inline-flex;
        }

        .clear-filter-btn {
            display: none;
            align-items: center;
            gap: 5px;
            margin-left: 6px;
            font-size: 0.78rem;
            color: #e63946;
            cursor: pointer;
            font-weight: 700;
            border: 1.5px solid #e63946;
            background: transparent;
            padding: 6px 14px;
            border-radius: 50px;
            transition: all 0.18s;
            white-space: nowrap;
            line-height: 1.4;
        }

        .clear-filter-btn:hover {
            background: #e63946;
            color: white;
            box-shadow: 0 4px 12px rgba(230,57,70,0.3);
        }


        /* Product Grid */
        .category-section {
            margin-bottom: 50px;
            scroll-margin-top: 100px;
        }

        .cat-header {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 25px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
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
        }

        .product-card {
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .product-img {
            width: 100%;
            height: 180px;
            background: #fff;
            padding: 0;
            position: relative;
            overflow: hidden;
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



        /* Out-of-Stock Styling */
        .product-card.out-of-stock {
            pointer-events: none;
            opacity: 0.75;
        }

        .product-card.out-of-stock .add-btn {
            pointer-events: none;
        }

        .product-card.out-of-stock .product-img img {
            filter: grayscale(60%) brightness(0.88);
        }

        .product-card.out-of-stock:hover {
            transform: none;
            box-shadow: var(--shadow);
        }

        .product-card.out-of-stock:hover .product-img img {
            transform: none;
        }

        .out-of-stock-badge {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(0,0,0,0.58);
            color: white;
            text-align: center;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 6px 0;
            z-index: 5;
        }

        .btn-out-of-stock {
            background: #ccc !important;
            color: #888 !important;
            cursor: not-allowed !important;
            font-size: 0.75rem;
            padding: 6px 10px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
        }

        .product-details {
            padding: 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-cat {
            font-size: 0.75rem;
            color: var(--text-gray);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .product-title {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-dark);
            line-height: 1.3;
        }

        .rating {
            color: #FFC107;
            font-size: 0.75rem;
            margin-bottom: 10px;
        }

        .product-footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price {
            font-size: 1rem;
            font-weight: 800;
            color: var(--primary-color);
            font-family: 'Outfit', sans-serif;
        }

        .add-btn {
            background: #f0f2f5;
            color: var(--text-dark);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .add-btn {
            z-index: 10;
        }

        .add-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .footer { background: #111; color: white; padding: 60px 0 30px; margin-top: 100px; }

        @media (max-width: 992px) {
            .shop-layout {
                grid-template-columns: 1fr;
            }

            .sidebar-right {
                position: static;
                margin-bottom: 40px;
            }

            .sidebar-widget {
                max-width: 100%;
            }
        }

        /* Product Detail Modal */
        .product-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            overflow-y: auto;
        }

        .modal-container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
            position: relative;
        }

        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 30px;
            color: #999;
            cursor: pointer;
            z-index: 10;
            background: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .modal-close:hover {
            color: #333;
            background: #f5f5f5;
        }

        .modal-content-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 40px;
        }

        .modal-image {
            position: relative;
        }

        .modal-image img {
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .modal-details h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .modal-price {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin: 20px 0;
        }

        .modal-rating {
            color: #FFC107;
            font-size: 1.2rem;
            margin-bottom: 20px;
            cursor: pointer;
            transition: transform 0.2s;
            display: inline-block;
        }

        .modal-rating:hover {
            transform: scale(1.05);
        }

        .modal-description {
            color: var(--text-gray);
            line-height: 1.8;
            margin-bottom: 25px;
        }

        .size-selector {
            margin: 25px 0;
        }

        .size-selector label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .size-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .size-option {
            padding: 10px 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
        }

        .size-option:hover {
            border-color: var(--primary-color);
            background: #f8f9fa;
        }

        .size-option.selected {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 25px 0;
        }

        .quantity-selector label {
            font-weight: 600;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .qty-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: #f8f9fa;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .qty-input {
            width: 60px;
            height: 40px;
            border: none;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
        }

        .add-to-cart-btn {
            width: 100%;
            background: var(--primary-color);
            color: white;
            padding: 18px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .add-to-cart-btn:hover {
            background: #0a1f40;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(15, 44, 89, 0.3);
        }

        .modal-specs {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin: 25px 0;
        }

        .modal-specs h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .modal-specs ul {
            list-style: none;
        }

        .modal-specs li {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            color: var(--text-gray);
        }

        .modal-specs li:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .modal-content-wrapper {
                grid-template-columns: 1fr;
                padding: 20px;
            }
        }
        
        /* Review List Styles */
        .reviews-container {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .review-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f5f5f5;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .reviewer-name {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 0.95rem;
        }
        .review-date {
            color: #999;
            font-size: 0.8rem;
        }
        .review-stars {
            color: #FFC107;
            font-size: 0.8rem;
            margin-bottom: 5px;
        }
        .review-text {
            color: var(--text-gray);
            line-height: 1.5;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <?php include 'header.php'; ?>

    <header class="shop-hero">
        <div class="container">
            <h1 class="hero-title">Hardcore Gear. Real Results.</h1>
            <p class="hero-text">Professional grade equipment, supplements, and performance wear.</p>
        </div>
    </header>

    <div class="container shop-layout">

        <!-- Left Column: Products -->
        <main class="products-area">

            <!-- Horizontal Price Filter Bar -->
            <div class="price-filter-bar" id="priceFilterBar">
                <span class="filter-label-text">
                    <i class="fas fa-sliders-h"></i> Price Range
                </span>
                <div class="price-pills-group">
                    <label class="price-pill" id="pill-0-500">
                        <input type="checkbox" class="pill-checkbox" value="0-500" onchange="handlePillFilter(this)">
                        <i class="fas fa-check pill-check"></i>
                        Under &#8377;500
                    </label>
                    <label class="price-pill" id="pill-500-1000">
                        <input type="checkbox" class="pill-checkbox" value="500-1000" onchange="handlePillFilter(this)">
                        <i class="fas fa-check pill-check"></i>
                        &#8377;500 &ndash; &#8377;1000
                    </label>
                    <label class="price-pill" id="pill-1000-2000">
                        <input type="checkbox" class="pill-checkbox" value="1000-2000" onchange="handlePillFilter(this)">
                        <i class="fas fa-check pill-check"></i>
                        &#8377;1000 &ndash; &#8377;2000
                    </label>
                    <label class="price-pill" id="pill-2000-999999">
                        <input type="checkbox" class="pill-checkbox" value="2000-999999" onchange="handlePillFilter(this)">
                        <i class="fas fa-check pill-check"></i>
                        Over &#8377;2000
                    </label>
                    <button class="clear-filter-btn" id="clearFiltersBtn" onclick="clearAllFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>


            <?php
            require "db_connect.php";

            // Auto-add stock column if it doesn't exist yet
            $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS stock INT NOT NULL DEFAULT 100");
            
            // Define categories to display in order
            $categories = [
                'men' => "Men's Performance Wear",
                'women' => "Women's Performance Wear",
                'supplements' => "Supplements",
                'equipment' => "Heavy Equipment"
            ];

            foreach ($categories as $catKey => $catTitle):
                // Fetch products for this category with dynamic rating calculation
                $sql = "SELECT p.*, 
                        (SELECT COALESCE(AVG(rating), 0) FROM product_reviews pr WHERE pr.product_id = p.product_id) as avg_rating,
                        (SELECT COUNT(*) FROM product_reviews pr WHERE pr.product_id = p.product_id) as total_reviews
                        FROM products p 
                        WHERE p.category = ? 
                        ORDER BY p.is_bestseller DESC, avg_rating DESC 
                        LIMIT 30";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $catKey);
                $stmt->execute();
                $result = $stmt->get_result();
            ?>
                <section id="<?php echo $catKey; ?>" class="category-section">
                    <div class="cat-header"><?php echo $catTitle; ?></div>
                    <div class="product-grid">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($prod = $result->fetch_assoc()):
                                $prod['rating'] = number_format($prod['avg_rating'], 1);
                                $prod['review_count'] = $prod['total_reviews'];
                                $isOutOfStock = isset($prod['stock']) && (int)$prod['stock'] <= 0;
                            ?>
                                <div class="product-card<?php echo $isOutOfStock ? ' out-of-stock' : ''; ?>" onclick="<?php echo $isOutOfStock ? 'void(0)' : 'openProductModal(' . htmlspecialchars(json_encode($prod)) . ')'; ?>">
                                    <div class="product-img">
                                        <?php if ($isOutOfStock): ?>
                                            <span class="out-of-stock-badge">Out of Stock</span>
                                        <?php elseif ($prod['is_bestseller']): ?>
                                            <span class="badge" style="background:#000;">Best Seller</span>
                                        <?php elseif ($prod['is_sale']): ?>
                                            <span class="badge" style="background:#E63946;">Sale</span>
                                        <?php elseif ($prod['is_new']): ?>
                                            <span class="badge" style="background:var(--secondary-color);">New</span>
                                        <?php endif; ?>

                                        <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                    </div>
                                    <div class="product-details">
                                        <span class="product-cat"><?php echo ucfirst($prod['category']); ?></span>
                                        <h3 class="product-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
                                        <div class="rating">
                                            <?php 
                                            // Ensure we use the computed average rating
                                            $currentRating = $prod['total_reviews'] > 0 ? $prod['avg_rating'] : 0;
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($currentRating >= $i) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } elseif ($currentRating >= $i - 0.5) {
                                                    echo '<i class="fas fa-star-half-alt"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                            <span style="color:#aaa; font-size: 0.8em;">(<?php echo $prod['review_count']; ?>)</span>
                                        </div>
                                        
                                        <?php if ($prod['has_sizes']): ?>
                                            <div style="margin-bottom: 10px;">
                                                <select class="size-select" onclick="event.stopPropagation();" style="width: 100%; padding: 5px; border-radius: 4px; border: 1px solid #ddd; font-size: 0.85rem;">
                                                    <option value="">Select Size</option>
                                                    <option value="S">S</option>
                                                    <option value="M">M</option>
                                                    <option value="L">L</option>
                                                    <option value="XL">XL</option>
                                                    <option value="XXL">XXL</option>
                                                </select>
                                            </div>
                                        <?php else: ?>
                                            <div style="margin-bottom: 10px; height: 28px;"></div> <!-- Spacer -->
                                        <?php endif; ?>

                                        <div class="product-footer">
                                            <span class="price">₹<?php echo number_format($prod['price']); ?></span>
                                            <?php if ($isOutOfStock): ?>
                                                <button class="btn-out-of-stock" disabled>Out of Stock</button>
                                            <?php else: ?>
                                            <div style="display: flex; gap: 8px;">
                                                <button class="add-btn" title="Add to Cart" onclick="event.stopPropagation(); quickAddToCart(<?php echo $prod['product_id']; ?>, '<?php echo htmlspecialchars($prod['name']); ?>')"><i class="fas fa-plus"></i></button>
                                                <button class="add-btn" title="Buy Now" style="background: #2ECC71; color: white;" onclick="event.stopPropagation(); quickBuyNow(event, <?php echo htmlspecialchars(json_encode($prod)); ?>)"><i class="fas fa-bolt"></i></button>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="grid-column: 1/-1; text-align: center; color: #777; padding: 20px;">No products found in this category yet. Check back soon!</p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php 
                $stmt->close();
            endforeach; 
            ?>
        </main>

        <!-- Right Sidebar -->
        <aside class="sidebar-right">
            <!-- Search Widget -->
            <div class="sidebar-widget">
                <div class="widget-title">Search</div>
                <div class="search-box">
                    <input type="text" id="liveSearchInput" class="search-input" placeholder="Search products..." autocomplete="off">
                    <i class="fas fa-search search-icon"></i>
                    <div id="liveSearchResults" class="search-results-dropdown"></div>
                </div>
            </div>

            <!-- Categories Widget -->
            <div class="sidebar-widget">
                <div class="widget-title">Categories</div>
                <ul class="cat-menu">
                    <li><a onclick="scrollToCat('men')" class="cat-link"><span>Men's Gym Wear</span> <span class="cat-count">20+</span></a></li>
                    <li><a onclick="scrollToCat('women')" class="cat-link"><span>Women's Gym Wear</span> <span class="cat-count">20+</span></a></li>
                    <li><a onclick="scrollToCat('supplements')" class="cat-link"><span>Supplements</span> <span class="cat-count">15+</span></a></li>
                    <li><a onclick="scrollToCat('equipment')" class="cat-link"><span>Equipment</span> <span class="cat-count">50+</span></a></li>
                </ul>
            </div>



            <!-- Promo Widget -->
            <div class="sidebar-widget" style="background: var(--primary-color); color: white; text-align: center;">
                <i class="fas fa-shipping-fast" style="font-size: 2rem; margin-bottom: 10px; color: var(--secondary-color);"></i>
                <h4 style="margin-bottom: 5px;">Free Shipping</h4>
                <p style="font-size: 0.9rem; opacity: 0.8;">On all orders over ₹2000</p>
            </div>
        </aside>

    </div>

    <!-- Product Detail Modal -->
    <div id="productModal" class="product-modal">
        <div class="modal-container">
            <span class="modal-close" onclick="closeProductModal()">&times;</span>
            <div class="modal-content-wrapper">
                <div class="modal-image">
                    <img id="modalProductImg" src="" alt="Product">
                </div>
                <div class="modal-details">
                    <span id="modalCategory" class="product-cat"></span>
                    <h2 id="modalProductName"></h2>
                    <div id="modalRating" class="modal-rating"></div>
                    <div id="modalPrice" class="modal-price"></div>
                    <p id="modalDescription" class="modal-description">Premium quality product designed for performance and comfort. Perfect for your fitness journey.</p>
                    
                    <div id="sizeSelector" class="size-selector" style="display:none;">
                        <label>Select Size:</label>
                        <div class="size-options">
                            <div class="size-option" onclick="selectSize(this, 'S')">S</div>
                            <div class="size-option" onclick="selectSize(this, 'M')">M</div>
                            <div class="size-option" onclick="selectSize(this, 'L')">L</div>
                            <div class="size-option" onclick="selectSize(this, 'XL')">XL</div>
                            <div class="size-option" onclick="selectSize(this, 'XXL')">XXL</div>
                        </div>
                    </div>

                    <div class="quantity-selector">
                        <label>Quantity:</label>
                        <div class="qty-controls">
                            <button class="qty-btn" onclick="changeQty(-1)">-</button>
                            <input type="number" id="productQty" class="qty-input" value="1" min="1" max="10" readonly>
                            <button class="qty-btn" onclick="changeQty(1)">+</button>
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 20px;">
                        <button class="add-to-cart-btn" onclick="addToCartFromModal()">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="add-to-cart-btn" onclick="openCheckoutModal()" style="background: #2ECC71;">
                            <i class="fas fa-bolt"></i> Buy Now
                        </button>
                    </div>

                    <div class="modal-specs">
                        <h3>Product Features</h3>
                        <ul>
                            <li>✓ Premium quality materials</li>
                            <li>✓ Moisture-wicking fabric</li>
                            <li>✓ Breathable design</li>
                            <li>✓ Perfect fit guarantee</li>
                            <li>✓ Easy care & maintenance</li>
                        </ul>
                    </div>

                    <!-- Review Display Section -->
                    <div class="reviews-container">
                        <h3 style="color: var(--primary-color); margin-bottom: 15px;">Customer Reviews</h3>
                        <div id="reviewsList">
                            <!-- Reviews will be loaded here via JS -->
                            <p style="color: #999; font-style: italic;">Loading reviews...</p>
                        </div>
                    </div>

                    <!-- Review Section (Form) -->
                    <div class="review-section" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                        <h3 style="color: var(--primary-color); margin-bottom: 15px;">Write a Review</h3>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="write-review-form">
                                <div class="rating-input" style="margin-bottom: 15px;">
                                    <label style="display:block; font-weight:600; margin-bottom:5px;">Your Rating:</label>
                                    <div class="star-rating-selector" style="font-size: 1.5rem; color: #ddd; cursor: pointer;">
                                        <i class="fas fa-star" onclick="setRating(1)" onmouseover="hoverRating(1)" onmouseout="resetRating()"></i>
                                        <i class="fas fa-star" onclick="setRating(2)" onmouseover="hoverRating(2)" onmouseout="resetRating()"></i>
                                        <i class="fas fa-star" onclick="setRating(3)" onmouseover="hoverRating(3)" onmouseout="resetRating()"></i>
                                        <i class="fas fa-star" onclick="setRating(4)" onmouseover="hoverRating(4)" onmouseout="resetRating()"></i>
                                        <i class="fas fa-star" onclick="setRating(5)" onmouseover="hoverRating(5)" onmouseout="resetRating()"></i>
                                    </div>
                                    <input type="hidden" id="reviewRating" value="0">
                                </div>
                                <div style="margin-bottom: 15px;">
                                    <textarea id="reviewText" class="search-input" rows="3" placeholder="Write your review here..."></textarea>
                                </div>
                                <button class="add-to-cart-btn" onclick="submitReview()" style="padding: 12px; font-size: 1rem; width: auto;">Submit Review</button>
                            </div>
                        <?php else: ?>
                            <p style="color: #666; background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">Please <a href="login.php" style="color: var(--primary-color); font-weight: bold; text-decoration: underline;">login</a> to leave a review.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Express Checkout Modal -->
    <div id="checkoutModal" class="product-modal" style="z-index: 2100;">
        <div class="modal-container" style="max-width: 600px;">
            <span class="modal-close" onclick="document.getElementById('checkoutModal').style.display='none'">&times;</span>
            <div style="padding: 40px;">
                <h2 style="color: var(--primary-color); margin-bottom: 20px;">Express Checkout</h2>
                <p style="color: #666; margin-bottom: 30px;">Enter your delivery details to proceed.</p>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Street Address</label>
                    <input type="text" id="chkAddress" class="search-input" placeholder="e.g. 123 Fitness St.">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px;">City</label>
                        <input type="text" id="chkCity" class="search-input" placeholder="City">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px;">Zip Code</label>
                        <input type="text" id="chkZip" class="search-input" placeholder="Zip Code">
                    </div>
                </div>

                <div style="background: #eefbf3; padding: 15px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #c3e6cb; color: #155724;">
                    <strong><i class="fas fa-truck"></i> Estimated Delivery:</strong> <span id="deliveryDate"></span>
                </div>

                <button class="add-to-cart-btn" onclick="proceedToPayment()" style="background: var(--primary-color);">
                    Proceed to Payment <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Auth Prompt Modal -->
    <div id="authModal" class="product-modal" style="z-index: 3000;">
        <div class="modal-container" style="max-width: 400px; text-align: center; padding: 40px 30px;">
            <span class="modal-close" onclick="closeAuthModal()">&times;</span>
            <div style="background: #eef2ff; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-user-lock" style="font-size: 2rem; color: var(--primary-color);"></i>
            </div>
            <h3 style="margin-bottom: 10px; color: var(--primary-color); font-size: 1.5rem;">Access Restricted</h3>
            <p id="authModalMessage" style="color: var(--text-gray); margin-bottom: 30px; line-height: 1.6;">You must be a registered client to perform this action.</p>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <button onclick="window.location.href='signup.php'" class="add-to-cart-btn" style="margin: 0; padding: 12px;">
                    Create Account
                </button>
                <button onclick="window.location.href='login.php'" class="add-to-cart-btn" style="margin: 0; padding: 12px; background: transparent; color: var(--text-gray); border: 1px solid #ddd;">
                    Already have an account? Login
                </button>
            </div>
        </div>
    </div>



    <script>
        let currentProduct = null;
        let selectedSize = null;
        // Check login status from PHP session
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        // Init Cart Count
        document.addEventListener('DOMContentLoaded', () => updateCartCount());

        function updateCartCount() {
            const userId = "<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>";
            const cartKey = userId ? `cart_${userId}` : 'cart_guest';
            
            const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
            const countSpan = document.getElementById('cartCount');
            if (countSpan) {
                const totalItems = cart.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
                countSpan.textContent = totalItems;
                countSpan.style.display = totalItems > 0 ? 'block' : 'none';
            }
        }

        function openProductModal(product) {
            currentProduct = product;
            selectedSize = null;
            
            document.getElementById('modalProductImg').src = product.image_url;
            document.getElementById('modalCategory').textContent = product.category.toUpperCase();
            document.getElementById('modalProductName').textContent = product.name;
            // Generate Star Rating HTML
            let starHtml = '';
            let ratingVal = parseInt(product.review_count) > 0 ? parseFloat(product.rating) : 0;
            for (let i = 1; i <= 5; i++) {
                if (ratingVal >= i) {
                    starHtml += '<i class="fas fa-star"></i>';
                } else if (ratingVal >= i - 0.5) {
                    starHtml += '<i class="fas fa-star-half-alt"></i>';
                } else {
                    starHtml += '<i class="far fa-star"></i>';
                }
            }
            document.getElementById('modalRating').innerHTML = `${starHtml} <span style="color:#aaa; font-size: 0.9rem;">(${product.review_count} reviews)</span>`;
            
            // Fetch Reviews
            fetchReviews(product.product_id);

            // Add click event to scroll to review section

            document.getElementById('modalRating').onclick = function() {
                const reviewSection = document.querySelector('.review-section');
                if(reviewSection) {
                    reviewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // Provide visual feedback
                    const form = reviewSection.querySelector('.write-review-form');
                    if(form) {
                        form.style.transition = 'background 0.3s';
                        form.style.background = '#f0f9ff';
                        setTimeout(() => form.style.background = 'transparent', 1000);
                    }
                }
            };
            document.getElementById('modalPrice').textContent = '₹' + Number(product.price).toLocaleString();
            document.getElementById('productQty').value = 1;
            
            // Show/hide size selector
            if (product.has_sizes == 1) {
                document.getElementById('sizeSelector').style.display = 'block';
            } else {
                document.getElementById('sizeSelector').style.display = 'none';
            }
            
            // Reset size selections
            document.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('selected'));
            
            document.getElementById('productModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function selectSize(element, size) {
            document.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('selected'));
            element.classList.add('selected');
            selectedSize = size;
        }

        function changeQty(delta) {
            const input = document.getElementById('productQty');
            let newVal = parseInt(input.value) + delta;
            if (newVal >= 1 && newVal <= 10) {
                input.value = newVal;
            }
        }

        // Review Logic
        let selectedRating = 0;
        
        function setRating(rating) {
            selectedRating = rating;
            document.getElementById('reviewRating').value = rating;
            updateStars(rating);
        }

        function hoverRating(rating) {
            updateStars(rating);
        }

        function resetRating() {
            updateStars(selectedRating);
        }

        function updateStars(rating) {
            const stars = document.querySelectorAll('.star-rating-selector i');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.style.color = '#FFC107';
                } else {
                    star.style.color = '#ddd';
                }
            });
        }

        function submitReview() {
            if (selectedRating === 0) {
                showToast('Please select a rating.', 'error');
                return;
            }
            
            const text = document.getElementById('reviewText').value;
             // Basic implementation - in real app, might want title etc.
            
            fetch('submit_review.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    product_id: currentProduct.product_id,
                    rating: selectedRating,
                    review_text: text
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Review submitted successfully!', 'success');
                    closeProductModal();
                    setTimeout(() => location.reload(), 1500); // Reload after toast
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(err => console.error(err));
        }

        function fetchReviews(productId) {
            const container = document.getElementById('reviewsList');
            container.innerHTML = '<p style="color: #999; font-style: italic;">Loading reviews...</p>';
            
            fetch(`get_reviews.php?product_id=${productId}`)
                .then(response => response.json())
                .then(reviews => {
                    if (reviews.length > 0) {
                        let html = '';
                        reviews.forEach(review => {
                            // Generate stars
                            let stars = '';
                            for (let i = 1; i <= 5; i++) {
                                stars += i <= review.rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star" style="color:#ddd;"></i>';
                            }
                            
                            html += `
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="reviewer-name">${review.user_name}</div>
                                        <div class="review-date">${review.date}</div>
                                    </div>
                                    <div class="review-stars">${stars}</div>
                                    <div class="review-text">${review.text}</div>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p style="color: #777;">No reviews yet. Be the first to add one!</p>';
                    }
                })
                .catch(err => {
                    console.error('Error fetching reviews:', err);
                    container.innerHTML = '<p style="color: red;">Failed to load reviews.</p>';
                });
        }

        function addToCartFromModal() {
            // Check if user is logged in
            if (!isLoggedIn) {
                showAuthModal("You must be a registered client to add items to the cart.");
                return;
            }

            if (!currentProduct) return;
            
            // Check if size is required but not selected
            if (currentProduct.has_sizes == 1 && !selectedSize) {
                showToast('Please select a size before adding to cart!', 'error');
                return;
            }
            
            const qty = parseInt(document.getElementById('productQty').value);
            
            // Get user-specific key
            const userId = "<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>";
            const cartKey = userId ? `cart_${userId}` : 'cart_guest';

            // Get existing cart or create new one
            let cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
            
            // Check if item exists
            const existingIndex = cart.findIndex(item => 
                item.id === currentProduct.product_id && 
                (selectedSize ? item.size === selectedSize : true)
            );
            
            if (existingIndex > -1) {
                cart[existingIndex].quantity += qty;
            } else {
                cart.push({
                    id: currentProduct.product_id,
                    name: currentProduct.name,
                    price: currentProduct.price,
                    image: currentProduct.image_url,
                    size: selectedSize,
                    quantity: qty
                });
            }
            
            localStorage.setItem(cartKey, JSON.stringify(cart));
            
            updateCartCount(); 

            showToast(`${currentProduct.name} ${selectedSize ? '(Size ' + selectedSize + ')' : ''} added to cart!`, 'success');
            closeProductModal();
        }

        function quickAddToCart(productId, productName) {
            // Check if user is logged in
            if (!isLoggedIn) {
                showAuthModal("You must be a registered client to add items to the cart.");
                return;
            }

            showToast('Please click on the product card to view options and add to cart.', 'info');
        }

        function quickBuyNow(event, product) {
            // Check login
            if (!isLoggedIn) {
                showAuthModal("You must be a registered client to buy items.");
                return;
            }

            // Find size selector if it exists
            const card = event.target.closest('.product-card');
            const sizeSelect = card.querySelector('.size-select');
            
            let size = null;
            if (product.has_sizes == 1) {
                if (sizeSelect && sizeSelect.value) {
                    size = sizeSelect.value;
                } else {
                    showToast('Please select a size from the dropdown first!', 'error');
                    return;
                }
            }

            currentProduct = product;
            selectedSize = size;
            document.getElementById('productQty').value = 1;

            openCheckoutModal();
        }

        function openCheckoutModal() {
            if (!isLoggedIn) {
                showAuthModal("You must be a registered client to buy items.");
                return;
            }

            if (currentProduct.has_sizes == 1 && !selectedSize) {
                showToast('Please select a size first!', 'error');
                return;
            }

            // Calculate Delivery Date (Today + 3-5 days)
            const today = new Date();
            const deliveryStart = new Date(today);
            deliveryStart.setDate(today.getDate() + 3);
            const deliveryEnd = new Date(today);
            deliveryEnd.setDate(today.getDate() + 5);
            
            const options = { weekday: 'short', month: 'short', day: 'numeric' };
            document.getElementById('deliveryDate').innerText = `${deliveryStart.toLocaleDateString('en-US', options)} - ${deliveryEnd.toLocaleDateString('en-US', options)}`;

            // Show Checkout Modal
            document.getElementById('checkoutModal').style.display = 'block';
        }

        function proceedToPayment() {
            const addr = document.getElementById('chkAddress').value;
            const city = document.getElementById('chkCity').value;
            const zip = document.getElementById('chkZip').value;

            if (!addr || !city || !zip) {
                showToast('Please fill in all delivery details.', 'error');
                return;
            }

            // Create form to submit to checkout page
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'shop_checkout.php';

            const fields = {
                product_id: currentProduct.product_id,
                name: currentProduct.name,
                price: currentProduct.price,
                image: currentProduct.image_url,
                qty: document.getElementById('productQty').value,
                size: selectedSize || '',
                address: addr,
                city: city,
                zip: zip,
                delivery_date: document.getElementById('deliveryDate').innerText
            };

            for (const key in fields) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }

        function scrollToCat(id) {
            const element = document.getElementById(id);
            const offset = 100;
            const bodyRect = document.body.getBoundingClientRect().top;
            const elementRect = element.getBoundingClientRect().top;
            const elementPosition = elementRect - bodyRect;
            const offsetPosition = elementPosition - offset;

            window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.id === 'productModal') {
                closeProductModal();
            }
            if (event.target.id === 'checkoutModal') {
                document.getElementById('checkoutModal').style.display='none';
            }
        }

        // Live Search & Autocomplete Functionality
        const searchInput = document.getElementById('liveSearchInput');
        const searchResults = document.getElementById('liveSearchResults');
        let searchTimeout;

        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();

            if (query.length < 1) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`api_shop_search.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(product => {
                                const itemDiv = document.createElement('div');
                                itemDiv.className = 'search-result-item';
                                itemDiv.onclick = function() { handleSearchResultClick(product); };
                                
                                const img = document.createElement('img');
                                img.src = product.image_url;
                                img.className = 'search-result-img';
                                img.alt = product.name;
                                
                                const infoDiv = document.createElement('div');
                                infoDiv.className = 'search-result-info';
                                
                                const nameDiv = document.createElement('div');
                                nameDiv.className = 'search-result-name';
                                nameDiv.textContent = product.name;
                                
                                const priceDiv = document.createElement('div');
                                priceDiv.className = 'search-result-price';
                                priceDiv.textContent = '₹' + Number(product.price).toLocaleString();
                                
                                infoDiv.appendChild(nameDiv);
                                infoDiv.appendChild(priceDiv);
                                
                                itemDiv.appendChild(img);
                                itemDiv.appendChild(infoDiv);
                                
                                searchResults.appendChild(itemDiv);
                            });
                            searchResults.style.display = 'block';
                        } else {
                            searchResults.innerHTML = '<div style="padding: 15px; text-align: center; color: #777; font-size: 0.9rem;">No products found.</div>';
                            searchResults.style.display = 'block';
                        }
                    })
                    .catch(err => console.error('Search error:', err));
            }, 300); // 300ms debounce
        });

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        function handleSearchResultClick(product) {
            openProductModal(product);
            searchResults.style.display = 'none';
            searchInput.value = ''; // Reset search input
        }

        // Price Filter — single-select pill logic (radio-button style)
        function handlePillFilter(checkbox) {
            const isChecked = checkbox.checked;

            // Deselect all pills first
            document.querySelectorAll('.pill-checkbox').forEach(cb => {
                cb.checked = false;
                cb.closest('.price-pill').classList.remove('active');
            });

            // Re-apply only if this one was just turned on
            if (isChecked) {
                checkbox.checked = true;
                checkbox.closest('.price-pill').classList.add('active');
            }

            applyFilters();

            const hasActive = document.querySelectorAll('.pill-checkbox:checked').length > 0;
            document.getElementById('clearFiltersBtn').style.display = hasActive ? 'inline-flex' : 'none';
        }

        function clearAllFilters() {
            document.querySelectorAll('.pill-checkbox').forEach(cb => {
                cb.checked = false;
                cb.closest('.price-pill').classList.remove('active');
            });
            document.getElementById('clearFiltersBtn').style.display = 'none';
            applyFilters();
        }

        function applyFilters() {
            const selectedRanges = Array.from(document.querySelectorAll('.pill-checkbox:checked'))
                .map(cb => {
                    const [min, max] = cb.value.split('-').map(Number);
                    return { min, max };
                });

            let totalVisible = 0;

            document.querySelectorAll('.category-section').forEach(section => {
                let hasVisible = false;
                section.querySelectorAll('.product-card').forEach(card => {
                    const priceText = card.querySelector('.price')?.textContent || '0';
                    const price = Number(priceText.replace(/[^0-9]/g, ''));
                    const matches = selectedRanges.length === 0 ||
                        selectedRanges.some(r => price >= r.min && price <= r.max);
                    card.style.display = matches ? '' : 'none';
                    if (matches) { hasVisible = true; totalVisible++; }
                });
                section.style.display = hasVisible ? '' : 'none';
            });

            // Show / hide empty state message
            let emptyMsg = document.getElementById('filterEmptyState');
            if (selectedRanges.length > 0 && totalVisible === 0) {
                if (!emptyMsg) {
                    emptyMsg = document.createElement('div');
                    emptyMsg.id = 'filterEmptyState';
                    emptyMsg.innerHTML = `
                        <div style="text-align:center; padding:60px 20px;">
                            <i class="fas fa-box-open" style="font-size:3.5rem; color:#ccd0dd; display:block; margin-bottom:16px;"></i>
                            <h3 style="color:var(--primary-color); margin-bottom:8px; font-size:1.2rem;">No Products Found</h3>
                            <p style="color:#8a95ab; font-size:0.9rem; margin-bottom:20px;">
                                No items match the selected price range.<br>Try a different range or clear the filter.
                            </p>
                            <button onclick="clearAllFilters()" style="padding:9px 24px; background:var(--primary-color); color:white; border:none; border-radius:50px; font-weight:600; cursor:pointer; font-size:0.88rem;">
                                <i class="fas fa-times" style="margin-right:6px;"></i>Clear Filter
                            </button>
                        </div>`;
                    document.querySelector('.products-area').appendChild(emptyMsg);
                }
                emptyMsg.style.display = 'block';
            } else if (emptyMsg) {
                emptyMsg.style.display = 'none';
            }
        }



        // Auth Modal Helper Functions
        function showAuthModal(msg) {
            if(msg) document.getElementById('authModalMessage').innerText = msg;
            document.getElementById('authModal').style.display = 'block';
            // Only hide overflow if it's not already hidden by another modal (corner case)
            if (document.body.style.overflow !== 'hidden') {
                document.body.style.overflow = 'hidden';
            }
        }

        function closeAuthModal() {
            document.getElementById('authModal').style.display = 'none';
            // Restore overflow only if no other modals are open
            if (document.getElementById('productModal').style.display !== 'block') {
                document.body.style.overflow = 'auto';
            }
        }
        
        // Close auth modal on outside click
        window.onclick = function(event) {
            const authModal = document.getElementById('authModal');
            if (event.target === authModal) {
                closeAuthModal();
            }
            if (event.target.id === 'productModal') {
                closeProductModal();
            }
            if (event.target.id === 'checkoutModal') {
                document.getElementById('checkoutModal').style.display='none';
            }
        }
        

    </script>
    <?php include 'footer.php'; ?>
</body>

</html>
