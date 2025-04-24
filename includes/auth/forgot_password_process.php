<?php
// error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Validate email
    if (empty($email)) {
        $_SESSION['error'] = "Email is required.";
        header("Location: ../../views/auth/forgot_password.php");
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../../views/auth/forgot_password.php");
        exit();
    }
    
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if email exists in database
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // For security reasons, don't reveal that the email doesn't exist
        $_SESSION['error'] = "email not found.";
        header("Location: ../../views/auth/forgot_password.php");
        exit();
    }
    
    // Store email in session for the reset page
    $_SESSION['reset_email'] = $email;
    
    // Redirect to reset password page
    header("Location: ../../views/auth/reset_password.php");
    exit();
}

// If not POST request, redirect to forgot password page
header("Location: ../../views/auth/forgot_password.php");
exit();
?>
