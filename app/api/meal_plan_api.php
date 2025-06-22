<?php
require_once __DIR__ . '/../../auth/auth_service.php';
require_once __DIR__ . '/../../services/meal_plan_service.php';

if (!AuthService::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance();
$mealPlanService = new MealPlanService($db);

// Create new meal plan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $planId = $mealPlanService->createMealPlan(
        $_SESSION['user_id'],
        $data['planName'],
        $data['startDate'],
        $data['endDate']
    );
    
    if ($planId) {
        echo json_encode(['status' => 'success', 'planId' => $planId]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error']);
    }
    exit;
}

// Add recipe to plan
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($mealPlanService->addRecipeToPlan(
        $data['planId'],
        $data['recipeId'],
        $data['dayOfWeek'],
        $data['mealType']
    )) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error']);
    }
    exit;
}