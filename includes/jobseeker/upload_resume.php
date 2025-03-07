<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_id = $_SESSION['user_id'];
    
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $upload_dir = '../../uploads/resumes/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
        $file_name = $user_id . '_' . uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_path)) {
            $resume_path = 'uploads/resumes/' . $file_name;
            
            // Update or insert resume record
            $query = "INSERT INTO resumes (user_id, resume_path, updated_at) 
                     VALUES (:user_id, :resume_path, NOW())
                     ON DUPLICATE KEY UPDATE 
                     resume_path = :resume_path,
                     updated_at = NOW()";
                     
            $stmt = $db->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":resume_path", $resume_path);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Resume uploaded successfully";
            } else {
                $_SESSION['error'] = "Failed to update resume record";
            }
        } else {
            $_SESSION['error'] = "Failed to upload resume file";
        }
    } else {
        $_SESSION['error'] = "No resume file uploaded";
    }
    
    header("Location: ../../views/jobseeker/profile.php");
    exit();
}
?>
