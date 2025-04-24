<?php
session_start();
require_once '../../config/database.php';
  
// Create database connection
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: inbox.php");
    exit();
}

$message_id = $_GET['id'];

// Get message details
$query = "SELECT m.*, 
          sender.user_id as sender_user_id, sender.first_name as sender_first_name, sender.last_name as sender_last_name, sender.role as sender_role,
          receiver.user_id as receiver_user_id, receiver.first_name as receiver_first_name, receiver.last_name as receiver_last_name, receiver.role as receiver_role
          FROM messages m
          JOIN users sender ON m.sender_id = sender.user_id
          JOIN users receiver ON m.receiver_id = receiver.user_id
          WHERE m.message_id = :message_id
          AND (m.sender_id = :user_id OR m.receiver_id = :user_id)";
$stmt = $db->prepare($query);
$stmt->bindParam(":message_id", $message_id);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = "Message not found or you don't have permission to view it.";
    header("Location: inbox.php");
    exit();
}

$message = $stmt->fetch(PDO::FETCH_ASSOC);

// Mark as read if user is the receiver
if ($message['receiver_id'] == $user_id && $message['is_read'] == 0) {
    $update_query = "UPDATE messages SET is_read = 1 WHERE message_id = :message_id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(":message_id", $message_id);
    $update_stmt->execute();
}

include_once '../templates/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">View Message</h1>
        <a href="inbox.php" class="text-blue-500 hover:text-blue-700">Back to Inbox</a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="border-b pb-4 mb-4">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm text-gray-600">
                        <strong>From:</strong> 
                        <?php echo htmlspecialchars($message['sender_first_name'] . ' ' . $message['sender_last_name']); ?>
                        <span class="text-xs">(<?php echo ucfirst($message['sender_role']); ?>)</span>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong>To:</strong> 
                        <?php echo htmlspecialchars($message['receiver_first_name'] . ' ' . $message['receiver_last_name']); ?>
                        <span class="text-xs">(<?php echo ucfirst($message['receiver_role']); ?>)</span>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?>
                    </p>
                </div>
                <div>
                    <a href="compose.php?reply_to=<?php echo $message_id; ?>" class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-1 px-3 rounded">
                        Reply
                    </a>
                </div>
            </div>
            <h2 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($message['subject'] ? $message['subject'] : '(No subject)'); ?></h2>
        </div>
        
        <div class="whitespace-pre-wrap">
            <?php echo nl2br(htmlspecialchars($message['message_text'])); ?>
        </div>
        
        <div class="mt-6 pt-4 border-t flex justify-between">
            <a href="inbox.php" class="text-blue-500 hover:text-blue-700">Back to Inbox</a>
            <div>
                <a href="compose.php?reply_to=<?php echo $message_id; ?>" class="text-green-500 hover:text-green-700 mr-4">Reply</a>
                <a href="../../includes/messages/delete_message.php?id=<?php echo $message_id; ?>" 
                   class="text-red-500 hover:text-red-700"
                   onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
            </div>
        </div>
    </div>
</div>

  