<?php
require_once __DIR__ . '/../database/models.php';

class AuthService {
    public static function login($username, $password) {
        $userModel = new UserModel();
        $user = $userModel->findByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_level'] = $user['user_level_id'];
            return true;
        }
        return false;
    }

    public static function logout() {
        session_unset();
        session_destroy();
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin() {
        return isset($_SESSION['user_level']) && $_SESSION['user_level'] == 1;
    }

     public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}