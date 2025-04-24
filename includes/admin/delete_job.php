<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Check if ID parameter is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Missing job ID.";
    header("Location: ../../views/admin/jobs.php");
    exit();
}

$job_id = $_GET['id'];

// Create database connection
$database = new Database();
$db = $database->getConnection();

try {
    // Start transaction
    $db->beginTransaction();
    
    // Delete job
    $query = "DELETE FROM jobs WHERE job_id = :job_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":job_id", $job_id);
    $stmt->execute();
    
    // Commit transaction
    $db->commit();
    
    $_SESSION['success'] = "Job deleted successfully.";
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollBack();
    $_SESSION['error'] = "Error deleting job: " . $e->getMessage();
}

header("Location: ../../views/admin/jobs.php");
exit();
?>