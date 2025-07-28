<?php
session_start();
require_once 'dbconnection.php';
require_once 'auth_functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userOTP = $_POST['otp'];
    
    if ($userOTP === $_SESSION['otp']) {
        $_SESSION['authenticated'] = true;
        
        // Redirect to goods.php instead of main page
        header("Location: goods.php");
        exit;
    } else {
        $error = "Invalid OTP code";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
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
        <h2>Verify OTP</h2>
        <?php if ($error): ?>
            <p style="color: var(--falu-red);"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="otp">Enter OTP sent to your email:</label>
            <input type="text" id="otp" name="otp" required autocomplete="off">
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>