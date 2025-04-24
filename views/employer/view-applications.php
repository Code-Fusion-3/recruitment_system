<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php");
    exit();
}

// Get job ID from URL
$job_id = isset($_GET['job_id']) ? $_GET['job_id'] : 0;

$database = new Database();
$db = $database->getConnection();

// Get job details
$query = "SELECT j.*, c.company_name 
          FROM jobs j 
          INNER JOIN companies c ON j.company_id = c.company_id 
          WHERE j.job_id = :job_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":job_id", $job_id);
$stmt->execute();
$job = $stmt->fetch(PDO::FETCH_ASSOC);

// Get applications for this job
$query = "SELECT a.*, u.first_name, u.last_name, u.email 
          FROM applications a 
          INNER JOIN users u ON a.user_id = u.user_id 
          WHERE a.job_id = :job_id 
          ORDER BY a.applied_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(":job_id", $job_id);
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
include_once '../templates/header.php';
?>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Job Details</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Position: <?php echo htmlspecialchars($job['title']); ?></p>
                    <p class="text-gray-600">Company: <?php echo htmlspecialchars($job['company_name']); ?></p>
                    <p class="text-gray-600">Location: <?php echo htmlspecialchars($job['location']); ?></p>
                    <p class="text-gray-600">Type: <?php echo ucfirst(htmlspecialchars($job['job_type'])); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Salary Range: <?php echo htmlspecialchars($job['salary_range']); ?></p>
                    <p class="text-gray-600">Posted: <?php echo date('M d, Y', strtotime($job['created_at'])); ?></p>
                    <p class="text-gray-600">Status: 
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php echo $job['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo ucfirst(htmlspecialchars($job['status'])); ?>
                        </span>
                    </p>
                    <p class="text-gray-600">Total Applications: <?php echo count($applications); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Applications (<?php echo count($applications); ?>)</h2>
                    <div class="flex gap-2">
                        <button onclick="filterApplications('all')" class="text-sm bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">All</button>
                        <button onclick="filterApplications('pending')" class="text-sm bg-yellow-100 px-3 py-1 rounded hover:bg-yellow-200">Pending</button>
                        <button onclick="filterApplications('shortlisted')" class="text-sm bg-green-100 px-3 py-1 rounded hover:bg-green-200">Shortlisted</button>
                        <button onclick="filterApplications('rejected')" class="text-sm bg-red-100 px-3 py-1 rounded hover:bg-red-200">Rejected</button>
                    </div>
                </div>

                <?php if ($applications): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>

                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="applicationsTable">
                                <?php foreach ($applications as $application): ?>
                                    <tr class="application-row" data-status="<?php echo $application['status']; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($application['email']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($application['applied_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $application['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                        ($application['status'] === 'shortlisted' ? 'bg-green-100 text-green-800' : 
                                                        'bg-red-100 text-red-800'); ?>">
                                                <?php echo ucfirst($application['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <a href="view-resume.php?application_id=<?php echo $application['application_id']; ?>" 
                                                   class="text-blue-600 hover:text-blue-900">View Resume</a>
                                                
                                                <?php if ($application['status'] === 'pending'): ?>
                                                    <button onclick="updateStatus(<?php echo $application['application_id']; ?>, 'shortlisted')" 
                                                            class="text-green-600 hover:text-green-900">Shortlist</button>
                                                    <button onclick="updateStatus(<?php echo $application['application_id']; ?>, 'rejected')" 
                                                            class="text-red-600 hover:text-red-900">Reject</button>
                                                <?php elseif ($application['status'] === 'shortlisted'): ?>
                                                    <button onclick="updateStatus(<?php echo $application['application_id']; ?>, 'hired')" 
                                                            class="text-green-600 hover:text-green-900">Hire</button>
                                                    <button onclick="updateStatus(<?php echo $application['application_id']; ?>, 'rejected')" 
                                                            class="text-red-600 hover:text-red-900">Reject</button>
                                                <?php elseif ($application['status'] === 'rejected'): ?>
                                                    <button onclick="updateStatus(<?php echo $application['application_id']; ?>, 'shortlisted')" 
                                                            class="text-green-600 hover:text-green-900">Reconsider</button>
                                                <?php elseif ($application['status'] === 'hired'): ?>
                                                    <span class="text-gray-500">Hired</span>
                                                <?php endif; ?>
                                                
                                                <a href="schedule_interview.php?application_id=<?php echo $application['application_id']; ?>" 
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                   <?php 
                                                   // Check if interview exists
                                                   $interview_query = "SELECT interview_id FROM interviews WHERE application_id = :application_id";
                                                   $interview_stmt = $db->prepare($interview_query);
                                                   $interview_stmt->bindParam(":application_id", $application['application_id']);
                                                   $interview_stmt->execute();
                                                   
                                                   echo $interview_stmt->rowCount() > 0 ? "Update Interview" : "Schedule Interview"; 
                                                   ?>
                                                </a>
                                            </div>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-gray-500 py-4">
                        No applications received yet
                    </div>
                <?php endif; ?>
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
function filterApplications(status) {
        const rows = document.querySelectorAll('.application-row');
        
        rows.forEach(row => {
            if (status === 'all') {
                row.style.display = '';
            } else {
                const rowStatus = row.getAttribute('data-status');
                row.style.display = rowStatus === status ? '' : 'none';
            }
        });
    }

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
