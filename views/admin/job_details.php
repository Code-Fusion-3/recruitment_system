<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if job ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Job ID is required";
    header("Location: jobs.php");
    exit();
}

$job_id = $_GET['id'];

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get job details
$job_query = "SELECT j.*, c.company_name, c.company_id, c.location as company_location, c.website, c.industry
              FROM jobs j 
              INNER JOIN companies c ON j.company_id = c.company_id 
              WHERE j.job_id = :job_id";
$job_stmt = $db->prepare($job_query);
$job_stmt->bindParam(":job_id", $job_id);
$job_stmt->execute();

if ($job_stmt->rowCount() === 0) {
    $_SESSION['error'] = "Job not found";
    header("Location: jobs.php");
    exit();
}

$job = $job_stmt->fetch(PDO::FETCH_ASSOC);

// Get applications for this job
$applications_query = "SELECT a.*, 
                      u.first_name, u.last_name, u.email, u.phone,
                      r.resume_path
                      FROM applications a
                      INNER JOIN users u ON a.user_id = u.user_id
                      LEFT JOIN resumes r ON u.user_id = r.user_id
                      WHERE a.job_id = :job_id
                      ORDER BY 
                        CASE 
                            WHEN a.status = 'hired' THEN 1
                            WHEN a.status = 'shortlisted' THEN 2
                            WHEN a.status = 'pending' THEN 3
                            WHEN a.status = 'rejected' THEN 4
                            ELSE 5
                        END";
$applications_stmt = $db->prepare($applications_query);
$applications_stmt->bindParam(":job_id", $job_id);
$applications_stmt->execute();
$applications = $applications_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get application statistics
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
                SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hired,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM applications
                WHERE job_id = :job_id";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->bindParam(":job_id", $job_id);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get interviews scheduled for this job
$interviews_query = "SELECT i.*, a.user_id, u.first_name, u.last_name
                    FROM interviews i
                    INNER JOIN applications a ON i.application_id = a.application_id
                    INNER JOIN users u ON a.user_id = u.user_id
                    WHERE a.job_id = :job_id
                    ORDER BY i.interview_date";
$interviews_stmt = $db->prepare($interviews_query);
$interviews_stmt->bindParam(":job_id", $job_id);
$interviews_stmt->execute();
$interviews = $interviews_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">   
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details - Admin Dashboard</title>
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
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Job Details</h2>
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

        <!-- Applications Overview -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Applications Overview</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-600">Total</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-yellow-600">Pending</p>
                        <p class="text-2xl font-bold text-yellow-800"><?php echo $stats['pending']; ?></p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-green-600">Shortlisted</p>
                        <p class="text-2xl font-bold text-green-800"><?php echo $stats['shortlisted']; ?></p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-blue-600">Hired</p>
                        <p class="text-2xl font-bold text-blue-800"><?php echo $stats['hired']; ?></p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-red-600">Rejected</p>
                        <p class="text-2xl font-bold text-red-800"><?php echo $stats['rejected']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Applications (<?php echo count($applications); ?>)</h3>
                    <div class="flex gap-2">
                    <button onclick="filterApplications('all')" class="text-sm bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">All</button>
                        <button onclick="filterApplications('pending')" class="text-sm bg-yellow-100 px-3 py-1 rounded hover:bg-yellow-200">Pending</button>
                        <button onclick="filterApplications('shortlisted')" class="text-sm bg-green-100 px-3 py-1 rounded hover:bg-green-200">Shortlisted</button>
                        <button onclick="filterApplications('hired')" class="text-sm bg-blue-100 px-3 py-1 rounded hover:bg-blue-200">Hired</button>
                        <button onclick="filterApplications('rejected')" class="text-sm bg-red-100 px-3 py-1 rounded hover:bg-red-200">Rejected</button>
                    </div>
                </div>

                <?php if (count($applications) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Applicant
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contact
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Applied On
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <!-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th> -->
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($applications as $application): ?>
                                    <tr class="application-row" data-status="<?php echo $application['status']; ?>">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-500">
                                                <div><?php echo htmlspecialchars($application['email']); ?></div>
                                                <?php if ($application['phone']): ?>
                                                    <div><?php echo htmlspecialchars($application['phone']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($application['applied_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $application['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                        ($application['status'] === 'shortlisted' ? 'bg-green-100 text-green-800' : 
                                                        ($application['status'] === 'hired' ? 'bg-blue-100 text-blue-800' : 
                                                        'bg-red-100 text-red-800')); ?>">
                                                <?php echo ucfirst($application['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <?php if ($application['resume_path']): ?>
                                                    <a href="../../<?php echo $application['resume_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900">
                                                        View Resume
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <div class="hidden inline-block text-left" x-data="{ open: false }">
                                                    <button @click="open = !open" type="button" class="text-gray-700 hover:text-gray-900">
                                                        Change Status
                                                    </button>
                                                    <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                                                        <div class="py-1" role="none">
                                                            <a href="../../includes/admin/update_application_status.php?id=<?php echo $application['application_id']; ?>&status=pending&job_id=<?php echo $job_id; ?>" class="text-yellow-600 block px-4 py-2 text-sm hover:bg-gray-100">Pending</a>
                                                            <a href="../../includes/admin/update_application_status.php?id=<?php echo $application['application_id']; ?>&status=shortlisted&job_id=<?php echo $job_id; ?>" class="text-green-600 block px-4 py-2 text-sm hover:bg-gray-100">Shortlist</a>
                                                            <a href="../../includes/admin/update_application_status.php?id=<?php echo $application['application_id']; ?>&status=hired&job_id=<?php echo $job_id; ?>" class="text-blue-600 block px-4 py-2 text-sm hover:bg-gray-100">Hire</a>
                                                            <a href="../../includes/admin/update_application_status.php?id=<?php echo $application['application_id']; ?>&status=rejected&job_id=<?php echo $job_id; ?>" class="text-red-600 block px-4 py-2 text-sm hover:bg-gray-100">Reject</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <a href="../../includes/admin/delete_application.php?id=<?php echo $application['application_id']; ?>&job_id=<?php echo $job_id; ?>" 
                                                   class="text-red-600 hover:text-red-900 hidden"
                                                   onclick="return confirm('Are you sure you want to delete this application?')">
                                                    Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-gray-500">
                        No applications have been submitted for this job yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Job Details Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p class="text-gray-600 mt-1">
                            <span class="font-semibold">Company:</span> 
                            <a href="company_details.php?id=<?php echo $job['company_id']; ?>" class="text-blue-600 hover:text-blue-800">
                                <?php echo htmlspecialchars($job['company_name']); ?>
                            </a>
                        </p>
                    </div>
                    <div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                            <?php echo $job['status'] === 'open' ? 'bg-green-100 text-green-800' : 
                                    ($job['status'] === 'closed' ? 'bg-red-100 text-red-800' : 
                                    'bg-yellow-100 text-yellow-800'); ?>">
                            <?php echo ucfirst($job['status']); ?>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-600">Location</h4>
                        <p class="mt-1"><?php echo htmlspecialchars($job['location']); ?></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-600">Job Type</h4>
                        <p class="mt-1"><?php echo ucfirst(htmlspecialchars($job['job_type'])); ?></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-600">Salary Range</h4>
                        <p class="mt-1"><?php echo htmlspecialchars($job['salary_range'] ?: 'Not specified'); ?></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-600">Posted On</h4>
                        <p class="mt-1"><?php echo date('F j, Y', strtotime($job['created_at'])); ?></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-600">Deadline</h4>
                        <p class="mt-1"><?php echo $job['deadline'] ? date('F j, Y', strtotime($job['deadline'])) : 'No deadline'; ?></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-600">Industry</h4>
                        <p class="mt-1"><?php echo htmlspecialchars($job['industry']); ?></p>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-800">Description</h4>
                    <div class="mt-2 text-gray-700 whitespace-pre-line">
                        <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-800">Requirements</h4>
                    <div class="mt-2 text-gray-700 whitespace-pre-line">
                        <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                    </div>
                </div>

                <div class="mt-6 flex space-x-4">
                    <?php if ($job['status'] === 'open'): ?>
                        <a href="../../includes/admin/update_job_status.php?id=<?php echo $job['job_id']; ?>&status=closed" 
                           class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                           onclick="return confirm('Are you sure you want to close this job?')">
                            Close Job
                        </a>
                    <?php elseif ($job['status'] === 'closed'): ?>
                        <a href="../../includes/admin/update_job_status.php?id=<?php echo $job['job_id']; ?>&status=open" 
                           class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            Reopen Job
                        </a>
                    <?php endif; ?>
                    <a href="../../includes/admin/delete_job.php?id=<?php echo $job['job_id']; ?>" 
                       class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                       onclick="return confirm('Are you sure you want to delete this job? This action cannot be undone.')">
                        Delete Job
                    </a>
                </div>
            </div>
        </div>


        <!-- Scheduled Interviews -->
        <?php if (count($interviews) > 0): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Scheduled Interviews</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Candidate
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date & Time
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Notes
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($interviews as $interview): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($interview['first_name'] . ' ' . $interview['last_name']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M d, Y - h:i A', strtotime($interview['interview_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo ucfirst($interview['interview_type']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $interview['status'] === 'scheduled' ? 'bg-yellow-100 text-yellow-800' : 
                                                    ($interview['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                                    'bg-red-100 text-red-800'); ?>">
                                            <?php echo ucfirst($interview['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo $interview['notes'] ? htmlspecialchars($interview['notes']) : 'No notes'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <script>
        function filterApplications(status) {
            const rows = document.querySelectorAll('.application-row');
            
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
