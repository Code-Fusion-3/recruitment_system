<?php
session_start();
require_once '../../config/database.php';
  // Create database connection
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get all sent messages
$query = "SELECT m.*, u.username, u.first_name, u.last_name, u.role 
          FROM messages m
          JOIN users u ON m.receiver_id = u.user_id
          WHERE m.sender_id = :user_id
          ORDER BY m.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$sent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread messages in inbox for the badge
$unread_query = "SELECT COUNT(*) as unread_count FROM messages 
                WHERE receiver_id = :user_id AND is_read = 0";
$unread_stmt = $db->prepare($unread_query);
$unread_stmt->bindParam(":user_id", $user_id);
$unread_stmt->execute();
$unread_result = $unread_stmt->fetch(PDO::FETCH_ASSOC);
$unread_count = $unread_result['unread_count'];

include_once '../templates/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Messages</h1>
        <a href="../<?php echo $_SESSION['role']; ?>/dashboard.php" class="text-blue-500 hover:text-blue-700">Back to Dashboard</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="flex mb-6">
        <a href="inbox.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded mr-2">Inbox <?php if ($unread_count > 0): ?><span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $unread_count; ?></span><?php endif; ?></a>
        <a href="sent.php" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Sent</a>
        <a href="compose.php" class="bg-green-500 text-white px-4 py-2 rounded">Compose New Message</a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Sent Messages</h2>
        
        <?php if (count($sent_messages) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                To
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Subject
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sent_messages as $message): ?>
                            <tr>
                                <td class="py-4 px-4 border-b border-gray-200">
                                    <?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?>
                                    <span class="text-xs text-gray-500">(<?php echo ucfirst($message['role']); ?>)</span>
                                </td>
                                <td class="py-4 px-4 border-b border-gray-200">
                                    <a href="view_message.php?id=<?php echo $message['message_id']; ?>" class="text-blue-500 hover:text-blue-700">
                                        <?php echo htmlspecialchars($message['subject'] ? $message['subject'] : '(No subject)'); ?>
                                    </a>
                                </td>
                                <td class="py-4 px-4 border-b border-gray-200">
                                    <?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?>
                                </td>
                                <td class="py-4 px-4 border-b border-gray-200">
                                    <a href="view_message.php?id=<?php echo $message['message_id']; ?>" class="text-blue-500 hover:text-blue-700 mr-2">View</a>
                                    <a href="../../includes/messages/delete_message.php?id=<?php echo $message['message_id']; ?>" 
                                       class="text-red-500 hover:text-red-700"
                                       onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">You haven't sent any messages yet.</p>
        <?php endif; ?>
    </div>
</div>

  