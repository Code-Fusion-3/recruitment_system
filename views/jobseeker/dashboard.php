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
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">My Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="browse-jobs.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Browse Jobs</a>
                    <a href="profile.php" class="text-gray-600 hover:text-gray-800">My Profile</a>
                    <a href="../../includes/auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

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
                            <div class="border-b pb-4">
                                <h3 class="font-semibold"><?php echo htmlspecialchars($application['title']); ?></h3>
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
</body>
</html>