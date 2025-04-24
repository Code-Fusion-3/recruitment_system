<?php
session_start();
require_once '../../config/database.php';
// Check if user is an employer
if ($_SESSION['role'] !== 'employer') {
    header("Location: ../../views/auth/login.php");
    exit();
}
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get company ID for this employer
$company_query = "SELECT company_id FROM companies WHERE user_id = :user_id";
$company_stmt = $db->prepare($company_query);
$company_stmt->bindParam(":user_id", $user_id);
$company_stmt->execute();

if ($company_stmt->rowCount() === 0) {
    // Redirect to create company profile if no company exists
    $_SESSION['error'] = "Please create a company profile first.";
    header("Location: company_profile.php");
    exit();
}

$company = $company_stmt->fetch(PDO::FETCH_ASSOC);
$company_id = $company['company_id'];

// Get all jobs for this company
$jobs_query = "SELECT j.*, 
              (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.job_id) as application_count 
              FROM jobs j 
              WHERE j.company_id = :company_id 
              ORDER BY j.created_at DESC";
$jobs_stmt = $db->prepare($jobs_query);
$jobs_stmt->bindParam(":company_id", $company_id);
$jobs_stmt->execute();
$jobs = $jobs_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle job status toggle if requested
if (isset($_GET['toggle_status']) && isset($_GET['job_id'])) {
    $job_id = $_GET['job_id'];
    $new_status = $_GET['toggle_status'] === 'open' ? 'closed' : 'open';
    
    // Verify this job belongs to the employer
    $check_query = "SELECT * FROM jobs j 
                   JOIN companies c ON j.company_id = c.company_id 
                   WHERE j.job_id = :job_id AND c.user_id = :user_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":job_id", $job_id);
    $check_stmt->bindParam(":user_id", $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        $update_query = "UPDATE jobs SET status = :status WHERE job_id = :job_id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(":status", $new_status);
        $update_stmt->bindParam(":job_id", $job_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Job status updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update job status.";
        }
    } else {
        $_SESSION['error'] = "You don't have permission to update this job.";
    }
    
    header("Location: jobs.php");
    exit();
}
include_once '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Manage Jobs</h1>
                <div>
              
                    <a href="post-job.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Post New Job
                    </a>
                </div>
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

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <?php if (count($jobs) > 0): ?>
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
                                    Posted Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($job['location']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            <?php echo ucfirst(htmlspecialchars($job['job_type'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $job['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($job['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="job_applications.php?job_id=<?php echo $job['job_id']; ?>" class="text-blue-500 hover:text-blue-700">
                                            <?php echo $job['application_count']; ?> applications
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="edit_job.php?id=<?php echo $job['job_id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <a href="jobs.php?toggle_status=<?php echo $job['status']; ?>&job_id=<?php echo $job['job_id']; ?>" 
                                           class="<?php echo $job['status'] === 'open' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'; ?> mr-3">
                                            <?php echo $job['status'] === 'open' ? 'Close' : 'Reopen'; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-6 text-center">
                        <p class="text-gray-500 mb-4">You haven't posted any jobs yet.</p>
                        <a href="post-job.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Post Your First Job
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>