<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if email is set in session
    if (!isset($_SESSION['reset_email'])) {
        $_SESSION['error'] = "Invalid password reset request.";
        header("Location: ../../views/auth/forgot_password.php");
        exit();
    }
    
    $email = $_SESSION['reset_email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords
    if (empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Both password fields are required.";
        header("Location: ../../views/auth/reset_password.php");
        exit();
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../../views/auth/reset_password.php");
        exit();
    }
    
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long.";
        header("Location: ../../views/auth/reset_password.php");
        exit();
    }
    
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Hash the new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update the user's password
    $query = "UPDATE users SET password = :password WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":password", $hashed_password);
    $stmt->bindParam(":email", $email);
    
    if ($stmt->execute()) {
        // Clear the reset email from session
        unset($_SESSION['reset_email']);
        
        $_SESSION['success'] = "Your password has been reset successfully. You can now log in with your new password.";
        header("Location: ../../views/auth/login.php");
    } else {
        $_SESSION['error'] = "Failed to reset password. Please try again.";
        header("Location: ../../views/auth/reset_password.php");
    }
    exit();
}

// If not POST request, redirect to forgot password page
header("Location: ../../views/auth/forgot_password.php");
exit();
?>
