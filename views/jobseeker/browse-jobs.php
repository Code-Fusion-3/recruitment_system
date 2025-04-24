<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is a jobseeker
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle search and filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$job_type = isset($_GET['job_type']) ? $_GET['job_type'] : '';

// Build query with filters
$query = "SELECT j.*, c.company_name 
          FROM jobs j 
          INNER JOIN companies c ON j.company_id = c.company_id 
          WHERE j.status = 'open'";

if ($search) {
    $query .= " AND (j.title LIKE :search OR j.description LIKE :search)";
}
if ($location) {
    $query .= " AND j.location LIKE :location";
}
if ($job_type) {
    $query .= " AND j.job_type = :job_type";
}

$query .= " ORDER BY j.created_at DESC";

$stmt = $db->prepare($query);

if ($search) {
    $searchParam = "%$search%";
    $stmt->bindParam(":search", $searchParam);
}
if ($location) {
    $locationParam = "%$location%";
    $stmt->bindParam(":location", $locationParam);
}
if ($job_type) {
    $stmt->bindParam(":job_type", $job_type);
}

$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
include_once '../templates/header.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs - Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">


    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Search and Filter Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" 
                           name="search" 
                           placeholder="Search jobs..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full px-4 py-2 border rounded-md">
                </div>
                <div>
                    <input type="text" 
                           name="location" 
                           placeholder="Location" 
                           value="<?php echo htmlspecialchars($location); ?>"
                           class="w-full px-4 py-2 border rounded-md">
                </div>
                <div>
                    <select name="job_type" class="w-full px-4 py-2 border rounded-md">
                        <option value="">All Job Types</option>
                        <option value="full-time" <?php echo $job_type === 'full-time' ? 'selected' : ''; ?>>Full Time</option>
                        <option value="part-time" <?php echo $job_type === 'part-time' ? 'selected' : ''; ?>>Part Time</option>
                        <option value="contract" <?php echo $job_type === 'contract' ? 'selected' : ''; ?>>Contract</option>
                        <option value="internship" <?php echo $job_type === 'internship' ? 'selected' : ''; ?>>Internship</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Job Listings -->
        <div class="space-y-6">
            <?php foreach ($jobs as $job): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($job['title']); ?></h2>
                            <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($job['company_name']); ?></p>
                            <div class="mt-2 space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo ucfirst(htmlspecialchars($job['job_type'])); ?>
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?php echo htmlspecialchars($job['location']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-600"><?php echo htmlspecialchars($job['salary_range']); ?></p>
                            <p class="text-sm text-gray-500 mt-1">Posted <?php echo date('M d, Y', strtotime($job['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-gray-600"><?php echo substr(htmlspecialchars($job['description']), 0, 200) . '...'; ?></p>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <a href="view-job.php?id=<?php echo $job['job_id']; ?>" 
                           class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($jobs)): ?>
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <p class="text-gray-500">No jobs found matching your criteria</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
