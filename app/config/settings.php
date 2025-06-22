<?php
// app/config/settings.php

// Database configuration
define('DB_HOST', 'localhost'); // Your database host
define('DB_NAME', 'recipe_planner_db'); // The database name you created in phpMyAdmin
define('DB_USER', 'root'); // Your MySQL username (default for XAMPP)
define('DB_PASS', ''); // Your MySQL password (default for XAMPP, usually empty)

// Application settings
define('APP_NAME', 'Recipe & Meal Planner');
define('APP_URL', 'http://localhost/recipe_meal_planner'); // Base URL for your application

// Error reporting (for development, set to 0 for production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session management (will be handled by auth_service.php usually)
// If you want sessions available globally, uncomment this:
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>