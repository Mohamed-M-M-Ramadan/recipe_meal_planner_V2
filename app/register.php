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
$username = $email = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($errors)) {
        try {
            $userModel = new UserModel();
            
            // Check if username or email exists
            if ($userModel->findByUsername($username)) {
                $errors[] = 'Username already taken';
            } 
            
            if ($userModel->findByEmail($email)) {
                $errors[] = 'Email already registered';
            }
            
            if (empty($errors)) {
                // Create new user
                if ($userModel->create($username, $email, $password)) {
                    // ... [rest remains the same] ...
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
    
    $error = implode('<br>', $errors);
}

// Display registration form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Recipe & Meal Planner</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Create New Account</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" onsubmit="return validateRegistrationForm()">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?= htmlspecialchars($username) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Register</button>
                    <p class="mt-3">
                        Already have an account? <a href="login.php">Login here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script src="static/js/validation.js"></script>
    <script>
    function validateRegistrationForm() {
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;
        
        if (!username || username.length < 3) {
            alert('Username must be at least 3 characters');
            return false;
        }
        
        if (!email || !email.includes('@')) {
            alert('Valid email is required');
            return false;
        }
        
        if (!password || password.length < 6) {
            alert('Password must be at least 6 characters');
            return false;
        }
        
        if (password !== confirm) {
            alert('Passwords do not match');
            return false;
        }
        
        return true;
    }
    </script>
</body>
</html>