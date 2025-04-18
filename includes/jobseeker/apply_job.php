<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $job_id = $_POST['job_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if already applied
    $query = "SELECT application_id FROM applications 
              WHERE job_id = :job_id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":job_id", $job_id);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'You have already applied for this position']);
        exit();
    }
    
    // Insert new application
    $query = "INSERT INTO applications (job_id, user_id, status, applied_at) 
              VALUES (:job_id, :user_id, 'pending', NOW())";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":job_id", $job_id);
    $stmt->bindParam(":user_id", $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit application']);
    }
    exit();
}
?>
