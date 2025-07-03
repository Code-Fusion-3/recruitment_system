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

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$company_filter = isset($_GET['company']) ? $_GET['company'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get all companies for filter dropdown
$companies_query = "SELECT company_id, company_name FROM companies ORDER BY company_name";
$companies_stmt = $db->prepare($companies_query);
$companies_stmt->execute();
$companies = $companies_stmt->fetchAll(PDO::FETCH_ASSOC);

// Build query with filters
$query = "SELECT j.*, c.company_name 
          FROM jobs j 
          INNER JOIN companies c ON j.company_id = c.company_id 
          WHERE 1=1";

if (!empty($status_filter)) {
    $query .= " AND j.status = :status";
}

if (!empty($company_filter)) {
    $query .= " AND j.company_id = :company_id";
}

if (!empty($search)) {
    $query .= " AND (j.title LIKE :search OR j.description LIKE :search OR j.location LIKE :search)";
}

$query .= " ORDER BY j.created_at DESC";

$stmt = $db->prepare($query);

if (!empty($status_filter)) {
    $stmt->bindValue(':status', $status_filter);
}

if (!empty($company_filter)) {
    $stmt->bindValue(':company_id', $company_filter);
}

if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bindValue(':search', $search_param);
}

$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <a href="../../includes/auth/logout.php"
                        class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Manage Jobs</h2>
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

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form action="" method="GET" class="flex flex-wrap items-end space-x-4">
                <div class="mb-4">
                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                    <select id="status" name="status"
                        class="shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Statuses</option>
                        <option value="open" <?php echo $status_filter === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed
                        </option>
                        <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="company" class="block text-gray-700 text-sm font-bold mb-2">Company</label>
                    <select id="company" name="company"
                        class="shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Companies</option>
                        <?php foreach ($companies as $company): ?>
                        <option value="<?php echo $company['company_id']; ?>"
                            <?php echo $company_filter == $company['company_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($company['company_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4 flex-grow">
                    <label for="search" class="block text-gray-700 text-sm font-bold mb-2">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search by title, description, location..."
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Filter
                    </button>
                    <a href="jobs.php" class="ml-2 text-blue-500 hover:text-blue-700">Reset</a>
                </div>
            </form>
        </div>

        <!-- Jobs Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Job Title
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Company
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Posted
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (count($jobs) > 0): ?>
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($job['title']); ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                <?php echo ucfirst(htmlspecialchars($job['job_type'])); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($job['company_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($job['location']); ?>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="job_details.php?id=<?php echo $job['job_id']; ?>"
                                class="text-blue-600 hover:text-blue-900 mr-3">View</a>

                            <?php if ($job['status'] === 'open'): ?>
                            <a href="../../includes/admin/update_job_status.php?id=<?php echo $job['job_id']; ?>&status=closed"
                                class="text-red-600 hover:text-red-900 mr-3"
                                onclick="return confirm('Are you sure you want to close this job?')">Close</a>
                            <?php elseif ($job['status'] === 'closed'): ?>
                            <a href="../../includes/admin/update_job_status.php?id=<?php echo $job['job_id']; ?>&status=open"
                                class="text-green-600 hover:text-green-900 mr-3">Reopen</a>
                            <?php endif; ?>


                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No jobs found matching your criteria.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>