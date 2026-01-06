<?php
$s = file_get_contents("fitness_nutrition.php");
$s = preg_replace("/<div[^>]*text-align: center[^>]*>\s*<a[^>]*See All Recipes.*?<\/div>/s", "", $s); 
$new = "
                <!-- Recipe/Food 4 -->
                <div class=\"data-card\">
                    <div class=\"data-img\" style=\"background-image: url('https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&q=80&w=600');\"></div>
                    <div class=\"data-info\">
                        <h3>Quinoa & Veggie Power Bowl</h3>
                        <p>A nutrient-dense bowl with quinoa, chickpeas, roasted veggies, and tahini dressing.</p>
                        <a href=\"healthy_recipes.php\" class=\"btn-link\">View Full Recipe <i class=\"fas fa-arrow-right\"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 5 -->
                <div class=\"data-card\">
                    <div class=\"data-img\" style=\"background-image: url('https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&q=80&w=600');\"></div>
                    <div class=\"data-info\">
                        <h3>Grilled Salmon with Asparagus</h3>
                        <p>Rich in Omega-3s, this simple grilled salmon dish is perfect for a healthy dinner.</p>
                        <a href=\"healthy_recipes.php\" class=\"btn-link\">View Full Recipe <i class=\"fas fa-arrow-right\"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 6 -->
                <div class=\"data-card\">
                    <div class=\"data-img\" style=\"background-image: url('https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?auto=format&fit=crop&q=80&w=600');\"></div>
                    <div class=\"data-info\">
                        <h3>Berry & Yogurt Parfait</h3>
                        <p>Layers of greek yogurt, granola, and fresh mixed berries for a protein-packed sweet treat.</p>
                        <a href=\"healthy_recipes.php\" class=\"btn-link\">View Full Recipe <i class=\"fas fa-arrow-right\"></i></a>
                    </div>
                </div>";
$pos = strpos($s, "<!-- Workouts Section -->");
$grid_end = strrpos(substr($s, 0, $pos), "</div>");
$s = substr_replace($s, $new, $grid_end, 0);
file_put_contents("fitness_nutrition.php", $s);
echo "Done";
