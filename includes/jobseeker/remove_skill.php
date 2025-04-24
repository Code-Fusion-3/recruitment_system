<?php
session_start();
require_once '../db_connection.php';
require_once '../auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['skill_id'])) {
    $user_id = $_SESSION['user_id'];
    $skill_id = $_POST['skill_id'];
    
    $query = "DELETE FROM user_skills WHERE user_id = :user_id AND skill_id = :skill_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":skill_id", $skill_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Skill removed successfully.";
    } else {
        $_SESSION['error'] = "Failed to remove skill.";
    }
}

header("Location: ../../views/jobseeker/skills.php");
exit();
?>