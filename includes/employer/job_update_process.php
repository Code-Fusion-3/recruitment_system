<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../../views/auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    $user_id = $_SESSION['user_id'];
    $job_id = $_POST['job_id'];
    
    // Verify this job belongs to the employer
    $check_query = "SELECT j.* FROM jobs j 
                   JOIN companies c ON j.company_id = c.company_id 
                   WHERE j.job_id = :job_id AND c.user_id = :user_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":job_id", $job_id);
    $check_stmt->bindParam(":user_id", $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "You don't have permission to update this job.";
        header("Location: ../../views/employer/jobs.php");
        exit();
    }
    
    // Get form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $requirements = trim($_POST['requirements']);
    $salary_range = trim($_POST['salary_range']);
    $location = trim($_POST['location']);
    $job_type = $_POST['job_type'];
    $status = $_POST['status'];
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
    
    // Validate required fields
    if (empty($title) || empty($description) || empty($requirements) || 
        empty($salary_range) || empty($location) || empty($job_type)) {
        $_SESSION['error'] = "All fields are required except deadline.";
        header("Location: ../../views/employer/edit_job.php?id=" . $job_id);
        exit();
    }
    
    // Update job in database
    $query = "UPDATE jobs 
              SET title = :title, 
                  description = :description, 
                  requirements = :requirements, 
                  salary_range = :salary_range, 
                  location = :location, 
                  job_type = :job_type, 
                  status = :status, 
                  deadline = :deadline
              WHERE job_id = :job_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":requirements", $requirements);
    $stmt->bindParam(":salary_range", $salary_range);
    $stmt->bindParam(":location", $location);
    $stmt->bindParam(":job_type", $job_type);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":deadline", $deadline);
    $stmt->bindParam(":job_id", $job_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Job updated successfully.";
        header("Location: ../../views/employer/jobs.php");
    } else {
        $_SESSION['error'] = "Failed to update job.";
        header("Location: ../../views/employer/edit_job.php?id=" . $job_id);
    }
    exit();
} else {
    // If not POST request, redirect to jobs page
    header("Location: ../../views/employer/jobs.php");
    exit();
}
?>