<?php
session_start();
require_once '../../config/database.php';

// Check if user is an employer
if ($_SESSION['role'] !== 'employer') {
    header("Location: ../../views/auth/login.php");
    exit();
}

if (isset($_GET['interview_id'])) {
    $interview_id = $_GET['interview_id'];
    
    // Verify employer has access to this interview
    $check_query = "SELECT i.* FROM interviews i
                   JOIN applications a ON i.application_id = a.application_id
                   JOIN jobs j ON a.job_id = j.job_id
                   JOIN companies c ON j.company_id = c.company_id
                   WHERE i.interview_id = :interview_id
                   AND c.user_id = :user_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":interview_id", $interview_id);
    $check_stmt->bindParam(":user_id", $_SESSION['user_id']);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "You don't have permission to delete this interview.";
    } else {
        // Get application details for notification
        $get_details_query = "SELECT a.user_id, j.title 
                             FROM interviews i
                             JOIN applications a ON i.application_id = a.application_id
                             JOIN jobs j ON a.job_id = j.job_id
                             WHERE i.interview_id = :interview_id";
        $get_details_stmt = $db->prepare($get_details_query);
        $get_details_stmt->bindParam(":interview_id", $interview_id);
        $get_details_stmt->execute();
        $details = $get_details_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the interview
        $delete_query = "DELETE FROM interviews WHERE interview_id = :interview_id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(":interview_id", $interview_id);
        
        if ($delete_stmt->execute()) {
            // Create notification for jobseeker
            $title = "Interview Cancelled";
            $message = "Your interview for the position of {$details['title']} has been cancelled.";
            
            $notification_query = "INSERT INTO notifications (user_id, title, message) 
                                  VALUES (:user_id, :title, :message)";
            $notification_stmt = $db->prepare($notification_query);
            $notification_stmt->bindParam(":user_id", $details['user_id']);
            $notification_stmt->bindParam(":title", $title);
            $notification_stmt->bindParam(":message", $message);
            $notification_stmt->execute();
            
            $_SESSION['success'] = "Interview deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete interview.";
        }
    }
}

header("Location: ../../views/employer/view_applications.php");
exit();
?>