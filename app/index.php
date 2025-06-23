<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/auth/auth_service.php';
require_once __DIR__ . '/database/db_connection.php';

// Initialize authentication
AuthService::init();

// Route handling
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? '';

// Set up common data
$data = [];

switch ($page) {
    case 'login':
        if (AuthService::isLoggedIn()) header('Location: ?page=profile');
        $content = __DIR__ . '/templates/login.html';
        break;
        
    case 'register':
        if (AuthService::isLoggedIn()) header('Location: ?page=profile');
        $content = __DIR__ . '/templates/register.html';
        break;
        
    case 'recipes':
        require_once __DIR__ . '/database/models.php';
        $recipeModel = new RecipeModel();
        $data['recipes'] = $recipeModel->getPublicRecipes();
        $content = __DIR__ . '/templates/recipes/recipe_list.html';
        break;
        
    case 'recipe':
        if (empty($_GET['id'])) header('Location: ?page=recipes');
        require_once __DIR__ . '/database/models.php';
        $recipeModel = new RecipeModel();
        $data['recipe'] = $recipeModel->getRecipeById($_GET['id']);
        $content = __DIR__ . '/templates/recipes/recipe_detail.html';
        break;
        
    case 'meal_plans':
        if (!AuthService::isLoggedIn()) header('Location: ?page=login');
        require_once __DIR__ . '/database/models.php';
        $mealPlanModel = new MealPlanModel();
        $data['plans'] = $mealPlanModel->getUserMealPlans($_SESSION['user_id']);
        $content = __DIR__ . '/templates/meal_plans/meal_plan_list.html';
        break;
        
    case 'shopping_list':
        if (!AuthService::isLoggedIn()) header('Location: ?page=login');
        if (empty($_GET['plan_id'])) header('Location: ?page=meal_plans');
        
        require_once __DIR__ . '/services/shopping_list_service.php';
        $shoppingService = new ShoppingListService(Database::getInstance());
        $items = $shoppingService->generateFromMealPlan($_GET['plan_id']);
        $data['items'] = $shoppingService->consolidateIngredients($items);
        $content = __DIR__ . '/templates/shopping_list/shopping_list.html';
        break;
        
    case 'admin':
        if (!AuthService::isAdmin()) header('Location: ?page=home');
        require_once __DIR__ . '/database/models.php';
        $recipeModel = new RecipeModel();
        $data['pendingRecipes'] = $recipeModel->getRecipesByStatus('pending');
        $content = __DIR__ . '/templates/users/admin_dashboard.html';
        break;
        
    case 'profile':
        if (!AuthService::isLoggedIn()) header('Location: ?page=login');
        require_once __DIR__ . '/database/models.php';
        $userModel = new UserModel();
        $recipeModel = new RecipeModel();

        $data['user'] = $userModel->getUserById($_SESSION['user_id']);
        $data['userRecipes'] = $recipeModel->getUserRecipes($_SESSION['user_id']);
        $content = __DIR__ . '/templates/users/user_profile.html';
        break;
        
    default:
        require_once $content = __DIR__ . '/templates/base.html';
}

// Render the template
include __DIR__ . '/templates/index.html';