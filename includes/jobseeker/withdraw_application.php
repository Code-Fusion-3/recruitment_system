<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is a jobseeker
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
    header("Location: ../../views/auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No application specified.";
    header("Location: ../../views/jobseeker/applications.php");
    exit();
}

$application_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Verify this application belongs to the user and is in pending status
$check_query = "SELECT * FROM applications 
               WHERE application_id = :application_id 
               AND user_id = :user_id
               AND status = 'pending'";
$check_stmt = $db->prepare($check_query);
$check_stmt->bindParam(":application_id", $application_id);
$check_stmt->bindParam(":user_id", $user_id);
$check_stmt->execute();

if ($check_stmt->rowCount() === 0) {
    $_SESSION['error'] = "You can only withdraw pending applications.";
    header("Location: ../../views/jobseeker/applications.php");
    exit();
}

// Delete the application
$delete_query = "DELETE FROM applications WHERE application_id = :application_id";
$delete_stmt = $db->prepare($delete_query);
$delete_stmt->bindParam(":application_id", $application_id);

if ($delete_stmt->execute()) {
    $_SESSION['success'] = "Application withdrawn successfully.";
} else {
    $_SESSION['error'] = "Failed to withdraw application.";
}

header("Location: ../../views/jobseeker/applications.php");
exit();
?>