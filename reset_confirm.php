<?php
session_start();
require_once 'dbconnection.php';
require_once 'auth_functions.php';

// Redirect if OTP not set
if (!isset($_SESSION['reset_otp'])) {
    header("Location: reset_password.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'];
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($otp !== $_SESSION['reset_otp']) {
        $error = "Invalid OTP code";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords don't match";
    } elseif (!validatePassword($newPassword)) {
        $error = "Password must be at least 8 characters with uppercase, lowercase, number, and special character";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $_SESSION['reset_email']]);
        
        // Clear reset session
        unset($_SESSION['reset_otp']);
        unset($_SESSION['reset_email']);
        
        header("Location: signin.php?reset=success");
        exit;
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
        input[type="email"],
        input[type="password"], 
        input[type="text"] {
            width: calc(100% - 16px);
            padding: 8px;
            margin-bottom: 14px;
            border-radius: 6px;
            border: 1px solid var(--brown);
            background-color: var(--peach);
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: var(--falu-red);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
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
        .error {
            color: var(--falu-red);
            text-align: center;
            margin-bottom: 16px;
        }
        .success {
            color: var(--lion);
            text-align: center;
            margin-bottom: 16px;
        }
        .form-container p {
            text-align: center;
        }
        .form-container p a {
            color: var(--falu-red);
            text-decoration: none;
            font-weight: 600;
        }
        .form-container p a:hover {
            color: var(--lion);
        }
        input[type="checkbox"] {
            accent-color: var(--falu-red);
        }
        input:focus, select:focus {
            border-color: var(--falu-red);
            outline: none;
        }
        input[type="text"],
        input[type="email"],    

        input[type="tel"],
        input[type="password"],     
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 14px;
            border-radius: 6px;
            border: 1px solid var(--brown);
            background-color: var(--peach);
            color: var(--bistre);
            box-shadow: 0 1px 4px rgba(67, 40, 24, 0.05);
            transition: border 0.2s;
        }   
        input:focus, select:focus {
            border-color: var(--falu-red);
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: var(--falu-red);
            color: var(--peach);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1em;
            box-shadow: 0 2px 8px rgba(67, 40, 24, 0.08);
            transition: background 0.2s, color 0.2s;
        }
        button:hover {
            background-color: var(--lion);
            color: var(--falu-red);
        }
        input[type="checkbox"] {
            accent-color: var(--falu-red);
        }

        .form-container p {
            text-align: center;
        }
        .form-container p a {
            color: var(--falu-red);
            text-decoration: none;
            font-weight: 600;
        }
        .form-container p a:hover {
            text-decoration: underline;
            color: var(--bistre);
        }
        
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Reset Password</h2>
        <?php if ($error): ?>
            <p style="color: var(--falu-red);"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="otp">Enter OTP:</label>
            <input type="text" id="otp" name="otp" required>
            
            <label for="password">New Password:</label>
            <input type="password" id="password" name="password" required>
            
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <button type="submit">Update Password</button>
        </form>
    </div>
</body>
</html>