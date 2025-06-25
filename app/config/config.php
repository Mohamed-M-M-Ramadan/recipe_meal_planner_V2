<?php
// Database configuration
define('DB_HOST', 'sql103.infinityfree.com');
define('DB_NAME', 'if0_39316706_recipe_planner');
define('DB_USER', 'if0_39316706');
define('DB_PASS', 'jBMQYO1cygA');

// Application paths
define('BASE_URL', 'http://localhost/recipe_meal_planner/');
define('IMAGE_UPLOAD_PATH', __DIR__ . '/../static/images/');

// Session and security
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'sql103.infinityfree.com';
$db   = 'if0_39316706_recipe_planner';
$user = 'if0_39316706';
$pass = 'jBMQYO1cyg';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}