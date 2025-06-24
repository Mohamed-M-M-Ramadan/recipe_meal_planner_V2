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

$recipeModel = new RecipeModel();
$ingredientModel = new IngredientModel();
$categoryModel = new CategoryModel();

$recipe = null;
$ingredients = [];
$categories = $categoryModel->getAll();

// Check if editing existing recipe
if (isset($_GET['id'])) {
    $recipeId = (int)$_GET['id'];
    $recipe = $recipeModel->getRecipeById($recipeId);
    
    // Check if recipe belongs to user or user is admin
    if (!$recipe || ($recipe['user_id'] != $_SESSION['user_id'] && !AuthService::isAdmin())) {
        header('Location: recipes.php');
        exit;
    }
    
    // Get recipe ingredients
    $ingredients = $ingredientModel->getIngredientsForRecipe($recipeId);
}

// Process form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $instructions = trim($_POST['instructions']);
    $prepTime = (int)$_POST['prep_time'];
    $cookTime = (int)$_POST['cook_time'];
    $servings = (int)$_POST['servings'];
    $status = $_POST['status'];
    $ingredientsData = $_POST['ingredients'] ?? [];
    
    // Validate
    if (empty($title)) {
        $errors[] = 'Recipe title is required';
    }
    
    if (empty($instructions)) {
        $errors[] = 'Instructions are required';
    }
    
    if (empty($errors)) {
        $recipeData = [
            'user_id' => $_SESSION['user_id'],
            'title' => $title,
            'description' => $description,
            'instructions' => $instructions,
            'prep_time' => $prepTime,
            'cook_time' => $cookTime,
            'servings' => $servings,
            'status' => $status
        ];

    // Handle image upload if applicable
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = IMAGE_UPLOAD_PATH;
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $recipeData['image_path'] = $fileName;
        }
    }

    if ($recipe) {
        // Edit mode: update recipe
        $recipeId = $recipe['recipe_id'];
        $recipeModel->update($recipeId, $recipeData);
    } else {
        // Create mode: insert new recipe
        $recipeId = $recipeModel->create(
            $recipeData['user_id'],
            $recipeData['title'],
            $recipeData['description'],
            $recipeData['instructions'],
            $recipeData['prep_time'],
            $recipeData['cook_time'],
            $recipeData['servings'],
            $recipeData['status'],
            $recipeData['image_path'] ?? null
        );
    }

    // Now that we definitely have a $recipeId, validate and save ingredients
    if ($recipeId && is_numeric($recipeId)) {
        $ingredientsData = [];
        foreach ($_POST['ingredients'] as $ing) {
            $ingredientsData[] = [
                'name' => $ing['name'],
                'quantity' => $ing['quantity'],
                'unit' => $ing['unit'],
                'notes' => $ing['notes'] ?? ''
            ];
        }

        $recipeModel->saveIngredients($recipeId, $ingredientsData);

        header("Location: recipe_detail.php?id=$recipeId");
        exit;
    } else {
        $errors[] = 'Failed to save recipe';
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $recipe ? 'Edit' : 'Create' ?> Recipe - Recipe & Meal Planner</title>
    <link rel="stylesheet" href="static/css/style.css">
    <script src="static/js/recipe_forms.js"></script>
</head>
<body>
    <?php foreach ($ingredients as $index => $ing): ?>
        <div class="ingredient-row form-row mb-2">
            <div class="col-md-5">
                <input type="text" name="ingredients[<?= $index ?>][name]" class="form-control" placeholder="Ingredient" 
                    value="<?= htmlspecialchars($ing['ingredient_name']) ?>" required>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" name="ingredients[<?= $index ?>][quantity]" class="form-control" placeholder="Qty" 
                    value="<?= htmlspecialchars($ing['quantity']) ?>" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="ingredients[<?= $index ?>][unit]" class="form-control" placeholder="Unit" 
                    value="<?= htmlspecialchars($ing['unit_of_measure']) ?>">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-ingredient">Remove</button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php include __DIR__ . '/templates/includes/header.html'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2><?= $recipe ? 'Edit Recipe' : 'Create New Recipe' ?></h2>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Recipe Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required 
                               value="<?= htmlspecialchars($recipe['title'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($recipe['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="instructions">Instructions *</label>
                        <textarea id="instructions" name="instructions" class="form-control" rows="6" required><?= htmlspecialchars($recipe['instructions'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="prep_time">Prep Time (minutes)</label>
                            <input type="number" id="prep_time" name="prep_time" class="form-control" min="0" 
                                   value="<?= htmlspecialchars($recipe['prep_time'] ?? '0') ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="cook_time">Cook Time (minutes)</label>
                            <input type="number" id="cook_time" name="cook_time" class="form-control" min="0" 
                                   value="<?= htmlspecialchars($recipe['cook_time'] ?? '0') ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="servings">Servings</label>
                            <input type="number" id="servings" name="servings" class="form-control" min="1" 
                                   value="<?= htmlspecialchars($recipe['servings'] ?? '1') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Recipe Image</label>
                        <input type="file" id="image" name="image" class="form-control-file">
                        <?php if ($recipe && $recipe['image_path']): ?>
                            <img src="static/images/recipes/<?= $recipe['image_path'] ?>" alt="Current Image" class="img-thumbnail mt-2" style="max-width: 200px;">
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Visibility</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="statusPrivate" value="private" 
                                    <?= (!$recipe || $recipe['status'] == 'private') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="statusPrivate">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="statusPublic" value="public"
                                    <?= ($recipe && $recipe['status'] == 'public') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="statusPublic">Public</label>
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="mt-4">Ingredients</h3>
                    <div id="ingredients-container">
                        <?php foreach ($ingredients as $index => $ing): ?>
                            <div class="ingredient-row form-row mb-2">
                                <div class="col-md-5">
                                    <input type="text" name="ingredients[<?= $index ?>][name]" class="form-control" placeholder="Ingredient" 
                                           value="<?= htmlspecialchars($ing['ingredient_name']) ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="0.01" name="ingredients[<?= $index ?>][quantity]" class="form-control" placeholder="Qty" 
                                           value="<?= htmlspecialchars($ing['quantity']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="ingredients[<?= $index ?>][unit]" class="form-control" placeholder="Unit" 
                                           value="<?= htmlspecialchars($ing['unit_of_measure']) ?>">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-ingredient">Remove</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="add-ingredient" class="btn btn-secondary mb-3">Add Ingredient</button>
                    
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary"><?= $recipe ? 'Update Recipe' : 'Create Recipe' ?></button>
                        <a href="<?= $recipe ? 'recipe_detail.php?id='.$recipe['recipe_id'] : 'recipes.php' ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/templates/includes/footer.html'; ?>
    
    <script>
        let ingredientIndex = <?= count($ingredients) ?>;
        
        document.getElementById('add-ingredient').addEventListener('click', function() {
            const container = document.getElementById('ingredients-container');
            const row = document.createElement('div');
            row.className = 'ingredient-row form-row mb-2';
            row.innerHTML = `
                <div class="col-md-5">
                    <input type="text" name="ingredients[${ingredientIndex}][name]" class="form-control" placeholder="Ingredient" required>
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="ingredients[${ingredientIndex}][quantity]" class="form-control" placeholder="Qty" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="ingredients[${ingredientIndex}][unit]" class="form-control" placeholder="Unit">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-ingredient">Remove</button>
                </div>
            `;
            container.appendChild(row);
            ingredientIndex++;
            
            // Add event listener to new remove button
            row.querySelector('.remove-ingredient').addEventListener('click', function() {
                row.remove();
            });
        });
        
        // Add event listeners to existing remove buttons
        document.querySelectorAll('.remove-ingredient').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.ingredient-row').remove();
            });
        });
    </script>
</body>
</html>