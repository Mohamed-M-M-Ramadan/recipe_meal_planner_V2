<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/auth/auth_service.php';
require_once __DIR__ . '/database/db_connection.php';
require_once __DIR__ . '/database/models.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id'])) {
    header('Location: recipes.php');
    exit;
}

$recipeId = (int)$_GET['id'];
$recipeModel = new RecipeModel();
$recipe = $recipeModel->getRecipeById($recipeId);

if (!$recipe) {
    header('Location: recipes.php');
    exit;
}

// Check if private recipe belongs to current user
if ($recipe['status'] === 'private' && (!AuthService::isLoggedIn() || $recipe['user_id'] != $_SESSION['user_id'])) {
    header('Location: recipes.php');
    exit;
}

$userModel = new UserModel();
$author = $userModel->getUserById($recipe['user_id']);

$ingredientModel = new IngredientModel();
$ingredients = $ingredientModel->getIngredientsForRecipe($recipeId);

// Check if recipe is favorited
$isFavorite = false;
if (AuthService::isLoggedIn()) {
    $favoriteModel = new FavoriteModel();
    $isFavorite = $favoriteModel->isFavorite($_SESSION['user_id'], $recipeId);
}

// Increment view count
$recipeModel->incrementViews($recipeId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe['title']) ?> - Recipe & Meal Planner</title>
    <link rel="stylesheet" href="static/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/templates/includes/header.html'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1><?= htmlspecialchars($recipe['title']) ?></h1>
                <p class="text-muted">By <?= htmlspecialchars($author['username']) ?> | <?= date('M d, Y', strtotime($recipe['creation_date'])) ?></p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <img src="<?= $recipe['image_path'] ? 'static/images/recipes/'.$recipe['image_path'] : 'static/images/placeholder.png' ?>" 
                             alt="<?= htmlspecialchars($recipe['title']) ?>" class="img-fluid rounded">
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <span class="badge bg-primary">Prep: <?= $recipe['prep_time'] ?> min</span>
                                <span class="badge bg-primary">Cook: <?= $recipe['cook_time'] ?> min</span>
                                <span class="badge bg-primary">Serves: <?= $recipe['servings'] ?></span>
                            </div>
                            <div>
                                <?php if (AuthService::isLoggedIn()): ?>
                                    <button id="favorite-btn" class="btn btn-sm <?= $isFavorite ? 'btn-warning' : 'btn-outline-secondary' ?>" data-id="<?= $recipeId ?>">
                                        <i class="material-icons"><?= $isFavorite ? 'favorite' : 'favorite_border' ?></i>
                                    </button>
                                <?php endif; ?>
                                <?php if (AuthService::isLoggedIn() && ($_SESSION['user_id'] == $recipe['user_id'] || AuthService::isAdmin())): ?>
                                    <a href="recipe_form.php?id=<?= $recipeId ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <p><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h3>Ingredients</h3>
                        <ul class="list-group">
                            <?php foreach ($ingredients as $ing): ?>
                                <li class="list-group-item">
                                    <?= htmlspecialchars($ing['quantity']) ?> 
                                    <?= htmlspecialchars($ing['unit_of_measure']) ?> 
                                    <?= htmlspecialchars($ing['ingredient_name']) ?>
                                    <?php if (!empty($ing['notes'])): ?> 
                                        - <?= htmlspecialchars($ing['notes']) ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h3>Instructions</h3>
                        <div class="instructions">
                            <?= nl2br(htmlspecialchars($recipe['instructions'])) ?>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="meal_plan_form.php?recipe_id=<?= $recipeId ?>" class="btn btn-success">Add to Meal Plan</a>
                </div>
            </div>              
        </div>
    </div>
    
    <?php include __DIR__ . '/templates/includes/footer.html'; ?>
    
    <script>
        // Favorite button functionality
        const favoriteBtn = document.getElementById('favorite-btn');
        if (favoriteBtn) {
            favoriteBtn.addEventListener('click', function() {
                const recipeId = this.dataset.id;
                const isFavorite = this.classList.contains('btn-warning');
                
                fetch('/app/api/user_api.php?action=' + (isFavorite ? 'removeFavorite' : 'addFavorite'), {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({recipeId: recipeId})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        this.classList.toggle('btn-warning');
                        this.classList.toggle('btn-outline-secondary');
                        const icon = this.querySelector('i');
                        icon.textContent = isFavorite ? 'favorite_border' : 'favorite';
                    }
                });
            });
        }
    </script>
</body>
</html>