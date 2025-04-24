<?php
session_start();
require_once '../db_connection.php';
// require_once '../auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['skill_id'])) {
    $user_id = $_SESSION['user_id'];
    $skill_id = $_POST['skill_id'];
    
    // Check if skill already exists for this user
    $check_query = "SELECT * FROM user_skills WHERE user_id = :user_id AND skill_id = :skill_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":user_id", $user_id);
    $check_stmt->bindParam(":skill_id", $skill_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = "You already have this skill in your profile.";
    } else {
        // Add the skill to user profile
        $query = "INSERT INTO user_skills (user_id, skill_id) VALUES (:user_id, :skill_id)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":skill_id", $skill_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Skill added successfully.";
        } else {
            $_SESSION['error'] = "Failed to add skill.";
        }
    }
}

header("Location: ../../views/jobseeker/skills.php");
exit();
?>