<?php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch recipes created by the user
$stmt = $pdo->prepare("SELECT title, description FROM recipes WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $user_id]);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Recipes</title>
    <meta charset="UTF-8">
    
</head>
<body>
    <h1>My Recipes</h1>
    <p>
        <nav>
            <a href="http://localhost/recipe_meal_planner/app">Home</a> |
            <a href="http://localhost/recipe_meal_planner/app/recipes.php">Recipes</a> |
            <a href="http://localhost/recipe_meal_planner/app/profile.php">Profile</a> |
            <a href="http://localhost/recipe_meal_planner/app/logout.php">Logout</a>
        </nav>
    </p>
    <?php if (empty($recipes)): ?>
        <p>You have not created any recipes yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($recipes as $recipe): ?>
                <li>
                    <strong><?= htmlspecialchars($recipe['title']) ?></strong><br>
                    <?= nl2br(htmlspecialchars($recipe['description'])) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        h1 {
            background: #4CAF50;
            color: #fff;
            margin: 0;
            padding: 20px 0;
            text-align: center;
        }
        nav {
            background: #333;
            padding: 10px 0;
            text-align: center;
        }
        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }
        nav a:hover {
            text-decoration: underline;
        }
        ul {
            max-width: 700px;
            margin: 30px auto;
            padding: 0;
            list-style: none;
        }
        li {
            background: #fff;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.07);
        }
        strong {
            font-size: 1.2em;
            color: #333;
        }
        p {
            max-width: 700px;
            margin: 30px auto;
            color: #555;
        }
    </style></style>
</body>
</html>