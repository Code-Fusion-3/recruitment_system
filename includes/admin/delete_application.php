<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Check if application ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Application ID is required";
    header("Location: ../../views/admin/jobs.php");
    exit();
}

$application_id = $_GET['id'];
$job_id = isset($_GET['job_id']) ? $_GET['job_id'] : null;

// Create database connection
$database = new Database();
$db = $database->getConnection();

try {
    // First, check if the application exists
    $check_query = "SELECT * FROM applications WHERE application_id = :application_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":application_id", $application_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "Application not found";
    } else {
        // Delete the application
        $delete_query = "DELETE FROM applications WHERE application_id = :application_id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(":application_id", $application_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Application deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete application";
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

// Redirect back to job details page if job_id is provided, otherwise to jobs list
if ($job_id) {
    header("Location: ../../views/admin/job_details.php?id=" . $job_id);
} else {
    header("Location: ../../views/admin/jobs.php");
}
exit();
?>
