<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/auth/auth_service.php';
require_once __DIR__ . '/database/models.php';

// Routing logic
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'login':
        $content = __DIR__ . '/templates/login.html';
        break;
    case 'register':
        $content = __DIR__ . '/templates/register.html';
        break;
    case 'recipes':
        $recipeModel = new RecipeModel();
        $recipes = $recipeModel->getPublicRecipes();
        $content = __DIR__ . '/templates/recipes/recipe_list.html';
        break;
    // Additional routes...
    default:
        $content = __DIR__ . '/templates/index.html';
}

// Render base template
include __DIR__ . '/templates/base.html';