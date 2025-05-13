<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if company ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Company ID is required";
    header("Location: jobs.php");
    exit();
}

$company_id = $_GET['id'];

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get company details
$company_query = "SELECT c.*, u.first_name, u.last_name, u.email, u.phone, u.username
                 FROM companies c 
                 INNER JOIN users u ON c.user_id = u.user_id 
                 WHERE c.company_id = :company_id";
$company_stmt = $db->prepare($company_query);
$company_stmt->bindParam(":company_id", $company_id);
$company_stmt->execute();

if ($company_stmt->rowCount() === 0) {
    $_SESSION['error'] = "Company not found";
    header("Location: jobs.php");
    exit();
}

$company = $company_stmt->fetch(PDO::FETCH_ASSOC);

// Get jobs posted by this company
$jobs_query = "SELECT j.*, 
              (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.job_id) as application_count
              FROM jobs j 
              WHERE j.company_id = :company_id
              ORDER BY j.created_at DESC";
$jobs_stmt = $db->prepare($jobs_query);
$jobs_stmt->bindParam(":company_id", $company_id);
$jobs_stmt->execute();
$jobs = $jobs_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get job statistics
$stats_query = "SELECT 
               COUNT(*) as total_jobs,
               SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as active_jobs,
               SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_jobs,
               SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_jobs
               FROM jobs
               WHERE company_id = :company_id";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->bindParam(":company_id", $company_id);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get total applications for all company jobs
$applications_query = "SELECT COUNT(*) as total_applications
                      FROM applications a
                      JOIN jobs j ON a.job_id = j.job_id
                      WHERE j.company_id = :company_id";
$applications_stmt = $db->prepare($applications_query);
$applications_stmt->bindParam(":company_id", $company_id);
$applications_stmt->execute();
$applications_count = $applications_stmt->fetch(PDO::FETCH_ASSOC)['total_applications'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Details - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">Admin Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                    <a href="users.php" class="text-gray-700 hover:text-gray-900">Manage Users</a>
                    <a href="jobs.php" class="text-gray-700 hover:text-gray-900">Manage Jobs</a>
                    <a href="../auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
             <!-- Company Statistics -->
             <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Company Statistics</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-blue-600">Total Jobs</p>
                        <p class="text-2xl font-bold text-blue-800"><?php echo $stats['total_jobs']; ?></p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-green-600">Active Jobs</p>
                        <p class="text-2xl font-bold text-green-800"><?php echo $stats['active_jobs']; ?></p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-red-600">Closed Jobs</p>
                        <p class="text-2xl font-bold text-red-800"><?php echo $stats['closed_jobs']; ?></p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-purple-600">Total Applications</p>
                        <p class="text-2xl font-bold text-purple-800"><?php echo $applications_count; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jobs Posted by Company -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Jobs Posted (<?php echo count($jobs); ?>)</h3>
                
                <?php if (count($jobs) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Job Title
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Location
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Posted On
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Applications
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($jobs as $job): ?>
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($job['title']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($job['location']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo ucfirst(htmlspecialchars($job['job_type'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $job['status'] === 'open' ? 'bg-green-100 text-green-800' : 
                                                        ($job['status'] === 'closed' ? 'bg-red-100 text-red-800' : 
                                                        'bg-yellow-100 text-yellow-800'); ?>">
                                                <?php echo ucfirst($job['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $job['application_count']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="job_details.php?id=<?php echo $job['job_id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                            
                                            <?php if ($job['status'] === 'open'): ?>
                                                <a href="../../includes/admin/update_job_status.php?id=<?php echo $job['job_id']; ?>&status=closed" 
                                                   class="text-red-600 hover:text-red-900 mr-3"
                                                   onclick="return confirm('Are you sure you want to close this job?')">Close</a>
                                            <?php elseif ($job['status'] === 'closed'): ?>
                                                <a href="../../includes/admin/update_job_status.php?id=<?php echo $job['job_id']; ?>&status=open" 
                                                   class="text-green-600 hover:text-green-900 mr-3">Reopen</a>
                                            <?php endif; ?>
                                            
                                            <a href="../../includes/admin/delete_job.php?id=<?php echo $job['job_id']; ?>" 
                                               class="text-red-600 hover:text-red-900"
                                               onclick="return confirm('Are you sure you want to delete this job? This action cannot be undone.')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-gray-500">
                        This company has not posted any jobs yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Company Details</h2>
            <a href="jobs.php" class="text-blue-500 hover:text-blue-700">Back to Jobs</a>
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

        <!-- Company Details Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <div class="flex items-center">
                        <?php if ($company['logo']): ?>
                            <img src="../../<?php echo htmlspecialchars($company['logo']); ?>" alt="<?php echo htmlspecialchars($company['company_name']); ?> Logo" class="h-20 w-20 object-contain mr-4">
                        <?php else: ?>
                            <div class="h-20 w-20 bg-gray-200 flex items-center justify-center mr-4 rounded">
                                <span class="text-gray-500 text-2xl"><?php echo substr($company['company_name'], 0, 1); ?></span>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($company['company_name']); ?></h3>
                            <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($company['industry']); ?></p>
                        </div>
                    </div>
                    <div>
                        <a href="../../views/messages/compose.php?to=<?php echo $company['user_id']; ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Contact Employer
                        </a>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-800">Company Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <h5 class="text-sm font-semibold text-gray-600">Location</h5>
                            <p class="mt-1"><?php echo htmlspecialchars($company['location']); ?></p>
                        </div>
                        <div>
                            <h5 class="text-sm font-semibold text-gray-600">Website</h5>
                            <p class="mt-1">
                                <?php if ($company['website']): ?>
                                    <a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                        <?php echo htmlspecialchars($company['website']); ?>
                                    </a>
                                <?php else: ?>
                                    Not specified
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <h5 class="text-sm font-semibold text-gray-600">Registered On</h5>
                            <p class="mt-1"><?php echo date('F j, Y', strtotime($company['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-800">Company Description</h4>
                    <div class="mt-2 text-gray-700 whitespace-pre-line">
                        <?php echo nl2br(htmlspecialchars($company['description'] ?: 'No description provided.')); ?>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-800">Employer Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <h5 class="text-sm font-semibold text-gray-600">Name</h5>
                            <p class="mt-1"><?php echo htmlspecialchars($company['first_name'] . ' ' . $company['last_name']); ?></p>
                        </div>
                        <div>
                            <h5 class="text-sm font-semibold text-gray-600">Username</h5>
                            <p class="mt-1"><?php echo htmlspecialchars($company['username']); ?></p>
                        </div>
                        <div>
                            <h5 class="text-sm font-semibold text-gray-600">Email</h5>
                            <p class="mt-1"><?php echo htmlspecialchars($company['email']); ?></p>
                        </div>
                        <div>
                            <h5 class="text-sm font-semibold text-gray-600">Phone</h5>
                            <p class="mt-1"><?php echo htmlspecialchars($company['phone'] ?: 'Not provided'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

   
    </div>
</body>
</html>
