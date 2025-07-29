<?php
session_start();
require_once 'dbconnection.php';
require_once 'auth_functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $otp = generateOTP();
        sendOTP($email, $otp);
        
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_email'] = $email;
        
        header("Location: reset_confirm.php");
        exit;
    } else {
        $error = "Email not found";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        :root {
            --falu-red: #6f1d1bff;
            --lion: #bb9457ff;
            --bistre: #432818ff;
            --brown: #99582aff;
            --peach: #ffe6a7ff;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--peach);
            margin: 0;
            padding: 20px;
            color: var(--bistre);
        }
        .form-container {
            max-width: 400px;
            margin: auto;
            background: var(--lion);
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(67, 40, 24, 0.10);
            color: var(--bistre);
        }
        h2 {
            text-align: center;
            color: var(--falu-red);
            font-weight: 700;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--falu-red);
            font-weight: 600;
        }
        input[type="email"] {
            width: calc(100% - 16px);
            padding: 8px;
            border-radius: 4px;
            border: 1px solid var(--bistre);
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: var(--falu-red);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: var(--lion);
        }
        a {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: var(--falu-red);
            text-decoration: none;
        }
        a:hover {
            color: var(--lion);
        }
    </style>
    
</head>
<body>
    <div class="form-container">
        <h2>Password Reset</h2>
        <?php if ($error): ?>
            <p style="color: var(--falu-red);"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="email">Enter your email:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Send Reset Code</button>
        </form>
        <p><a href="signin.php">Back to Login</a></p>
    </div>
</body>
</html>