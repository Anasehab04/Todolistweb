<?php
require_once 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Todo List Project</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon">✓</div>
            <span>Todo List Project</span>
        </div>

        <h2>Welcome back</h2>
        <p class="auth-subtitle">Sign in to continue managing your tasks</p>

        <?php if (isset($error)): ?>
            <div class="error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-envelope icon"></i>
                    <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="off" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock icon"></i>
                    <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="new-password" required>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top:8px;">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In
            </button>

            <div class="auth-footer" style="margin-top:15px;">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
        </form>
        

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Create one</a>
        </div>
    </div>
</div>
</body>
</html>