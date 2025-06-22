<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/auth/auth_service.php';
require_once __DIR__ . '/database/db_connection.php';
require_once __DIR__ . '/database/models.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (AuthService::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$error = '';
$username = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = 'Both username and password are required';
    } else {
        // Attempt login
        $userModel = new UserModel();
        $user = $userModel->findByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_level'] = $user['user_level_id'];
            
            // Set remember me cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + 86400 * 30; // 30 days
                setcookie('remember_token', $token, $expiry, '/');
                
                // Store token in database using model method
                $userModel->updateRememberToken($user['user_id'], $token);
            }
            
            // Redirect to home page
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}

// Display login form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Recipe & Meal Planner</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Login to Your Account</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST" onsubmit="return validateLoginForm()">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?= htmlspecialchars($username) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login</button>
                    <p class="mt-3">
                        Don't have an account? <a href="register.php">Register here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script src="static/js/validation.js"></script>
    <script>
    function validateLoginForm() {
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        if (!username || !password) {
            alert('Both username and password are required');
            return false;
        }
        return true;
    }
    </script>
</body>
</html>