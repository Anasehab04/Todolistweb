<?php
require_once 'config.php';
session_start();

$step = 1;
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check Email
    if (isset($_POST['check_email'])) {

        $email = trim($_POST['email']);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['reset_email'] = $email;
            $step = 2;
        } else {
            $error = "Email not found";
        }
    }

    // Reset Password
    if (isset($_POST['reset_password'])) {

        if (!isset($_SESSION['reset_email'])) {
            header("Location: forgot_password.php");
            exit();
        }

        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (strlen($new_password) < 8) {

            $error = "Password must be at least 8 characters long";
            $step = 2;

        } elseif ($new_password != $confirm_password) {

            $error = "Passwords do not match";
            $step = 2;

        } else {

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([
                $hashed_password,
                $_SESSION['reset_email']
            ]);

            unset($_SESSION['reset_email']);

            $message = "Password updated successfully";
            $step = 1;
        }
    }
}

if (isset($_SESSION['reset_email'])) {
    $step = 2;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }

    </style>
</head>

<body>

<div class="auth-wrapper">

    <div class="auth-card">

        <div class="auth-logo">
            <div class="logo-icon">🔒</div>
            <span>Todo App</span>
        </div>

        <?php if (!empty($message)) { ?>
            <div class="success">
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="error">
                <?php echo $error; ?>
            </div>
        <?php } ?>

        <?php if ($step == 1) { ?>

            <h2>Forgot Password</h2>

            <p class="auth-subtitle">
                Enter your email to reset password
            </p>

            <form method="POST">

                <div class="form-group">

                    <label>Email Address</label>

                    <div class="input-wrap">

                        <input
                            type="email"
                            name="email"
                            placeholder="Enter your email"
                            required
                        >

                    </div>

                </div>

                <button
                    type="submit"
                    name="check_email"
                    class="btn-primary"
                >
                    Check Email
                </button>

            </form>

        <?php } else { ?>

            <h2>Create New Password</h2>

            <p class="auth-subtitle">
                Enter your new password
            </p>

            <form method="POST">

                <div class="form-group">

                    <label>New Password</label>

                    <div class="input-wrap">

                        <input
                            type="password"
                            name="new_password"
                            id="new_password"
                            placeholder="At least 8 characters"
                            minlength="8"
                            oninput="checkFPLength(this)"
                            required
                        >

                    </div>
                    <small id="fp-pw-hint" style="color:#e74c3c;font-size:12px;display:none;margin-top:4px;"><i class="fa-solid fa-circle-exclamation"></i> Password must be at least 8 characters</small>

                </div>

                <div class="form-group">

                    <label>Confirm Password</label>

                    <div class="input-wrap">

                        <input
                            type="password"
                            name="confirm_password"
                            placeholder="Confirm Password"
                            required
                        >

                    </div>

                </div>

                <button
                    type="submit"
                    name="reset_password"
                    class="btn-primary"
                >
                    Reset Password
                </button>

            </form>

        <?php } ?>

        <div class="auth-footer">

            <a href="login.php">
                Back to Login
            </a>

        </div>

    </div>

</div>

<script>
function checkFPLength(input) {
    const hint = document.getElementById('fp-pw-hint');
    if (input.value.length > 0 && input.value.length < 8) {
        hint.style.display = 'block';
    } else {
        hint.style.display = 'none';
    }
}
</script>
</body>