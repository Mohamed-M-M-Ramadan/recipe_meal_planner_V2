<?php
require_once __DIR__ . '/config/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_name = trim($_POST['plan_name'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if ($plan_name && $start_date && $end_date) {
        $stmt = $pdo->prepare("INSERT INTO meal_plans (user_id, plan_name, start_date, end_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $plan_name, $start_date, $end_date]);
        $success = "Meal plan created successfully!";
    } else {
        $error = "Please fill in all fields.";
    }
}

// Fetch user's meal plans
$stmt = $pdo->prepare("SELECT * FROM meal_plans WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$meal_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f8f9fa;
        margin: 0;
        padding: 0;
    }
    h1, h2 {
        color: #2c3e50;
        text-align: center;
    }
    form {
        background: #fff;
        max-width: 400px;
        margin: 30px auto;
        padding: 24px 32px 18px 32px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(44,62,80,0.08);
    }
    label {
        display: block;
        margin-bottom: 6px;
        color: #34495e;
        font-weight: bold;
    }
    input[type="text"], input[type="date"] {
        width: 100%;
        padding: 8px 10px;
        margin-bottom: 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 15px;
    }
    button[type="submit"] {
        background: #27ae60;
        color: #fff;
        border: none;
        padding: 10px 18px;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.2s;
    }
    button[type="submit"]:hover {
        background: #219150;
    }
    ul {
        max-width: 500px;
        margin: 30px auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(44,62,80,0.08);
        padding: 18px 28px;
        list-style: none;
    }
    ul li {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        color: #2c3e50;
    }
    ul li:last-child {
        border-bottom: none;
    }
    p[style*="color:green"], p[style*="color:red"] {
        text-align: center;
        font-weight: bold;
    }
</style>
<head>
    <title>Create Meal Plan</title>
    <link rel="stylesheet" href="../public/styles.css">
</head>
<body>
    <h1>Create a Meal Plan</h1>
    <?php if (!empty($success)): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label for="plan_name">Meal Plan Name:</label>
        <input type="text" name="plan_name" id="plan_name" required><br><br>
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" id="start_date" required><br><br>
        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" id="end_date" required><br><br>
        <button type="submit">Create Meal Plan</button>
    </form>

    <h2>Your Meal Plans</h2>
    <ul>
        <?php foreach ($meal_plans as $plan): ?>
            <li>
                <?= htmlspecialchars($plan['plan_name']) ?> (<?= htmlspecialchars($plan['start_date']) ?> to <?= htmlspecialchars($plan['end_date']) ?>)
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>