<?php
require_once 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username    = trim($_POST['username']);
    $email       = trim($_POST['email']);
    $rawPassword = $_POST['password'];

    if (strlen($rawPassword) < 8 || !preg_match('/[A-Z]/', $rawPassword) || !preg_match('/\d/', $rawPassword) || !preg_match('/[^A-Za-z0-9]/', $rawPassword)) {
        $error = "Password must be at least 8 characters and include an uppercase letter, a number, and a symbol.";
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
                    <input type="password" id="password" name="password" placeholder="At least 8 chars, uppercase, number, symbol" autocomplete="new-password" minlength="8" required
                           oninput="checkPasswordRequirements(this)">
                </div>
                <small id="pw-hint" style="color:#e74c3c;font-size:12px;display:none;margin-top:4px;">
                    <i class="fa-solid fa-circle-exclamation"></i> Password must be at least 8 characters and include an uppercase letter, a number, and a symbol.
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
function checkPasswordRequirements(input) {
    const hint = document.getElementById('pw-hint');
    const value = input.value;
    const validLength = value.length >= 8;
    const hasUppercase = /[A-Z]/.test(value);
    const hasNumber = /\d/.test(value);
    const hasSymbol = /[^A-Za-z0-9]/.test(value);

    if (value.length > 0 && !(validLength && hasUppercase && hasNumber && hasSymbol)) {
        hint.style.display = 'block';
    } else {
        hint.style.display = 'none';
    }
}

document.querySelector('form').addEventListener('submit', function (event) {
    const password = document.getElementById('password').value;
    const meetsRequirements = password.length >= 8 && /[A-Z]/.test(password) && /\d/.test(password) && /[^A-Za-z0-9]/.test(password);

    if (!meetsRequirements) {
        document.getElementById('pw-hint').style.display = 'block';
        event.preventDefault();
    }
});
</script>
</body>
</html>
