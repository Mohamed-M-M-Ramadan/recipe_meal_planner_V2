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

// Database connection
$host = 'localhost';
$db   = 'recipe_planner';
$user = 'root';
$pass = '';
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