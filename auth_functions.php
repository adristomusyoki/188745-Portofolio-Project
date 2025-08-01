<?php
// Generate OTP
function generateOTP($length = 6) {
    return bin2hex(random_bytes($length/2));
}

// Send OTP via Email (pseudo-implementation)
function sendOTP($email, $otp) {
    // In production: Use PHPMailer or mail()
    $subject = "Your Verification Code";
    $message = "Your OTP code is: $otp";
    // mail($email, $subject, $message);
    error_log("OTP for $email: $otp"); 
}

// Password strength validation
function validatePassword($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
}
?>