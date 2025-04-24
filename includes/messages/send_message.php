<?php
// error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user_id'];
    $recipient_id = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : null;
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $reply_to = isset($_POST['reply_to']) ? intval($_POST['reply_to']) : null;
    
    // Validate inputs
    if (!$recipient_id) {
        $_SESSION['error'] = "Please select a recipient.";
        header("Location: ../../views/messages/compose.php");
        exit();
    }
    
    if (empty($message)) {
        $_SESSION['error'] = "Message cannot be empty.";
        header("Location: ../../views/messages/compose.php" . ($reply_to ? "?reply_to=$reply_to" : ""));
        exit();
    }
    
    // Verify recipient exists
    $check_query = "SELECT user_id FROM users WHERE user_id = :recipient_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":recipient_id", $recipient_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "Invalid recipient.";
        header("Location: ../../views/messages/compose.php");
        exit();
    }
    
    // Insert message
    $query = "INSERT INTO messages (sender_id, receiver_id, subject, message_text) 
              VALUES (:sender_id, :receiver_id, :subject, :message)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":sender_id", $sender_id);
    $stmt->bindParam(":receiver_id", $recipient_id);
    $stmt->bindParam(":subject", $subject);
    $stmt->bindParam(":message", $message);
    
    if ($stmt->execute()) {
        // If this is a reply, mark the original message as read
        if ($reply_to) {
            $update_query = "UPDATE messages SET is_read = 1 WHERE message_id = :message_id AND receiver_id = :user_id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(":message_id", $reply_to);
            $update_stmt->bindParam(":user_id", $sender_id);
            $update_stmt->execute();
        }
        
        // Create notification for recipient
        $get_sender_query = "SELECT first_name, last_name FROM users WHERE user_id = :user_id";
        $get_sender_stmt = $db->prepare($get_sender_query);
        $get_sender_stmt->bindParam(":user_id", $sender_id);
        $get_sender_stmt->execute();
        $sender_data = $get_sender_stmt->fetch(PDO::FETCH_ASSOC);
        
        $title = "New Message";
        $notification_message = "You have received a new message from {$sender_data['first_name']} {$sender_data['last_name']}.";
        
        $notification_query = "INSERT INTO notifications (user_id, title, message) 
                              VALUES (:user_id, :title, :message)";
        $notification_stmt = $db->prepare($notification_query);
        $notification_stmt->bindParam(":user_id", $recipient_id);
        $notification_stmt->bindParam(":title", $title);
        $notification_stmt->bindParam(":message", $notification_message);
        $notification_stmt->execute();
        
        $_SESSION['success'] = "Message sent successfully.";
        header("Location: ../../views/messages/sent.php");
    } else {
        $_SESSION['error'] = "Failed to send message.";
        header("Location: ../../views/messages/compose.php" . ($reply_to ? "?reply_to=$reply_to" : ""));
    }
    exit();
}

// If not POST request, redirect
header("Location: ../../views/messages/inbox.php");
exit();
?>
