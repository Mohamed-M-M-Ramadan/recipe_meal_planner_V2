<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/auth/auth_service.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to view public recipes.');
}

$current_user_id = $_SESSION['user_id'];

// Fetch public recipes not created by the current user
$sql = "SELECT r.recipe_id, r.title, r.description, u.username 
        FROM recipes r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.user_id != ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$current_user_id]);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Public Recipes</title>
    <link href="static/css/style.css" rel="stylesheet">
</head>
<head>
    <title>Public Recipes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 32px 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 24px;
            text-align: center;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background:rgb(241, 246, 242);
            margin-bottom: 18px;
            padding: 18px 20px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        strong {
            font-size: 1.15em;
            color: #2c3e50;
        }
        .author {
            color: #888;
            font-size: 0.98em;
        }
        p {
            color: #555;
            text-align: center;
        }

    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <ul class="nav-list">
                <li><a href="index.php">Home</a></li>
                <li><a href="my_recipes.php">Your Recipes</a></li>
                <li><a href="public_recipes.php">Public Recipes</a></li>
                <li><a href="meal_plans.php">Meal Plans</a></li>
                <?php if (AuthService::isLoggedIn()): ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                    <?php if (AuthService::isAdmin()): ?>
                        <li><a href="admin.php">Admin</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="container">
        <h1>Public Recipes by Other Users</h1>
        <?php if (empty($recipes)): ?>
            <p>No public recipes found.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($recipes as $recipe): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($recipe['title']); ?></strong>
                        <span class="author">by <?php echo htmlspecialchars($recipe['username']); ?></span><br>
                        <?php echo nl2br(htmlspecialchars($recipe['description'])); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>