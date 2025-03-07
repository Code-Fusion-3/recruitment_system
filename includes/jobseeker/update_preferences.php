<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_id = $_SESSION['user_id'];
    $desired_position = $_POST['desired_position'];
    $preferred_location = $_POST['preferred_location'];
    $skills = $_POST['skills'];
    
    $query = "INSERT INTO job_preferences (user_id, desired_position, preferred_location, skills, updated_at) 
              VALUES (:user_id, :desired_position, :preferred_location, :skills, NOW())
              ON DUPLICATE KEY UPDATE 
              desired_position = :desired_position,
              preferred_location = :preferred_location,
              skills = :skills,
              updated_at = NOW()";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":desired_position", $desired_position);
    $stmt->bindParam(":preferred_location", $preferred_location);
    $stmt->bindParam(":skills", $skills);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Career preferences updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update career preferences";
    }
    
    header("Location: ../../views/jobseeker/profile.php");
    exit();
}
?>
