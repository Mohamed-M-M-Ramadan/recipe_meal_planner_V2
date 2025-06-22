<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/database/db_connection.php';
require_once __DIR__ . '/database/models.php';

echo "<h1>Model Test</h1>";

// Test UserModel
try {
    $userModel = new UserModel();
    
    echo "<h2>UserModel Test</h2>";
    $testUser = $userModel->findByUsername('testuser');
    if ($testUser) {
        echo "Found user: " . htmlspecialchars($testUser['username']);
    } else {
        echo "Test user not found - this is normal if no users exist";
    }
    
    // Test RecipeModel
    $recipeModel = new RecipeModel();
    echo "<h2>RecipeModel Test</h2>";
    $recipes = $recipeModel->getPublicRecipes();
    echo "Found " . count($recipes) . " public recipes";
    
} catch (PDOException $e) {
    echo "<div style='color:red; padding: 20px; border: 1px solid red;'>";
    echo "Database Error: " . $e->getMessage();
    echo "<br>Check DB_HOST, DB_NAME, DB_USER, and DB_PASS in config.php";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='color:red; padding: 20px; border: 1px solid red;'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}