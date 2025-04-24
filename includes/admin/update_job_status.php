<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Check if ID and status parameters are provided
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    $_SESSION['error'] = "Missing parameters.";
    header("Location: ../../views/admin/jobs.php");
    exit();
}

$job_id = $_GET['id'];
$status = $_GET['status'];

// Validate status
$valid_statuses = ['open', 'closed', 'draft'];
if (!in_array($status, $valid_statuses)) {
    $_SESSION['error'] = "Invalid status.";
    header("Location: ../../views/admin/jobs.php");
    exit();
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Update job status
$query = "UPDATE jobs SET status = :status WHERE job_id = :job_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":status", $status);
$stmt->bindParam(":job_id", $job_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Job status updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update job status.";
}

header("Location: ../../views/admin/jobs.php");
exit();
?>