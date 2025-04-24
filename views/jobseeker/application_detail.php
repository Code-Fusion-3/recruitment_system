<?php
session_start();
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Check if user is a jobseeker
if ($_SESSION['role'] !== 'jobseeker') {
    header("Location: ../../views/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if application ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No application specified.";
    header("Location: applications.php");
    exit();
}

$application_id = $_GET['id'];

// Get application details with job and company info
$query = "SELECT a.*, j.title as job_title, j.description as job_description, 
          j.requirements, j.location, j.job_type, j.salary_range,
          c.company_name, c.description as company_description, c.logo, c.website,
          i.interview_id, i.interview_date, i.interview_type, i.status as interview_status
          FROM applications a
          JOIN jobs j ON a.job_id = j.job_id
          JOIN companies c ON j.company_id = c.company_id
          LEFT JOIN interviews i ON a.application_id = i.application_id
          WHERE a.application_id = :application_id AND a.user_id = :user_id";
          
$stmt = $db->prepare($query);
$stmt->bindParam(":application_id", $application_id);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = "Application not found or you don't have permission to view it.";
    header("Location: applications.php");
    exit();
    
}

$application = $stmt->fetch(PDO::FETCH_ASSOC);
include_once '../templates/header.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details - Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Application Details</h1>
                <a href="applications.php" class="text-blue-500 hover:text-blue-700">Back to Applications</a>
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

            <!-- Application Status -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold">Application Status</h2>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                        <?php 
                        switch($application['status']) {
                            case 'pending':
                                echo 'bg-yellow-100 text-yellow-800';
                                break;
                            case 'reviewed':
                                echo 'bg-blue-100 text-blue-800';
                                break;
                            case 'shortlisted':
                                echo 'bg-indigo-100 text-indigo-800';
                                break;
                            case 'rejected':
                                echo 'bg-red-100 text-red-800';
                                break;
                            case 'hired':
                                echo 'bg-green-100 text-green-800';
                                break;
                            default:
                                echo 'bg-gray-100 text-gray-800';
                        }
                        ?>">
                        <?php echo ucfirst($application['status']); ?>
                    </span>
                </div>
                <div class="mt-4">
                    <p class="text-gray-600">Applied on: <span class="text-gray-800"><?php echo date('F j, Y', strtotime($application['applied_at'])); ?></span></p>
                    
                    <?php if ($application['interview_id']): ?>
                        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                            <h3 class="font-semibold text-blue-800">Interview Scheduled</h3>
                            <p class="text-gray-700">
                                Date: <?php echo date('F j, Y - g:i A', strtotime($application['interview_date'])); ?><br>
                                Type: <?php echo ucfirst($application['interview_type']); ?><br>
                                Status: <?php echo ucfirst($application['interview_status']); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Job Details -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center mb-4">
                    <?php if ($application['logo']): ?>
                        <img src="../../<?php echo htmlspecialchars($application['logo']); ?>" alt="<?php echo htmlspecialchars($application['company_name']); ?>" class="h-16 w-16 object-contain mr-4">
                    <?php endif; ?>
                    <div>
                        <h2 class="text-xl font-bold"><?php echo htmlspecialchars($application['job_title']); ?></h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($application['company_name']); ?></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-gray-600">Location: <span class="text-gray-800"><?php echo htmlspecialchars($application['location']); ?></span></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Job Type: <span class="text-gray-800"><?php echo ucfirst(htmlspecialchars($application['job_type'])); ?></span></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Salary Range: <span class="text-gray-800"><?php echo htmlspecialchars($application['salary_range']); ?></span></p>
                    </div>
                    <?php if ($application['website']): ?>
                    <div>
                        <p class="text-gray-600">Website: 
                            <a href="<?php echo htmlspecialchars($application['website']); ?>" target="_blank" class="text-blue-500 hover:text-blue-700">
                                <?php echo htmlspecialchars($application['website']); ?>
                            </a>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-6">
                    <h3 class="font-semibold mb-2">Job Description</h3>
                    <div class="text-gray-700">
                        <?php echo nl2br(htmlspecialchars($application['job_description'])); ?>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h3 class="font-semibold mb-2">Requirements</h3>
                    <div class="text-gray-700">
                        <?php echo nl2br(htmlspecialchars($application['requirements'])); ?>
                    </div>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-2">About the Company</h3>
                    <div class="text-gray-700">
                        <?php echo nl2br(htmlspecialchars($application['company_description'])); ?>
                    </div>
                </div>
            </div>
            
                       <!-- Application Details -->
                       <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Your Application</h2>
                
                <?php if (!empty($application['resume_path'])): ?>
                <div class="mb-4">
                    <h3 class="font-semibold mb-2">Resume</h3>
                    <a href="../../<?php echo htmlspecialchars($application['resume_path']); ?>" 
                       class="text-blue-500 hover:text-blue-700 flex items-center" 
                       target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        View Resume
                    </a>
                </div>
                <?php else: ?>
                <div class="mb-4">
                    <h3 class="font-semibold mb-2">Resume</h3>
                    <p class="text-gray-500 italic">No resume was attached to this application.</p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($application['cover_letter'])): ?>
                <div>
                    <h3 class="font-semibold mb-2">Cover Letter</h3>
                    <div class="text-gray-700 p-4 bg-gray-50 rounded-lg">
                        <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                    </div>
                </div>
                <?php else: ?>
                <div>
                    <h3 class="font-semibold mb-2">Cover Letter</h3>
                    <p class="text-gray-500 italic">No cover letter was included with this application.</p>
                </div>
                <?php endif; ?>
                
                <?php if ($application['status'] === 'pending'): ?>
                <div class="mt-6">
                    <a href="../../includes/jobseeker/withdraw_application.php?id=<?php echo $application['application_id']; ?>" 
                       class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded inline-block"
                       onclick="return confirm('Are you sure you want to withdraw this application?');">
                        Withdraw Application
                    </a>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</body>
</html>
