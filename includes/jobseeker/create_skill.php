<?php
session_start();
require_once '../db_connection.php';
// require_once '../auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['skill_name'])) {
    $skill_name = trim($_POST['skill_name']);
    $user_id = $_SESSION['user_id'];
    
    if (empty($skill_name)) {
        $_SESSION['error'] = "Skill name cannot be empty.";
        header("Location: ../../views/jobseeker/skills.php");
        exit();
    }
    
    // Check if skill already exists
    $check_query = "SELECT skill_id FROM skills WHERE LOWER(skill_name) = LOWER(:skill_name)";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":skill_name", $skill_name);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        // Skill exists, get its ID
        $skill = $check_stmt->fetch(PDO::FETCH_ASSOC);
        $skill_id = $skill['skill_id'];
    } else {
        // Create new skill
        $insert_query = "INSERT INTO skills (skill_name) VALUES (:skill_name)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(":skill_name", $skill_name);
        
        if (!$insert_stmt->execute()) {
            $_SESSION['error'] = "Failed to create new skill.";
            header("Location: ../../views/jobseeker/skills.php");
            exit();
        }
        
        $skill_id = $db->lastInsertId();
    }
    
    // Add skill to user profile
    $add_query = "INSERT INTO user_skills (user_id, skill_id) 
                 VALUES (:user_id, :skill_id)
                 ON DUPLICATE KEY UPDATE user_id = user_id";
    $add_stmt = $db->prepare($add_query);
    $add_stmt->bindParam(":user_id", $user_id);
    $add_stmt->bindParam(":skill_id", $skill_id);
    
    if ($add_stmt->execute()) {
        $_SESSION['success'] = "New skill created and added to your profile.";
    } else {
        $_SESSION['error'] = "Failed to add skill to your profile.";
    }
}

header("Location: ../../views/jobseeker/skills.php");
exit();
?>