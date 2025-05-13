<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Check if required parameters are provided
if (!isset($_GET['id']) || !isset($_GET['status']) || !isset($_GET['job_id'])) {
    $_SESSION['error'] = "Missing required parameters";
    header("Location: ../../views/admin/jobs.php");
    exit();
}

$application_id = $_GET['id'];
$status = $_GET['status'];
$job_id = $_GET['job_id'];

// Validate status
$valid_statuses = ['pending', 'shortlisted', 'hired', 'rejected'];
if (!in_array($status, $valid_statuses)) {
    $_SESSION['error'] = "Invalid status";
    header("Location: ../../views/admin/job_details.php?id=" . $job_id);
    exit();
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

try {
    // Update application status
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
        
        $_SESSION['success'] = "Application status updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update application status";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

header("Location: ../../views/admin/job_details.php?id=" . $job_id);
exit();
?>