<?php
// Get unread notifications count
if (isset($_SESSION['user_id'])) {
    $notification_query = "SELECT COUNT(*) as count FROM notifications 
                          WHERE user_id = :user_id AND is_read = 0";
    $notification_stmt = $db->prepare($notification_query);
    $notification_stmt->bindParam(":user_id", $_SESSION['user_id']);
    $notification_stmt->execute();
    $notification_result = $notification_stmt->fetch(PDO::FETCH_ASSOC);
    $unread_notifications = $notification_result['count'];
    
    // Get unread messages count
    $message_query = "SELECT COUNT(*) as count FROM messages 
                     WHERE receiver_id = :user_id AND is_read = 0";
    $message_stmt = $db->prepare($message_query);
    $message_stmt->bindParam(":user_id", $_SESSION['user_id']);
    $message_stmt->execute();
    $message_result = $message_stmt->fetch(PDO::FETCH_ASSOC);
    $unread_messages = $message_result['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="<?php echo isset($_SESSION['user_id']) ? '../' . $_SESSION['role'] . '/dashboard.php' : '../index.php'; ?>" class="text-xl font-bold">
                Recruitment System
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="flex items-center space-x-4">
                    <!-- Dashboard Button -->
                    <a href="../<?php echo $_SESSION['role']; ?>/dashboard.php" class="flex items-center hover:bg-blue-700 px-3 py-1 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                    
                    <?php if (isset($unread_notifications) && $unread_notifications > 0): ?>
                        <a href="../notifications/index.php" class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                <?php echo $unread_notifications; ?>
                            </span>
                        </a>
                    <?php else: ?>
                        <a href="../notifications/index.php">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (isset($unread_messages) && $unread_messages > 0): ?>
                        <a href="../messages/inbox.php" class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                <?php echo $unread_messages; ?>
                            </span>
                        </a>
                    <?php else: ?>
                        <a href="../messages/inbox.php">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-1">
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                            <a href="../<?php echo $_SESSION['role']; ?>/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <?php if ($_SESSION['role'] === 'jobseeker'): ?>
                                <a href="../jobseeker/applications.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Applications</a>
                                <a href="../jobseeker/interviews.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Interviews</a>
                            <?php elseif ($_SESSION['role'] === 'employer'): ?>
                                <a href="../employer/jobs.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Jobs</a>
                                <a href="../employer/applications.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Applications</a>
                            <?php elseif ($_SESSION['role'] === 'admin'): ?>
                                <a href="../admin/users.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Manage Users</a>
                                <a href="../admin/jobs.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Manage Jobs</a>
                            <?php endif; ?>
                            <a href="../auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="space-x-4">
                    <a href="../auth/login.php" class="hover:underline">Login</a>
                    <a href="../auth/register.php" class="bg-white text-blue-600 px-4 py-2 rounded hover:bg-blue-100">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
