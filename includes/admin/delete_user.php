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
    $_SESSION['error'] = "Missing user ID.";
    header("Location: ../../views/admin/users.php");
    exit();
}

$user_id = $_GET['id'];

// Prevent admin from deleting themselves
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account.";
    header("Location: ../../views/admin/users.php");
    exit();
}

// Create database connection
$database = new Database();
$db = $database->getConnection();
try {
    // Start transaction
    $db->beginTransaction();
    
    // Delete user
    $query = "DELETE FROM users WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    // Commit transaction
    $db->commit();
    
    $_SESSION['success'] = "User deleted successfully.";
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollBack();
    $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
}

header("Location: ../../views/admin/users.php");
exit();
?>
