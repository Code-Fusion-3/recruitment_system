<?php
session_start();
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();
// Check if user is a jobseeker
if ($_SESSION['role'] !== 'jobseeker') {
    header("Location: ../../views/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all applications for this user with job and company details
$query = "SELECT a.*, j.title as job_title, j.location, j.job_type, 
          c.company_name, c.logo, a.applied_at, a.status
          FROM applications a
          JOIN jobs j ON a.job_id = j.job_id
          JOIN companies c ON j.company_id = c.company_id
          WHERE a.user_id = :user_id
          ORDER BY a.applied_at DESC";
          
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get status counts for summary
$status_query = "SELECT status, COUNT(*) as count 
                FROM applications 
                WHERE user_id = :user_id 
                GROUP BY status";
$status_stmt = $db->prepare($status_query);
$status_stmt->bindParam(":user_id", $user_id);
$status_stmt->execute();
$status_counts = $status_stmt->fetchAll(PDO::FETCH_ASSOC);

// Format status counts for easy access
$counts = [
    'pending' => 0,
    'reviewed' => 0,
    'shortlisted' => 0,
    'rejected' => 0,
    'hired' => 0
];

foreach ($status_counts as $status) {
    $counts[$status['status']] = $status['count'];
}

// Get total applications
$total_applications = array_sum($counts);
include_once '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">My Applications</h1>
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

            <!-- Application Summary -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Total</h3>
                    <p class="text-3xl font-bold text-gray-700"><?php echo $total_applications; ?></p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Pending</h3>
                    <p class="text-3xl font-bold text-yellow-500"><?php echo $counts['pending']; ?></p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Shortlisted</h3>
                    <p class="text-3xl font-bold text-blue-500"><?php echo $counts['shortlisted']; ?></p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Rejected</h3>
                    <p class="text-3xl font-bold text-red-500"><?php echo $counts['rejected']; ?></p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Hired</h3>
                    <p class="text-3xl font-bold text-green-500"><?php echo $counts['hired']; ?></p>
                </div>
            </div>

            <!-- Applications List -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <?php if (count($applications) > 0): ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Job
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Company
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Applied On
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($applications as $application): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($application['job_title']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($application['location']); ?> • 
                                            <?php echo ucfirst(htmlspecialchars($application['job_type'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php if ($application['logo']): ?>
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full object-cover" 
                                                         src="../../<?php echo htmlspecialchars($application['logo']); ?>" 
                                                         alt="<?php echo htmlspecialchars($application['company_name']); ?>">
                                                </div>
                                            <?php endif; ?>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($application['company_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('M j, Y', strtotime($application['applied_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            switch($application['status']) {
                                                case 'pending':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'reviewed':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                case 'shortlisted':
                                                    echo 'bg-indigo-100 text-indigo-800';
                                                    break;
                                                case 'rejected':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                case 'hired':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php echo ucfirst($application['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="application_detail.php?id=<?php echo $application['application_id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                        <?php if ($application['status'] === 'pending'): ?>
                                            <a href="../../includes/jobseeker/withdraw_application.php?id=<?php echo $application['application_id']; ?>" 
                                               class="text-red-600 hover:text-red-900"
                                               onclick="return confirm('Are you sure you want to withdraw this application?');">
                                                Withdraw
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-6 text-center">
                        <p class="text-gray-500 mb-4">You haven't applied to any jobs yet.</p>
                        <a href="../jobs/index.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Browse Jobs
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>