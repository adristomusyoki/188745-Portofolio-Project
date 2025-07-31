<?php


// Security Best Practices Implementation
session_start([
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

// Include database connection
require_once 'dbconnection.php';

// Initialize variables
$errors = [];
$success = false;
$name = $email = $phone = $color = $date = $message = $subscribe = '';
$filePath = '';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "CSRF validation failed. Please try again.";
    }
    
    // Implement rate limiting
    $rateLimitTime = 300; // 5 minutes
    $maxRequests = 3;
    
    if (!isset($_SESSION['last_submission'])) {
        $_SESSION['last_submission'] = time();
        $_SESSION['submission_count'] = 1;
    } else {
        $elapsed = time() - $_SESSION['last_submission'];
        
        if ($elapsed < $rateLimitTime) {
            if ($_SESSION['submission_count'] >= $maxRequests) {
                $errors[] = "Too many requests. Please try again in " . ceil(($rateLimitTime - $elapsed)/60) . " minutes.";
            } else {
                $_SESSION['submission_count']++;
            }
        } else {
            $_SESSION['last_submission'] = time();
            $_SESSION['submission_count'] = 1;
        }
    }
    
    // Server-side validation
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    if (!empty($phone)) {
        $phonePattern = '/^[0-9\s\+\-\(\)]{7,20}$/';
        if (!preg_match($phonePattern, $phone)) {
            $errors[] = "Invalid phone number format.";
        }
    }
    
    $color = filter_input(INPUT_POST, 'color', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    if (empty($message)) {
        $errors[] = "Message is required.";
    }
    
    $subscribe = isset($_POST['subscribe']) ? 1 : 0;
    
    // File upload handling
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            'text/plain' => 'txt'
        ];
        
        $fileType = $_FILES['file']['type'];
        $fileSize = $_FILES['file']['size'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (isset($allowedTypes[$fileType]) && $fileSize <= $maxSize) {
            $extension = $allowedTypes[$fileType];
            $fileName = uniqid('file_', true) . '.' . $extension;
            $uploadDir = 'uploads/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $destination = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
                $filePath = $destination;
            } else {
                $errors[] = "Failed to upload file.";
            }
        } else {
            $errors[] = "Invalid file type or size exceeds 2MB limit.";
        }
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        try {
            // Prepare SQL statement
            $stmt = $conn->prepare("INSERT INTO contact_submissions 
                                  (name, email, phone, color, contact_date, message, subscribe, file_path, ip_address) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // Execute with parameters
            $stmt->bind_param("ssssssiss", 
                $name,
                $email,
                $phone,
                $color,
                $date,
                $message,
                $subscribe,
                $filePath,
                $_SERVER['REMOTE_ADDR']
            );
            
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $success = true;
                
                // Send email notification
                $to = "admin@example.com";
                $subject = "New Contact Form Submission";
                $body = "Name: $name\nEmail: $email\nPhone: $phone\nMessage:\n$message";
                // mail($to, $subject, $body);
            } else {
                $errors[] = "Failed to save submission to database.";
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Me</title>
    <link rel="stylesheet" href="styles/contact1.css">
</head>
<body>
    <div class="form-container">
        <h2>Contact Me</h2>
        
        <?php if ($success): ?>
            <div class="success">Thank you! Your message has been submitted successfully.</div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <p>If you would like to get in touch, please fill out the form below:</p>
        
        <form method="POST" action="contact1.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required 
                       value="<?php echo htmlspecialchars($name); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($email); ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($phone); ?>">
            </div>
            
            <div class="form-group">
                <label for="color">Favorite Color:</label>
                <input type="color" id="color" name="color" 
                       value="<?php echo htmlspecialchars($color ? $color : '#6f1d1b'); ?>">
            </div>
            
            <div class="form-group">
                <label for="date">Preferred Contact Date:</label>
                <input type="date" id="date" name="date" 
                       value="<?php echo htmlspecialchars($date); ?>">
            </div>
            
            <div class="form-group">
                <label for="file">Attach a file (optional):</label>
                <input type="file" id="file" name="file">
            </div>
            
            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="4" required><?php 
                    echo htmlspecialchars($message); 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="subscribe" value="yes"
                        <?php echo $subscribe ? 'checked' : ''; ?>>
                    Subscribe to newsletter
                </label>
            </div>
            
            <button type="submit">Send</button>
        </form>
        
        <p>Feel free to reach out with any questions or collaboration ideas!</p>
        <p>Thank you for visiting my portfolio!</p>
    </div>
</body>
</html>