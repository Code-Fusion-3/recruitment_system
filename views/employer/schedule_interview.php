<?php
session_start();
require_once '../../config/database.php';
  

// Check if user is an employer
if ($_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php");
    exit();
}

// Get application ID from URL
if (!isset($_GET['application_id'])) {
    header("Location: applications.php");
    exit();
}

$application_id = $_GET['application_id'];

// Get application details
$query = "SELECT a.*, j.title as job_title, u.first_name, u.last_name, u.email 
          FROM applications a
          JOIN jobs j ON a.job_id = j.job_id
          JOIN users u ON a.user_id = u.user_id
          WHERE a.application_id = :application_id
          AND j.company_id IN (SELECT company_id FROM companies WHERE user_id = :user_id)";
$stmt = $db->prepare($query);
$stmt->bindParam(":application_id", $application_id);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = "Application not found or you don't have permission to schedule an interview.";
    header("Location: applications.php");
    exit();
}

$application = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if interview already exists
$query = "SELECT * FROM interviews WHERE application_id = :application_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":application_id", $application_id);
$stmt->execute();
$existing_interview = $stmt->fetch(PDO::FETCH_ASSOC);

include_once '../templates/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Schedule Interview</h1>
        <a href="javascript:history.back()" class="text-blue-500 hover:text-blue-700">Back to Applications</a>
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

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Application Details</h2>
        <div class="mb-4">
            <p><strong>Job:</strong> <?php echo htmlspecialchars($application['job_title']); ?></p>
            <p><strong>Applicant:</strong> <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($application['status'])); ?></p>
            <p><strong>Applied on:</strong> <?php echo date('F j, Y', strtotime($application['applied_at'])); ?></p>
        </div>
        <h2 class="text-xl font-bold mb-4"><?php echo $existing_interview ? 'Update Interview' : 'Schedule New Interview'; ?></h2>
        <form action="../../includes/employer/save_interview.php" method="POST" class="space-y-4">
            <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
            
            <?php if ($existing_interview): ?>
                <input type="hidden" name="interview_id" value="<?php echo $existing_interview['interview_id']; ?>">
            <?php endif; ?>
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="interview_date">
                    Interview Date and Time
                </label>
                <input type="datetime-local" id="interview_date" name="interview_date" 
                       value="<?php echo $existing_interview ? date('Y-m-d\TH:i', strtotime($existing_interview['interview_date'])) : ''; ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       required>
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="interview_type">
                    Interview Type
                </label>
                <select id="interview_type" name="interview_type" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                    <option value="">Select interview type</option>
                    <option value="online" <?php echo ($existing_interview && $existing_interview['interview_type'] == 'online') ? 'selected' : ''; ?>>Online</option>
                    <option value="in-person" <?php echo ($existing_interview && $existing_interview['interview_type'] == 'in-person') ? 'selected' : ''; ?>>In-person</option>
                    <option value="phone" <?php echo ($existing_interview && $existing_interview['interview_type'] == 'phone') ? 'selected' : ''; ?>>Phone</option>
                </select>
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">
                    Notes (Optional)
                </label>
                <textarea id="notes" name="notes" rows="4"
                          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo $existing_interview ? htmlspecialchars($existing_interview['notes']) : ''; ?></textarea>
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                    Status
                </label>
                <select id="status" name="status" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                    <option value="scheduled" <?php echo ($existing_interview && $existing_interview['status'] == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="completed" <?php echo ($existing_interview && $existing_interview['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo ($existing_interview && $existing_interview['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <?php echo $existing_interview ? 'Update Interview' : 'Schedule Interview'; ?>
                </button>
                
                <?php if ($existing_interview): ?>
                <a href="../../includes/employer/delete_interview.php?interview_id=<?php echo $existing_interview['interview_id']; ?>" 
                   class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                   onclick="return confirm('Are you sure you want to delete this interview?');">
                    Delete Interview
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

  

