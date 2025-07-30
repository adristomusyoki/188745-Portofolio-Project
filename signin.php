<?php
session_start();
require_once 'dbconnection.php';

// Check database connection
if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Generate 2FA code
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['user_id'] = $user['id'];
                
                // mail($email, "Your OTP", "Code: $otp");
                header("Location: verify_otp.php");
                exit;
            }
        }
        echo "Invalid credentials!";
        $stmt->close();
    } else {
        echo "Database query error!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
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
        <h2>Sign In</h2>
        <form id="signinForm">
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <label>
                <input type="checkbox" name="remember">
                Remember Me
            </label><br><br>
              <button type="submit">Sign In</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
    </div>
    <script>
        
    document.getElementById('signinForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            alert('Please enter a valid email address.');
            e.preventDefault();
            return;
        }
    });
    </script>
</body>
</html>