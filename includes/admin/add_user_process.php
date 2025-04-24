<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $status = $_POST['status'];
    
    // Validate data
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password) || empty($role)) {
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: ../../views/admin/add_user.php");
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../../views/admin/add_user.php");
        exit();
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../../views/admin/add_user.php");
        exit();
    }
    
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if username already exists
    $query = "SELECT username FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Username already exists.";
        header("Location: ../../views/admin/add_user.php");
        exit();
    }
    
    // Check if email already exists
    $query = "SELECT email FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email already exists.";
        header("Location: ../../views/admin/add_user.php");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $query = "INSERT INTO users (username, email, password, role, first_name, last_name, phone, status) 
              VALUES (:username, :email, :password, :role, :first_name, :last_name, :phone, :status)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $hashed_password);
    $stmt->bindParam(":role", $role);
    $stmt->bindParam(":first_name", $first_name);
    $stmt->bindParam(":last_name", $last_name);
    $stmt->bindParam(":phone", $phone);
    $stmt->bindParam(":status", $status);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "User added successfully.";
        header("Location: ../../views/admin/users.php");
    } else {
        $_SESSION['error'] = "Failed to add user.";
        header("Location: ../../views/admin/add_user.php");
    }
    exit();
}

// If not POST request, redirect back
header("Location: ../../views/admin/add_user.php");
exit();
?>