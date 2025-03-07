<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $application_id = $_POST['application_id'];
    $status = $_POST['status'];
    
    // Verify employer owns this job posting
    $query = "SELECT c.user_id 
              FROM applications a 
              INNER JOIN jobs j ON a.job_id = j.job_id 
              INNER JOIN companies c ON j.company_id = c.company_id 
              WHERE a.application_id = :application_id";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(":application_id", $application_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && $result['user_id'] == $_SESSION['user_id']) {
        $query = "UPDATE applications 
                  SET status = :status 
                  WHERE application_id = :application_id";
                  
        $stmt = $db->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":application_id", $application_id);
        
        if ($stmt->execute()) {
            // Add notification for jobseeker
            $notify_query = "INSERT INTO notifications (user_id, title, message) 
                           SELECT a.user_id,
                                  'Application Status Updated',
                                  CONCAT('Your application for ', j.title, ' has been ', :status)
                           FROM applications a
                           JOIN jobs j ON a.job_id = j.job_id
                           WHERE a.application_id = :application_id";
            $notify_stmt = $db->prepare($notify_query);
            $notify_stmt->bindParam(":status", $status);
            $notify_stmt->bindParam(":application_id", $application_id);
            $notify_stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    }
    exit();
}
?>
