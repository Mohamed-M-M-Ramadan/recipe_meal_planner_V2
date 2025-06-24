<?php
// /app/admin.php

session_start();

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

// Database connection (adjust as needed)
$mysqli = new mysqli('localhost', 'root', '', 'recipe_meal_planner');
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Helper function to escape output
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Handle actions (simplified for demonstration)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Example: Accept or reject a recipe
    if (isset($_POST['recipe_action'], $_POST['recipe_id'])) {
        $id = (int)$_POST['recipe_id'];
        if ($_POST['recipe_action'] === 'accept') {
            $mysqli->query("UPDATE recipes SET status='public' WHERE id=$id");
        } elseif ($_POST['recipe_action'] === 'reject') {
            $mysqli->query("UPDATE recipes SET status='rejected' WHERE id=$id");
        }
    }
    // Example: Add category
    if (isset($_POST['add_category']) && !empty($_POST['category_name'])) {
        $name = $mysqli->real_escape_string($_POST['category_name']);
        $mysqli->query("INSERT INTO categories (name) VALUES ('$name')");
    }
    // Example: Delete user
    if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
        $id = (int)$_POST['user_id'];
        $mysqli->query("DELETE FROM users WHERE id=$id");
    }
    // Add more actions as needed...
}

// Fetch data for display
$pending_recipes = $mysqli->query("SELECT * FROM recipes WHERE status='pending'")->fetch_all(MYSQLI_ASSOC);
$users = $mysqli->query("SELECT * FROM users")->fetch_all(MYSQLI_ASSOC);
$categories = $mysqli->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
$meal_plans = $mysqli->query("SELECT * FROM meal_plans")->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { margin-top: 40px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f0f0f0; }
        form.inline { display: inline; }
    </style>
</head>
<body>
    <h1>Admin Panel</h1>

    <h2>Pending Recipes</h2>
    <table>
        <tr><th>ID</th><th>Title</th><th>Author</th><th>Actions</th></tr>
        <?php foreach ($pending_recipes as $recipe): ?>
        <tr>
            <td><?= h($recipe['id']) ?></td>
            <td><?= h($recipe['title']) ?></td>
            <td><?= h($recipe['author_id']) ?></td>
            <td>
                <form class="inline" method="post">
                    <input type="hidden" name="recipe_id" value="<?= h($recipe['id']) ?>">
                    <button name="recipe_action" value="accept">Accept</button>
                    <button name="recipe_action" value="reject">Reject</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Users</h2>
    <table>
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th></tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= h($user['id']) ?></td>
            <td><?= h($user['username']) ?></td>
            <td><?= h($user['email']) ?></td>
            <td><?= h($user['role']) ?></td>
            <td>
                <form class="inline" method="post" onsubmit="return confirm('Delete user?');">
                    <input type="hidden" name="user_id" value="<?= h($user['id']) ?>">
                    <button name="delete_user" value="1">Delete</button>
                </form>
                <!-- Add edit user functionality as needed -->
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Categories</h2>
    <table>
        <tr><th>ID</th><th>Name</th></tr>
        <?php foreach ($categories as $cat): ?>
        <tr>
            <td><?= h($cat['id']) ?></td>
            <td><?= h($cat['name']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <form method="post">
        <input type="text" name="category_name" placeholder="New Category" required>
        <button name="add_category" value="1">Add Category</button>
    </form>

    <h2>Meal Plans</h2>
    <table>
        <tr><th>ID</th><th>Name</th><th>User</th></tr>
        <?php foreach ($meal_plans as $plan): ?>
        <tr>
            <td><?= h($plan['id']) ?></td>
            <td><?= h($plan['name']) ?></td>
            <td><?= h($plan['user_id']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- Add more admin/moderator features as needed -->

</body>
</html>