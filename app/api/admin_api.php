<?php
require_once __DIR__ . '/../../auth/auth_service.php';
require_once __DIR__ . '/../../database/models.php';

// Start session and check admin status
session_start();
if (!AuthService::isAdmin()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

$db = Database::getInstance();
$recipeModel = new RecipeModel($db);

if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'approve') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    
    if ($id && $recipeModel->updateStatus($id, 'public')) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update recipe status']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    
    if ($id && $recipeModel->delete($id)) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete recipe']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['pending'])) {
    $recipes = $recipeModel->getRecipesByStatus('pending');
    echo json_encode(['status' => 'success', 'data' => $recipes]);
    exit;
}

// Default response
http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);