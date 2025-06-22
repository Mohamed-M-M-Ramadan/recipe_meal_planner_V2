<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/auth/auth_service.php';
require_once __DIR__ . '/database/db_connection.php';
require_once __DIR__ . '/database/models.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!AuthService::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Initialize models
$userModel = new UserModel();
$recipeModel = new RecipeModel();
$mealPlanModel = new MealPlanModel();
$userService = new UserService();

// Get user data
$user = $userModel->getUserById($_SESSION['user_id']);
$userRecipes = $recipeModel->getUserRecipes($_SESSION['user_id']);
$favoriteRecipes = $userService->getFavorites($_SESSION['user_id']);
$mealPlans = $mealPlanModel->getUserMealPlans($_SESSION['user_id']);

// Function to format recipe card
function formatRecipeCard($recipe) {
    return '
    <div class="recipe-card">
        <img src="' . ($recipe['image_path'] ?: 'static/images/placeholder.png') . '" 
             alt="' . htmlspecialchars($recipe['title']) . '" class="recipe-img">
        <div class="recipe-content">
            <h3>' . htmlspecialchars($recipe['title']) . '</h3>
            <p>' . htmlspecialchars(substr($recipe['description'], 0, 100)) . '...</p>
            <div class="recipe-meta">
                <span>Prep: ' . $recipe['prep_time'] . ' min</span>
                <span>Cook: ' . $recipe['cook_time'] . ' min</span>
                <span>Serves: ' . $recipe['servings'] . '</span>
            </div>
            <a href="recipe_detail.php?id=' . $recipe['recipe_id'] . '" class="btn">View Recipe</a>
        </div>
    </div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile - Recipe & Meal Planner</title>
    <link rel="stylesheet" href="static/css/style.css">
    <style>
        .profile-header {
            background: linear-gradient(to right, #2e7d32, #4caf50);
            color: white;
            padding: 2rem 0;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
            margin: 0 auto 1rem;
            background-color: #81c784;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }
        
        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 1.5rem 0;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            min-width: 120px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .profile-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .tab {
            padding: 1rem 2rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-bottom: 3px solid #2e7d32;
            color: #2e7d32;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .section-title {
            color: #2e7d32;
            border-bottom: 2px solid #81c784;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .meal-plan-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .meal-plan-header {
            background-color: #4caf50;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .meal-plan-dates {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .meal-plan-body {
            padding: 1.5rem;
        }
        
        .meal-plan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        
        .meal-day {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .day-title {
            color: #2e7d32;
            font-weight: bold;
            margin-bottom: 0.5rem;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 0.5rem;
        }
        
        .meal-item {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .meal-type {
            font-weight: bold;
            color: #4caf50;
        }
        
        .no-items {
            text-align: center;
            padding: 2rem;
            color: #757575;
            font-style: italic;
        }
        
        .btn-edit {
            background-color: #ff9800;
        }
        
        .btn-edit:hover {
            background-color: #f57c00;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/templates/includes/header.html'; ?>
    
    <div class="profile-header">
        <div class="container">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            </div>
            <h1><?= htmlspecialchars($user['username']) ?></h1>
            <p><?= htmlspecialchars($user['email']) ?></p>
            
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-number"><?= count($userRecipes) ?></div>
                    <div class="stat-label">Recipes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= count($favoriteRecipes) ?></div>
                    <div class="stat-label">Favorites</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= count($mealPlans) ?></div>
                    <div class="stat-label">Meal Plans</div>
                </div>
            </div>
            
            <a href="edit_profile.php" class="btn btn-edit">Edit Profile</a>
        </div>
    </div>
    
    <div class="container">
        <div class="profile-tabs" id="profileTabs">
            <div class="tab active" data-tab="recipes">My Recipes</div>
            <div class="tab" data-tab="favorites">Favorites</div>
            <div class="tab" data-tab="mealplans">Meal Plans</div>
        </div>
        
        <!-- Recipes Tab -->
        <div class="tab-content active" id="recipesTab">
            <h2 class="section-title">My Recipes</h2>
            
            <?php if (count($userRecipes) > 0): ?>
                <div class="recipe-grid">
                    <?php foreach ($userRecipes as $recipe): ?>
                        <?= formatRecipeCard($recipe) ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-items">
                    <p>You haven't created any recipes yet.</p>
                    <a href="recipe_form.php" class="btn">Create Your First Recipe</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Favorites Tab -->
        <div class="tab-content" id="favoritesTab">
            <h2 class="section-title">Favorite Recipes</h2>
            
            <?php if (count($favoriteRecipes) > 0): ?>
                <div class="recipe-grid">
                    <?php foreach ($favoriteRecipes as $recipe): ?>
                        <?= formatRecipeCard($recipe) ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-items">
                    <p>You haven't favorited any recipes yet.</p>
                    <p>Browse <a href="recipes.php">public recipes</a> to find some you like!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Meal Plans Tab -->
        <div class="tab-content" id="mealplansTab">
            <h2 class="section-title">My Meal Plans</h2>
            
            <?php if (count($mealPlans) > 0): ?>
                <?php foreach ($mealPlans as $plan): ?>
                    <div class="meal-plan-card">
                        <div class="meal-plan-header">
                            <h3><?= htmlspecialchars($plan['plan_name']) ?></h3>
                            <div class="meal-plan-dates">
                                <?= date('M d, Y', strtotime($plan['start_date'])) ?> - 
                                <?= date('M d, Y', strtotime($plan['end_date'])) ?>
                            </div>
                        </div>
                        <div class="meal-plan-body">
                            <a href="shopping_list.php?plan_id=<?= $plan['plan_id'] ?>" class="btn">Generate Shopping List</a>
                            <a href="meal_plan_detail.php?id=<?= $plan['plan_id'] ?>" class="btn">View Details</a>
                            
                            <div class="meal-plan-grid">
                                <!-- Sample content for each day -->
                                <div class="meal-day">
                                    <div class="day-title">Monday</div>
                                    <div class="meal-item">
                                        <span class="meal-type">Breakfast:</span> Oatmeal
                                    </div>
                                    <div class="meal-item">
                                        <span class="meal-type">Lunch:</span> Salad
                                    </div>
                                    <div class="meal-item">
                                        <span class="meal-type">Dinner:</span> Pasta
                                    </div>
                                </div>
                                
                                <div class="meal-day">
                                    <div class="day-title">Tuesday</div>
                                    <div class="meal-item">
                                        <span class="meal-type">Breakfast:</span> Smoothie
                                    </div>
                                    <div class="meal-item">
                                        <span class="meal-type">Lunch:</span> Sandwich
                                    </div>
                                    <div class="meal-item">
                                        <span class="meal-type">Dinner:</span> Stir Fry
                                    </div>
                                </div>
                                
                                <!-- More days would be dynamically generated in a real app -->
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-items">
                    <p>You haven't created any meal plans yet.</p>
                    <a href="meal_plan_form.php" class="btn">Create Your First Meal Plan</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include __DIR__ . '/templates/includes/footer.html'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(`${tabId}Tab`).classList.add('active');
                });
            });
            
            // Favorite functionality
            document.querySelectorAll('.favorite-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const recipeId = this.dataset.id;
                    const isFavorite = this.classList.contains('active');
                    
                    fetch('/app/api/user_api.php?action=' + (isFavorite ? 'removeFavorite' : 'addFavorite'), {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({recipeId: recipeId})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.classList.toggle('active');
                            const icon = this.querySelector('i');
                            icon.textContent = isFavorite ? 'favorite_border' : 'favorite';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>