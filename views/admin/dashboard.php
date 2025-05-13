<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get total users count
$users_query = "SELECT COUNT(*) as total, 
               SUM(CASE WHEN role = 'jobseeker' THEN 1 ELSE 0 END) as jobseekers,
               SUM(CASE WHEN role = 'employer' THEN 1 ELSE 0 END) as employers
               FROM users";
$users_stmt = $db->prepare($users_query);
$users_stmt->execute();
$users_data = $users_stmt->fetch(PDO::FETCH_ASSOC);

// Get active jobs count
$jobs_query = "SELECT COUNT(*) as total, 
              SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as active,
              SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
              FROM jobs";
$jobs_stmt = $db->prepare($jobs_query);
$jobs_stmt->execute();
$jobs_data = $jobs_stmt->fetch(PDO::FETCH_ASSOC);

// Get applications count
$applications_query = "SELECT COUNT(*) as total,
                      SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                      SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
                      SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hired,
                      SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                      FROM applications";
$applications_stmt = $db->prepare($applications_query);
$applications_stmt->execute();
$applications_data = $applications_stmt->fetch(PDO::FETCH_ASSOC);

// Get recent users
$recent_users_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users_stmt = $db->prepare($recent_users_query);
$recent_users_stmt->execute();
$recent_users = $recent_users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent jobs
$recent_jobs_query = "SELECT j.*, c.company_name 
                     FROM jobs j 
                     JOIN companies c ON j.company_id = c.company_id 
                     ORDER BY j.created_at DESC LIMIT 5";
$recent_jobs_stmt = $db->prepare($recent_jobs_query);
$recent_jobs_stmt->execute();
$recent_jobs = $recent_jobs_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">Admin Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="users.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Manage Users</a>
                    <a href="jobs.php" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Manage Jobs</a>
                    <a href="../auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-4">Total Users</h3>
                <p class="text-3xl font-bold text-blue-600"><?php echo $users_data['total']; ?></p>
                <div class="mt-2 flex justify-between text-sm">
                    <span class="text-gray-600">Jobseekers: <?php echo $users_data['jobseekers']; ?></span>
                    <span class="text-gray-600">Employers: <?php echo $users_data['employers']; ?></span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-4">Active Jobs</h3>
                <p class="text-3xl font-bold text-green-600"><?php echo $jobs_data['active']; ?></p>
                <div class="mt-2 flex justify-between text-sm">
                    <span class="text-gray-600">Total: <?php echo $jobs_data['total']; ?></span>
                    <span class="text-gray-600">Closed: <?php echo $jobs_data['closed']; ?></span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-4">Total Applications</h3>
                <p class="text-3xl font-bold text-purple-600"><?php echo $applications_data['total']; ?></p>
                <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                    <span class="text-gray-600">Pending: <?php echo $applications_data['pending']; ?></span>
                    <span class="text-gray-600">Shortlisted: <?php echo $applications_data['shortlisted']; ?></span>
                    <span class="text-gray-600">Hired: <?php echo $applications_data['hired']; ?></span>
                    <span class="text-gray-600">Rejected: <?php echo $applications_data['rejected']; ?></span>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-4">User Distribution</h3>
                <canvas id="userChart" width="400" height="300"></canvas>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-4">Application Status</h3>
                <canvas id="applicationChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Recent Users -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Recent Users</h3>
                    <a href="users.php" class="text-blue-500 hover:text-blue-700">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                                    ($user['role'] === 'employer' ? 'bg-blue-100 text-blue-800' : 
                                                    'bg-green-100 text-green-800'); ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Jobs -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Recent Jobs</h3>
                    <a href="jobs.php" class="text-blue-500 hover:text-blue-700">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recent_jobs as $job): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($job['location']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($job['company_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $job['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($job['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // User Distribution Chart
        const userCtx = document.getElementById('userChart').getContext('2d');
        const userChart = new Chart(userCtx, {
            type: 'pie',
            data: {
                labels: ['Jobseekers', 'Employers', 'Admins'],
                datasets: [{
                    data: [
                        <?php echo $users_data['jobseekers']; ?>, 
                        <?php echo $users_data['employers']; ?>, 
                        <?php echo $users_data['total'] - $users_data['jobseekers'] - $users_data['employers']; ?>
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Application Status Chart
        const appCtx = document.getElementById('applicationChart').getContext('2d');
        const appChart = new Chart(appCtx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Shortlisted', 'Hired', 'Rejected'],
                datasets: [{
                    label: 'Applications by Status',
                    data: [
                        <?php echo $applications_data['pending']; ?>,
                        <?php echo $applications_data['shortlisted']; ?>,
                        <?php echo $applications_data['hired']; ?>,
                        <?php echo $applications_data['rejected']; ?>
                    ],
                    backgroundColor: [
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>