<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect based on role
            switch($user['role']) {
                case 'admin':
                    header("Location: ../../views/admin/dashboard.php");
                    break;
                case 'employer':
                    header("Location: ../../views/employer/dashboard.php");
                    break;
                case 'jobseeker':
                    header("Location: ../../views/jobseeker/dashboard.php");
                    break;
            }
            exit();
        }
    }
    
    $_SESSION['error'] = "Invalid email or password";
    header("Location: ../../views/auth/login.php");
    exit();
}
