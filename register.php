<?php
require_once 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username    = trim($_POST['username']);
    $email       = trim($_POST['email']);
    $rawPassword = $_POST['password'];

    if (strlen($rawPassword) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address";
    } else {
        $password = password_hash($rawPassword, PASSWORD_DEFAULT);
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = "Email already registered";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$username, $email, $password]);
                header("Location: login.php");
                exit();
            } catch(PDOException $e) {
                $error = "Username already exists";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Todo List Project</title>
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

        <h2>Create account</h2>
        <p class="auth-subtitle">Start organizing your tasks today</p>

        <?php if (isset($error)): ?>
            <div class="error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-user icon"></i>
                    <input type="text" id="username" name="username" placeholder="Choose a username" autocomplete="off" required>
                </div>
            </div>

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
                    <input type="password" id="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required
                           oninput="checkPasswordLength(this)">
                </div>
                <small id="pw-hint" style="color:#e74c3c;font-size:12px;display:none;margin-top:4px;">
                    <i class="fa-solid fa-circle-exclamation"></i> Password must be at least 8 characters
                </small>
            </div>

            <button type="submit" class="btn-primary" style="margin-top:8px;">
                <i class="fa-solid fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</div>
<script>
function checkPasswordLength(input) {
    const hint = document.getElementById('pw-hint');
    if (input.value.length > 0 && input.value.length < 8) {
        hint.style.display = 'block';
    } else {
        hint.style.display = 'none';
    }
}
</script>
</body>
</html>