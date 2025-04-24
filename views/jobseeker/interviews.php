<?php
session_start();
require_once '../../config/database.php';
  

// Check if user is a jobseeker
if ($_SESSION['role'] !== 'jobseeker') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all interviews for this jobseeker
$query = "SELECT i.*, j.title as job_title, c.company_name, a.status as application_status
          FROM interviews i
          JOIN applications a ON i.application_id = a.application_id
          JOIN jobs j ON a.job_id = j.job_id
          JOIN companies c ON j.company_id = c.company_id
          WHERE a.user_id = :user_id
          ORDER BY i.interview_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once '../templates/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">My Interviews</h1>
        <a href="dashboard.php" class="text-blue-500 hover:text-blue-700">Back to Dashboard</a>
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
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Upcoming Interviews</h2>
        
        <?php if (count($interviews) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Job Title
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Company
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date & Time
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Notes
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($interviews as $interview): ?>
                            <tr>
                                <td class="py-4 px-4 border-b border-gray-200">
                                    <?php echo htmlspecialchars($interview['job_title']); ?>
                                </td>
                                <td class="py-4 px-4 border-b border-gray-200">
                                    <?php echo htmlspecialchars($interview['company_name']); ?>
                                </td>
                                <td class="py-4 px-4 border-b border-gray-200">
                                    <?php echo date('M j, Y g:i A', strtotime($interview['interview_date'])); ?>
                                </td>
                                <td class="py-4 px-4 border-b border-gray-200">
                                    <?php 
                                        $type_badges = [
                                            'online' => '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Online</span>',
                                            'in-person' => '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">In-person</span>',
                                            'phone' => '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Phone</span>'
                                        ];
                                        echo $type_badges[$interview['interview_type']];
                                    ?>
                                </td>
                                <td class="py-4 px-4 border-b border-gray-200">
                                    <?php 
                                        $status_badges = [
                                            'scheduled' => '<span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">Scheduled</span>',
                                            'completed' => '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Completed</span>',
                                            'cancelled' => '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Cancelled</span>'
                                        ];
                                        echo $status_badges[$interview['status']];
                                    ?>
                                </td>
                                <td class="py-4 px-4 border-b border-gray-200">
                                    <?php echo !empty($interview['notes']) ? htmlspecialchars($interview['notes']) : '<span class="text-gray-400">No notes</span>'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">You don't have any scheduled interviews yet.</p>
        <?php endif; ?>
    </div>
</div>

  
