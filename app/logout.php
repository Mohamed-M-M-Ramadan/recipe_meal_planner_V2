<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/auth/auth_service.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
$_SESSION = [];

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    
    // Remove token from database
    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/database/db_connection.php';
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE Users SET remember_token = NULL WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }
}

// Redirect to login page
header('Location: login.php');
exit;