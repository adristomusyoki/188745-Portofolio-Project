<?php
session_start();
require_once 'dbconnection.php';
require_once 'auth_functions.php';

// Database connection check
if ($pdo === null) {
    die('<div class="error">Unable to connect to the database. Please try again later.</div>');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Input sanitization
    $name = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $dob = $_POST['dob'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $interests = $_POST['interests'] ?? null;
    
    // Validate inputs
    if (!preg_match('/^[a-zA-Z\s\-]{2,100}$/', $name)) {
        $errors[] = "Invalid name format (2-100 characters, letters only)";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address";
    }
    
    if ($password !== $confirmpassword) {
        $errors[] = "Passwords do not match";
    }
    
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must be at least 8 characters with uppercase, lowercase, and number";
    }
    
    // File upload handling
    $profilePath = null;
    if (isset($_FILES['profilepic']) && $_FILES['profilepic']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['profilepic']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['profilepic']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('profile_', true) . '.' . $extension;
            $profilePath = $uploadDir . $filename;
            
            if (!move_uploaded_file($_FILES['profilepic']['tmp_name'], $profilePath)) {
                $errors[] = "Failed to upload profile picture";
            }
        } else {
            $errors[] = "Invalid file type. Only JPG, PNG, and GIF allowed";
        }
    }
    
    // Terms agreement check
    if (!isset($_POST['terms'])) {
        $errors[] = "You must agree to the Terms and Conditions";
    }

    if (empty($errors)) {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email already registered";
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user with correct column names
                $stmt = $pdo->prepare("INSERT INTO users 
                    (fullname, email, password, phone, dob, gender, profile_pic, interests) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $name, 
                    $email, 
                    $hashedPassword, 
                    $phone, 
                    $dob, 
                    $gender,
                    $profilePath,
                    $interests
                ]);
                
                // Generate 2FA code
                $otp = generateOTP();
                
                // Store 2FA in session
                 $_SESSION['otp'] = $otp;
                 $_SESSION['temp_user'] = $email;
                header("Location: verify_otp.php");
             exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
            max-width: 500px;
            margin: auto;
            background: var(--lion);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(67, 40, 24, 0.1);
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
        select,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 14px;
            border-radius: 6px;
            border: 1px solid var(--brown);
            background-color: var(--peach);
            font-size: 16px;
        }
        input[type="checkbox"] {
            margin-right: 8px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: var(--falu-red);
            color: var(--peach);
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #5a1614;
        }
        a {
            color: var(--falu-red);
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .form-container p {
            text-align: center;
            margin-top: 15px;
        }
        .form-container p a {
            font-weight: 600;
        }
        .form-container p a:hover {
            color: #5a1614;
        }
        .form-container input[type="radio"] {
            margin-right: 5px;
        }
        .form-container input[type="radio"] + label {
            margin-right: 15px;
        }
        .form-container select {
            background-color: var(--peach);
            border: 1px solid var(--brown);
        }
        .form-container select:focus {
            border-color: var(--falu-red);
            outline: none;
        }
        .form-container input[type="file"] {
            padding: 5px;
            border: 1px solid var(--brown);
            background-color: var(--peach);
        }
        .form-container input[type="file"]:focus {
            border-color: var(--falu-red);
            outline: none;
        }
        .form-container input[type="file"]::file-selector-button {
            background-color: var(--falu-red);
            color: var(--peach);
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
        }
        .form-container input[type="file"]::file-selector-button:hover {
            background-color: #5a1614;
        }
        .form-container input[type="file"]::file-selector-button:focus {
            outline: 2px solid var(--falu-red);
        }
        .form-container input[type="file"]::file-selector-button:focus-visible {
            outline: 2px solid var(--falu-red);
        }
        .form-container input[type="file"]::file-selector-button:active {
            background-color: #4c1312;
        }
        .form-container input[type="file"]::file-selector-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            outline: none;
        }
        .error {
            color: #d32f2f;
            background-color: #ffcdd2;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Sign Up</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form id="signupForm" action="signup.php" method="POST" enctype="multipart/form-data">
            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" required 
                   value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required 
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" 
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirmpassword">Confirm Password:</label>
            <input type="password" id="confirmpassword" name="confirmpassword" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" 
                   value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>">

            <label>Gender:</label>
            <div>
                <input type="radio" id="male" name="gender" value="male" 
                       <?= (isset($_POST['gender']) && $_POST['gender'] === 'male') ? 'checked' : '' ?>>
                <label for="male">Male</label>
                
                <input type="radio" id="female" name="gender" value="female"
                       <?= (isset($_POST['gender']) && $_POST['gender'] === 'female') ? 'checked' : '' ?>>
                <label for="female">Female</label>
            </div>

            <label for="profilepic">Profile Picture:</label>
            <input type="file" id="profilepic" name="profilepic" accept="image/*">

            <label for="interests">Interests:</label>
            <select id="interests" name="interests">
                <option value="tech" <?= (isset($_POST['interests']) && $_POST['interests'] === 'tech') ? 'selected' : '' ?>>Technology</option>
                <option value="sports" <?= (isset($_POST['interests']) && $_POST['interests'] === 'sports') ? 'selected' : '' ?>>Sports</option>
                <option value="art" <?= (isset($_POST['interests']) && $_POST['interests'] === 'art') ? 'selected' : '' ?>>Art</option>
                <option value="music" <?= (isset($_POST['interests']) && $_POST['interests'] === 'music') ? 'selected' : '' ?>>Music</option>
            </select>

            <label>
                <input type="checkbox" name="terms" required
                    <?= isset($_POST['terms']) ? 'checked' : '' ?>>
                I agree to the Terms and Conditions
            </label>

            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="signin.php">Sign In</a></p>
    </div>
    
    <script>
        // Client-side validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmpassword').value;
            
            // Password match validation
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                e.preventDefault();
                return;
            }
            
            // Password strength validation
            const strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            if (!strongRegex.test(password)) {
                alert('Password must be at least 8 characters with uppercase, lowercase, and number');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>