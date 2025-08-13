<?php
session_start();
require_once 'auth_session.php'; 
require_once 'dbc0nnection.php'; 
//require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $authorId = $_SESSION['user_id'];
    $title = $_POST['article_title'];
    $content = $_POST['article_full_text'];
    $display = $_POST['article_display'];
    $order = $_POST['article_order'];

    $stmt = $conn->prepare("INSERT INTO articles (authorId, article_title, article_full_text, article_created_date, article_last_update, article_display, article_order) VALUES (?, ?, ?, NOW(), NOW(), ?, ?)");
    $stmt->bind_param("isssi", $authorId, $title, $content, $display, $order);

    if ($stmt->execute()) {
        // Send email to all Administrators
        sendArticleNotification($conn, $title, $content);
        echo "Article added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

function sendArticleNotification($conn, $title, $content) {
    $result = $conn->query("SELECT email FROM users WHERE UserType = 'Administrator'");
    
    if ($result->num_rows > 0) {
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your_email@gmail.com'; 
            $mail->Password   = 'your_app_password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('your_email@gmail.com', 'Article System');

            while ($row = $result->fetch_assoc()) {
                $mail->addAddress($row['email']);
            }

            $mail->isHTML(true);
            $mail->Subject = 'New Article Posted: ' . $title;
            $mail->Body    = "<h2>$title</h2><p>$content</p><p><em>Login to view more.</em></p>";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}
?>

