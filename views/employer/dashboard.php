<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php");
    exit();
}

// Get employer data
$database = new Database();
$db = $database->getConnection();

// Get counts for dashboard stats
$user_id = $_SESSION['user_id'];

// Count active jobs
$query = "SELECT COUNT(*) as active_jobs FROM jobs j 
          INNER JOIN companies c ON j.company_id = c.company_id 
          WHERE c.user_id = :user_id AND j.status = 'open'";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$active_jobs = $stmt->fetch(PDO::FETCH_ASSOC)['active_jobs'];

// Count total applications
$query = "SELECT COUNT(*) as total_applications FROM applications a 
          INNER JOIN jobs j ON a.job_id = j.job_id 
          INNER JOIN companies c ON j.company_id = c.company_id 
          WHERE c.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$total_applications = $stmt->fetch(PDO::FETCH_ASSOC)['total_applications'];

// Count shortlisted candidates
$query = "SELECT COUNT(*) as shortlisted FROM applications a 
          INNER JOIN jobs j ON a.job_id = j.job_id 
          INNER JOIN companies c ON j.company_id = c.company_id 
          WHERE c.user_id = :user_id AND a.status = 'shortlisted'";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$shortlisted = $stmt->fetch(PDO::FETCH_ASSOC)['shortlisted'];

// Get recent job postings
$query = "SELECT j.*, COUNT(a.application_id) as application_count 
          FROM jobs j 
          INNER JOIN companies c ON j.company_id = c.company_id 
          LEFT JOIN applications a ON j.job_id = a.job_id 
          WHERE c.user_id = :user_id 
          GROUP BY j.job_id 
          ORDER BY j.created_at DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$recent_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard - Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">Employer Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="company-profile.php" class="text-gray-600 hover:text-gray-800">Company Profile</a>
                    <a href="post-job.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Post New Job</a>
                    <a href="../../includes/auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-4">Active Jobs</h3>
                <p class="text-3xl font-bold text-blue-600"><?php echo $active_jobs; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-4">Total Applications</h3>
                <p class="text-3xl font-bold text-green-600"><?php echo $total_applications; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-4">Shortlisted</h3>
                <p class="text-3xl font-bold text-purple-600"><?php echo $shortlisted; ?></p>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-bold mb-4">Recent Job Postings</h2>
            <div class="bg-white rounded-lg shadow-md">
                <?php if ($recent_jobs): ?>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($recent_jobs as $job): ?>
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($job['title']); ?></h3>
                                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($job['location']); ?></p>
                                        <p class="text-sm text-gray-500 mt-2">Posted on: <?php echo date('M d, Y', strtotime($job['created_at'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                            <?php echo $job['application_count']; ?> applications
                                        </span>
                                        <div class="mt-2">
                                            <a href="view-applications.php?job_id=<?php echo $job['job_id']; ?>" 
                                               class="text-blue-500 hover:text-blue-700 text-sm">
                                                View Applications
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-center text-gray-500">
                        No jobs posted yet
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
