<?php
session_start();
require_once '../../config/database.php';

$user_id = $_SESSION['user_id'];

// Delete all notifications for this user
$query = "DELETE FROM notifications WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "All notifications cleared successfully.";
} else {
    $_SESSION['error'] = "Failed to clear notifications.";
}

header("Location: ../../views/notifications/index.php");
exit();
?>