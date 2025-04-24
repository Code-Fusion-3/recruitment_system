<?php
session_start();
require_once '../db_connection.php';
require_once '../auth_check.php';

if (isset($_GET['id'])) {
    $message_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    // Verify user has access to this message
    $check_query = "SELECT * FROM messages 
                   WHERE message_id = :message_id 
                   AND (sender_id = :user_id OR receiver_id = :user_id)";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":message_id", $message_id);
    $check_stmt->bindParam(":user_id", $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "Message not found or you don't have permission to delete it.";
    } else {
        $message = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the message
        $delete_query = "DELETE FROM messages WHERE message_id = :message_id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(":message_id", $message_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Message deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete message.";
        }
    }
}

// Redirect back to appropriate page
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../../views/messages/inbox.php';
header("Location: $referer");
exit();
?>