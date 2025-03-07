<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get the employer's company_id
    $query = "SELECT company_id FROM companies WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->execute();
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($company) {
        // Collect form data
        $title = $_POST['title'];
        $description = $_POST['description'];
        $requirements = $_POST['requirements'];
        $salary_range = $_POST['salary_range'];
        $location = $_POST['location'];
        $job_type = $_POST['job_type'];
        $company_id = $company['company_id'];
        
        // Insert job posting
        $query = "INSERT INTO jobs (company_id, title, description, requirements, salary_range, location, job_type) 
                  VALUES (:company_id, :title, :description, :requirements, :salary_range, :location, :job_type)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":company_id", $company_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":requirements", $requirements);
        $stmt->bindParam(":salary_range", $salary_range);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":job_type", $job_type);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Job posted successfully!";
            header("Location: ../../views/employer/dashboard.php");
        } else {
            $_SESSION['error'] = "Failed to post job. Please try again.";
            header("Location: ../../views/employer/post-job.php");
        }
    } else {
        $_SESSION['error'] = "Please complete your company profile first.";
        header("Location: ../../views/employer/company-profile.php");
    }
    exit();
}
?>
