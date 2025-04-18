<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_id = $_SESSION['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    $query = "UPDATE users 
              SET first_name = :first_name,
                  last_name = :last_name,
                  email = :email,
                  phone = :phone,
                  updated_at = NOW()
              WHERE user_id = :user_id";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(":first_name", $first_name);
    $stmt->bindParam(":last_name", $last_name);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":phone", $phone);
    $stmt->bindParam(":user_id", $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update profile";
    }
    
    header("Location: ../../views/jobseeker/profile.php");
    exit();
}
?>
