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
    header("Location: ../../views/admin/users.php");
    exit();
}

$user_id = $_GET['id'];
$status = $_GET['status'];

// Validate status
$valid_statuses = ['active', 'inactive', 'blocked'];
if (!in_array($status, $valid_statuses)) {
    $_SESSION['error'] = "Invalid status.";
    header("Location: ../../views/admin/users.php");
    exit();
}

// Prevent admin from changing their own status
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot change your own status.";
    header("Location: ../../views/admin/users.php");
    exit();
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Update user status
$query = "UPDATE users SET status = :status WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":status", $status);
$stmt->bindParam(":user_id", $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "User status updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update user status.";
}

header("Location: ../../views/admin/users.php");
exit();
?>