<?php
require_once __DIR__ . '/../../auth/auth_service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (AuthService::login($data['username'], $data['password'])) {
        echo json_encode(['status' => 'success', 'user' => $_SESSION['username']]);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
    exit;
}