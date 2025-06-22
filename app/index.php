<?php
// app/index.php - Main Application Entry Point

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/database/db_connection.php';
require_once __DIR__ . '/database/models.php'; // Include models

// Test database connection (temporary, remove later)
$db_connected = false;
try {
    get_db_connection();
    $db_connected = true;
    // echo "PHP: Database connection successful from index.php!<br>"; // For testing
} catch (Exception $e) {
    // Error already logged in get_db_connection, just display a message
    $db_connected = false;
}

// Very basic routing for now
$page = $_GET['page'] ?? 'home'; // Default to 'home'

// Output buffering to prevent headers already sent issues
ob_start();

// Include header (always first for consistent layout)
include __DIR__ . '/templates/includes/header.html';
include __DIR__ . '/templates/includes/navigation.html';

// Content based on page variable
switch ($page) {
    case 'home':
        include __DIR__ . '/templates/index.html';
        break;
    case 'login':
        include __DIR__ . '/templates/login.html';
        break;
    case 'register':
        include __DIR__ . '/templates/register.html';
        break;
    case 'recipes':
        include __DIR__ . '/templates/recipes/recipe_list.html';
        break;
    // Add more cases as you build out pages
    default:
        // Handle 404 Not Found
        http_response_code(404);
        echo "<div class='container p-4 mx-auto mt-8 text-center text-red-700 bg-red-100 border border-red-400 rounded-lg'>
                <h2 class='text-2xl font-bold'>404 Not Found</h2>
                <p>The page you requested could not be found.</p>
              </div>";
        break;
}

// Include footer (always last)
include __DIR__ . '/templates/includes/footer.html';

ob_end_flush(); // Send buffered output to the browser
?>