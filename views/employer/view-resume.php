<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php");
    exit();
}

$application_id = isset($_GET['application_id']) ? $_GET['application_id'] : 0;

$database = new Database();
$db = $database->getConnection();

// Get application details with user and job information
$query = "SELECT a.*, u.first_name, u.last_name, u.email, u.phone,
          j.title as job_title, j.company_id, r.* 
          FROM applications a 
          INNER JOIN users u ON a.user_id = u.user_id 
          INNER JOIN jobs j ON a.job_id = j.job_id 
          LEFT JOIN resumes r ON a.user_id = r.user_id 
          WHERE a.application_id = :application_id";

$stmt = $db->prepare($query);
$stmt->bindParam(":application_id", $application_id);
$stmt->execute();
$application = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Resume - Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Applicant Resume</h1>
            <a href="javascript:history.back()" class="text-blue-500 hover:text-blue-700">Back to Applications</a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="border-b pb-4 mb-4">
                <h2 class="text-xl font-semibold mb-2">Personal Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Name: <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></p>
                        <p class="text-gray-600">Email: <?php echo htmlspecialchars($application['email']); ?></p>
                        <p class="text-gray-600">Phone: <?php echo htmlspecialchars($application['phone'] ?? 'Not provided'); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Applied For: <?php echo htmlspecialchars($application['job_title']); ?></p>
                        <p class="text-gray-600">Status: 
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $application['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($application['status'] === 'shortlisted' ? 'bg-green-100 text-green-800' : 
                                        'bg-red-100 text-red-800'); ?>">
                                <?php echo ucfirst($application['status']); ?>
                            </span>
                        </p>
                        <p class="text-gray-600">Applied Date: <?php echo date('M d, Y', strtotime($application['applied_at'])); ?></p>
                    </div>
                </div>
            </div>

            <?php if (isset($application['resume_path'])): ?>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">Resume</h2>
                    <div class="bg-gray-50 p-4 rounded">
                        <a href="../../<?php echo htmlspecialchars($application['resume_path']); ?>" 
                           target="_blank"
                           class="text-blue-500 hover:text-blue-700">
                            View Resume Document
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex justify-end space-x-4 mt-6">
                <button onclick="updateStatus(<?php echo $application['application_id']; ?>, 'shortlisted')" 
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Shortlist Candidate
                </button>
                <button onclick="updateStatus(<?php echo $application['application_id']; ?>, 'rejected')" 
                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    Reject Application
                </button>
            </div>
        </div>
    </div>

    <script>
        function updateStatus(applicationId, status) {
            if (confirm('Are you sure you want to update this application status?')) {
                fetch('../../includes/employer/update_application_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `application_id=${applicationId}&status=${status}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update status');
                });
            }
        }
    </script>
</body>
</html>
