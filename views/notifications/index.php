<?php
session_start();
require_once '../../config/database.php';
  

$user_id = $_SESSION['user_id'];

// Get all notifications for this user
$query = "SELECT * FROM notifications 
          WHERE user_id = :user_id 
          ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark all notifications as read
$update_query = "UPDATE notifications 
                SET is_read = 1 
                WHERE user_id = :user_id AND is_read = 0";
$update_stmt = $db->prepare($update_query);
$update_stmt->bindParam(":user_id", $user_id);
$update_stmt->execute();

include_once '../templates/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Notifications</h1>
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

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">All Notifications</h2>
        
        <?php if (count($notifications) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($notifications as $notification): ?>
                    <div class="p-4 border rounded <?php echo $notification['is_read'] ? 'bg-white' : 'bg-blue-50'; ?>">
                        <div class="flex justify-between">
                            <h3 class="font-semibold"><?php echo htmlspecialchars($notification['title']); ?></h3>
                            <span class="text-sm text-gray-500"><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></span>
                        </div>
                        <p class="mt-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-6">
                <a href="../../includes/notifications/clear_all.php" 
                   class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                   onclick="return confirm('Are you sure you want to delete all notifications?');">
                    Clear All Notifications
                </a>
            </div>
        <?php else: ?>
            <p class="text-gray-500">You don't have any notifications.</p>
        <?php endif; ?>
    </div>
</div>

  