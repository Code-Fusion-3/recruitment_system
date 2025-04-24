<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get profile completion percentage
$query = "SELECT 
    (CASE WHEN first_name IS NOT NULL THEN 20 ELSE 0 END +
     CASE WHEN phone IS NOT NULL THEN 20 ELSE 0 END +
     CASE WHEN r.resume_path IS NOT NULL THEN 20 ELSE 0 END +
     CASE WHEN jp.desired_position IS NOT NULL THEN 20 ELSE 0 END +
     CASE WHEN jp.skills IS NOT NULL THEN 20 ELSE 0 END) as completion_percentage
    FROM users u
    LEFT JOIN resumes r ON u.user_id = r.user_id
    LEFT JOIN job_preferences jp ON u.user_id = jp.user_id
    WHERE u.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$profile_completion = $stmt->fetch(PDO::FETCH_ASSOC)['completion_percentage'];

// Get application statistics
$query = "SELECT 
            COUNT(*) as total_applications,
            SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
          FROM applications 
          WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent applications with job and company details
$query = "SELECT a.*, j.title, j.location, c.company_name, a.status, a.applied_at
          FROM applications a
          INNER JOIN jobs j ON a.job_id = j.job_id
          INNER JOIN companies c ON j.company_id = c.company_id
          WHERE a.user_id = :user_id
          ORDER BY a.applied_at DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$recent_applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recommended jobs based on user's preferences
$query = "SELECT j.*, c.company_name 
          FROM jobs j
          INNER JOIN companies c ON j.company_id = c.company_id
          LEFT JOIN job_preferences jp ON jp.user_id = :user_id
          WHERE j.status = 'open'
          AND (jp.desired_position IS NULL 
               OR j.title LIKE CONCAT('%', jp.desired_position, '%')
               OR j.description LIKE CONCAT('%', jp.desired_position, '%'))
          ORDER BY j.created_at DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$recommended_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
include_once '../templates/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobseeker Dashboard - Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Total Applications</h3>
        <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total_applications'] ?? 0; ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Shortlisted</h3>
        <p class="text-3xl font-bold text-green-600"><?php echo $stats['shortlisted'] ?? 0; ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Profile Completion</h3>
        <p class="text-3xl font-bold text-purple-600"><?php echo $profile_completion; ?>%</p>
    </div>
</div>


        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Recent Applications</h2>
                <?php if ($recent_applications): ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_applications as $application): ?>
                            <div class="border-b pb-4 hover:bg-gray-50 transition duration-150 ease-in-out rounded p-3">
                                <a href="application_detail.php?id=<?php echo $application['application_id']; ?>" class="block">
                                    <h3 class="font-semibold text-blue-600 hover:text-blue-800"><?php echo htmlspecialchars($application['title']); ?></h3>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($application['company_name']); ?></p>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-sm text-gray-500">Applied: <?php echo date('M d, Y', strtotime($application['applied_at'])); ?></span>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            <?php echo $application['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                    ($application['status'] === 'shortlisted' ? 'bg-green-100 text-green-800' : 
                                                    'bg-red-100 text-red-800'); ?>">
                                            <?php echo ucfirst($application['status']); ?>
                                        </span>
                                    </div>
                                </a>
                                <div class="mt-3 text-right">
                                    <a href="application_detail.php?id=<?php echo $application['application_id']; ?>" 
                                       class="text-sm text-blue-500 hover:text-blue-700 font-medium inline-flex items-center">
                                        View details
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No applications yet</p>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Recommended Jobs</h2>
                <?php if ($recommended_jobs): ?>
                    <div class="space-y-4">
                        <?php foreach ($recommended_jobs as $job): ?>
                            <div class="border-b pb-4">
                                <h3 class="font-semibold"><?php echo htmlspecialchars($job['title']); ?></h3>
                                <p class="text-gray-600"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($job['location']); ?></p>
                                <div class="mt-2">
                                    <a href="view-job.php?id=<?php echo $job['job_id']; ?>" 
                                       class="text-blue-500 hover:text-blue-700 text-sm">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No recommendations available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
  
<!-- Add this section to the employer dashboard -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Recent Messages</h2>
            <a href="../messages/inbox.php" class="text-blue-500 hover:text-blue-700 text-sm">View All</a>
        </div>
        
        <?php
        // Get recent messages
        $messages_query = "SELECT m.*, u.first_name, u.last_name 
                          FROM messages m
                          JOIN users u ON m.sender_id = u.user_id
                          WHERE m.receiver_id = :user_id
                          ORDER BY m.created_at DESC
                          LIMIT 3";
        $messages_stmt = $db->prepare($messages_query);
        $messages_stmt->bindParam(":user_id", $user_id);
        $messages_stmt->execute();
        $recent_messages = $messages_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <?php if (count($recent_messages) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($recent_messages as $message): ?>
                    <div class="border-b pb-3 <?php echo $message['is_read'] ? '' : 'font-semibold'; ?>">
                        <div class="flex justify-between">
                            <span><?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?></span>
                            <span class="text-sm text-gray-500"><?php echo date('M j, Y', strtotime($message['created_at'])); ?></span>
                        </div>
                        <a href="../messages/view_message.php?id=<?php echo $message['message_id']; ?>" class="text-blue-500 hover:text-blue-700">
                            <?php echo htmlspecialchars($message['subject'] ? $message['subject'] : '(No subject)'); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No recent messages.</p>
        <?php endif; ?>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Recent Notifications</h2>
            <a href="../notifications/index.php" class="text-blue-500 hover:text-blue-700 text-sm">View All</a>
        </div>
        
        <?php
        // Get recent notifications
        $notifications_query = "SELECT * FROM notifications 
                              WHERE user_id = :user_id
                              ORDER BY created_at DESC
                              LIMIT 3";
        $notifications_stmt = $db->prepare($notifications_query);
        $notifications_stmt->bindParam(":user_id", $user_id);
        $notifications_stmt->execute();
        $recent_notifications = $notifications_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <?php if (count($recent_notifications) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($recent_notifications as $notification): ?>
                    <div class="border-b pb-3 <?php echo $notification['is_read'] ? '' : 'font-semibold'; ?>">
                        <div class="flex justify-between">
                            <span class="font-medium"><?php echo htmlspecialchars($notification['title']); ?></span>
                            <span class="text-sm text-gray-500"><?php echo date('M j, Y', strtotime($notification['created_at'])); ?></span>
                        </div>
                        <p class="text-gray-600 truncate"><?php echo htmlspecialchars($notification['message']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No recent notifications.</p>
        <?php endif; ?>
    </div>
</div>


</body>
</html>