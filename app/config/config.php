<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'recipe_planner');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application paths
define('BASE_URL', 'http://localhost/recipe_meal_planner/');
define('IMAGE_UPLOAD_PATH', __DIR__ . '/../static/images/');

// Session and security
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);