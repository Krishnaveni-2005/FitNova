<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tandoori Chicken Protein Bowl - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #3498DB;
            --bg-color: #F8F9FA;
            --text-color: #333333;
            --text-light: #6C757D;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-color); margin: 0; }
        .container { max-width: 100%; margin: 40px auto; padding: 0 40px; }
        .recipe-header { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .recipe-image { 
            width: 100%; 
            height: 450px; 
            background-image: url('https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?auto=format&fit=crop&q=80&w=1200'); 
            background-size: cover; 
            background-position: center;
            position: relative;
            display: flex;
            align-items: flex-end;
        }
        .recipe-image::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60%;
            background: linear-gradient(to top, rgba(15, 44, 89, 0.95), transparent);
        }
        .recipe-title-overlay {
            position: relative;
            z-index: 1;
            color: white;
            padding: 40px;
            width: 100%;
        }
        .recipe-title-overlay h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 3rem;
            margin: 0 0 10px 0;
            font-weight: 900;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .recipe-title-overlay p {
            font-size: 1.2rem;
            margin: 0;
            opacity: 0.95;
            font-weight: 500;
        }
        .recipe-meta { display: flex; gap: 30px; padding: 25px; background: #f8f9fa; }
        .meta-item { display: flex; align-items: center; gap: 8px; color: var(--text-light); }
        .meta-item i { color: var(--accent-color); font-size: 1.2rem; }
        .recipe-content { background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        h2 { font-family: 'Outfit', sans-serif; color: var(--primary-color); margin-bottom: 20px; }
        h3 { color: var(--accent-color); font-size: 1.1rem; margin-top: 20px; }
        .ingredients-list { list-style: none; padding: 0; }
        .ingredients-list li { padding: 12px 0; border-bottom: 1px solid #eee; display: flex; gap: 12px; align-items: center; }
        .ingredients-list li i { color: var(--accent-color); }
        .instructions { line-height: 1.8; color: var(--text-color); }
        .instructions ol { padding-left: 20px; }
        .instructions li { margin-bottom: 15px; }
        .nutrition-info { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 12px; margin-top: 30px; }
        .nutrition-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 20px; margin-top: 15px; }
        .nutrition-item { text-align: center; }
        .nutrition-item .value { font-size: 1.5rem; font-weight: 700; }
        .nutrition-item .label { font-size: 0.85rem; opacity: 0.9; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="recipe-header">
            <div class="recipe-image">
                <div class="recipe-title-overlay">
                    <h1>Tandoori Chicken Protein Bowl</h1>
                    <p>Lean Protein Powerhouse</p>
                </div>
            </div>
            <div class="recipe-meta">
                <div class="meta-item"><i class="far fa-clock"></i> <strong>40 mins</strong></div>
                <div class="meta-item"><i class="fas fa-fire"></i> <strong>420 kcal</strong></div>
                <div class="meta-item"><i class="fas fa-utensils"></i> <strong>2 servings</strong></div>
                <div class="meta-item"><i class="fas fa-signal"></i> <strong>Medium</strong></div>
            </div>
        </div>

        <div class="recipe-content">
            <h2>About This Recipe</h2>
            <p>This Tandoori Chicken Protein Bowl combines lean, marinated chicken with fresh mint chutney, cooling cucumber raita, and fiber-rich brown rice. High in protein and packed with flavor, it's perfect for muscle building and post-workout recovery.</p>

            <h2>Ingredients</h2>
            <h3>For Tandoori Chicken:</h3>
            <ul class="ingredients-list">
                <li><i class="fas fa-check-circle"></i> 500g chicken breast, cut into pieces</li>
                <li><i class="fas fa-check-circle"></i> 1 cup Greek yogurt</li>
                <li><i class="fas fa-check-circle"></i> 2 tbsp tandoori masala</li>
                <li><i class="fas fa-check-circle"></i> 1 tbsp ginger-garlic paste</li>
                <li><i class="fas fa-check-circle"></i> 1 tbsp lemon juice</li>
                <li><i class="fas fa-check-circle"></i> 1 tsp red chili powder</li>
                <li><i class="fas fa-check-circle"></i> Salt to taste</li>
                <li><i class="fas fa-check-circle"></i> 1 tbsp oil</li>
            </ul>
            <h3>For the Bowl:</h3>
            <ul class="ingredients-list">
                <li><i class="fas fa-check-circle"></i> 1 cup cooked brown rice</li>
                <li><i class="fas fa-check-circle"></i> 1/2 cup cucumber raita</li>
                <li><i class="fas fa-check-circle"></i> 3 tbsp mint chutney</li>
                <li><i class="fas fa-check-circle"></i> Sliced onions and lemon wedges for garnish</li>
            </ul>

            <h2>Instructions</h2>
            <div class="instructions">
                <ol>
                    <li><strong>Marinate the Chicken:</strong> Mix yogurt, tandoori masala, ginger-garlic paste, lemon juice, chili powder, and salt. Coat chicken pieces thoroughly. Marinate for at least 2 hours (or overnight).</li>
                    <li><strong>Grill or Bake:</strong> Preheat oven to 200°C (400°F) or use a grill. Place marinated chicken on a baking tray or grill. Cook for 25-30 minutes, turning halfway, until charred and cooked through.</li>
                    <li><strong>Prepare Brown Rice:</strong> Cook brown rice according to package instructions.</li>
                    <li><strong>Make Raita:</strong> Mix grated cucumber with yogurt, roasted cumin powder, and salt.</li>
                    <li><strong>Assemble Bowl:</strong> Place brown rice at the bottom, top with tandoori chicken, add dollops of mint chutney and raita.</li>
                    <li><strong>Garnish and Serve:</strong> Add sliced onions and lemon wedges. Serve hot!</li>
                </ol>
            </div>

            <div class="nutrition-info">
                <h2 style="color: white; margin-bottom: 10px;">Nutritional Information (Per Serving)</h2>
                <div class="nutrition-grid">
                    <div class="nutrition-item">
                        <div class="value">420</div>
                        <div class="label">Calories</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">45g</div>
                        <div class="label">Protein</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">38g</div>
                        <div class="label">Carbs</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">10g</div>
                        <div class="label">Fat</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">5g</div>
                        <div class="label">Fiber</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
