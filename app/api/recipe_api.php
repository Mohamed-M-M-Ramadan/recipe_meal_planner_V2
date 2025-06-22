<?php
require_once __DIR__ . '/../../auth/auth_service.php';
require_once __DIR__ . '/../../database/models.php';

if (!AuthService::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$recipeModel = new RecipeModel();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $recipes = $recipeModel->getPublicRecipes();
    echo json_encode(['status' => 'success', 'data' => $recipes]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($recipeModel->create(
        $_SESSION['user_id'],
        $data['title'],
        $data['description'],
        $data['instructions'],
        $data['prep_time'],
        $data['cook_time'],
        $data['servings']
    )) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error']);
    }
    exit;
}