<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$job_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get job details
$query = "SELECT j.*, c.company_name, c.description as company_description, c.logo 
          FROM jobs j 
          INNER JOIN companies c ON j.company_id = c.company_id 
          WHERE j.job_id = :job_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":job_id", $job_id);
$stmt->execute();
$job = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user has already applied
$query = "SELECT application_id, status FROM applications 
          WHERE job_id = :job_id AND user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":job_id", $job_id);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$existing_application = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> - Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="browse-jobs.php" class="text-blue-500 hover:text-blue-700">← Back to Jobs</a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($job['title']); ?></h1>
                    <p class="text-lg text-gray-600 mt-1"><?php echo htmlspecialchars($job['company_name']); ?></p>
                </div>
                <?php if (!$existing_application): ?>
                    <button onclick="applyForJob()" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
                        Apply Now
                    </button>
                <?php else: ?>
                    <div class="text-right">
                        <span class="px-4 py-2 rounded-md bg-gray-100 text-gray-800">
                            Applied - <?php echo ucfirst($existing_application['status']); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-gray-600"><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                    <p class="text-gray-600"><strong>Job Type:</strong> <?php echo ucfirst(htmlspecialchars($job['job_type'])); ?></p>
                    <p class="text-gray-600"><strong>Salary Range:</strong> <?php echo htmlspecialchars($job['salary_range']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600"><strong>Posted:</strong> <?php echo date('M d, Y', strtotime($job['created_at'])); ?></p>
                    <p class="text-gray-600"><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($job['status'])); ?></p>
                </div>
            </div>

            <div class="border-t pt-6">
                <h2 class="text-xl font-bold mb-4">Job Description</h2>
                <div class="prose max-w-none">
                    <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                </div>
            </div>

            <div class="border-t pt-6 mt-6">
                <h2 class="text-xl font-bold mb-4">Requirements</h2>
                <div class="prose max-w-none">
                    <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                </div>
            </div>

            <div class="border-t pt-6 mt-6">
                <h2 class="text-xl font-bold mb-4">About the Company</h2>
                <div class="prose max-w-none">
                    <?php echo nl2br(htmlspecialchars($job['company_description'])); ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function applyForJob() {
            if (confirm('Are you sure you want to apply for this position?')) {
                fetch('../../includes/jobseeker/apply_job.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `job_id=<?php echo $job_id; ?>`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Application submitted successfully!');
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to submit application');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to submit application');
                });
            }
        }
    </script>
</body>
</html>
