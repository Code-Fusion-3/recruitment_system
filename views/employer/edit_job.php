<?php
session_start();
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();
// Check if user is an employer
if ($_SESSION['role'] !== 'employer') {
    header("Location: ../../views/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if job ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No job specified.";
    header("Location: jobs.php");
    exit();
}

$job_id = $_GET['id'];

// Verify this job belongs to the employer
$job_query = "SELECT j.* FROM jobs j 
             JOIN companies c ON j.company_id = c.company_id 
             WHERE j.job_id = :job_id AND c.user_id = :user_id";
$job_stmt = $db->prepare($job_query);
$job_stmt->bindParam(":job_id", $job_id);
$job_stmt->bindParam(":user_id", $user_id);
$job_stmt->execute();

if ($job_stmt->rowCount() === 0) {
    $_SESSION['error'] = "You don't have permission to edit this job.";
    header("Location: jobs.php");
    exit();
}

$job = $job_stmt->fetch(PDO::FETCH_ASSOC);
include_once '../templates/header.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold mb-6">Edit Job</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="../../includes/employer/job_update_process.php" method="POST">
                <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                        Job Title
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="title" 
                           name="title" 
                           type="text" 
                           value="<?php echo htmlspecialchars($job['title']); ?>"
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                        Job Description
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                              id="description" 
                              name="description" 
                              rows="6" 
                              required><?php echo htmlspecialchars($job['description']); ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="salary_range">
                            Salary Range
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                               id="salary_range" 
                               name="salary_range" 
                               type="text"
                               value="<?php echo htmlspecialchars($job['salary_range']); ?>"
                               required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="location">
                            Location
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                               id="location" 
                               name="location" 
                               type="text" 
                               value="<?php echo htmlspecialchars($job['location']); ?>"
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="job_type">
                        Job Type
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="job_type"
                            name="job_type"
                            required>
                        <option value="full-time" <?php echo $job['job_type'] === 'full-time' ? 'selected' : ''; ?>>Full Time</option>
                        <option value="part-time" <?php echo $job['job_type'] === 'part-time' ? 'selected' : ''; ?>>Part Time</option>
                        <option value="contract" <?php echo $job['job_type'] === 'contract' ? 'selected' : ''; ?>>Contract</option>
                        <option value="internship" <?php echo $job['job_type'] === 'internship' ? 'selected' : ''; ?>>Internship</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="requirements">
                        Requirements
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                              id="requirements" 
                              name="requirements" 
                              rows="4" 
                              required><?php echo htmlspecialchars($job['requirements']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                        Job Status
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="status"
                            name="status"
                            required>
                        <option value="open" <?php echo $job['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="closed" <?php echo $job['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        <option value="draft" <?php echo $job['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>

                <?php if ($job['deadline']): ?>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="deadline">
                        Application Deadline
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="deadline" 
                           name="deadline" 
                           type="date" 
                           value="<?php echo htmlspecialchars($job['deadline']); ?>">
                </div>
                <?php else: ?>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="deadline">
                        Application Deadline (Optional)
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="deadline" 
                           name="deadline" 
                           type="date">
                </div>
                <?php endif; ?>

                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                            type="submit">
                        Update Job
                    </button>
                    <a href="jobs.php" 
                       class="text-blue-500 hover:text-blue-700">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
