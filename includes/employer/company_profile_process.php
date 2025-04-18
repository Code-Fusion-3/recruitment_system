<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Collect form data
    $company_name = $_POST['company_name'];
    $description = $_POST['description'];
    $industry = $_POST['industry'];
    $location = $_POST['location'];
    $website = $_POST['website'];
    $user_id = $_SESSION['user_id'];
    
    // Handle logo upload
    $logo_path = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $upload_dir = '../../uploads/company_logos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)) {
            $logo_path = 'uploads/company_logos/' . $file_name;
        }
    }
    
    // Check if company profile already exists
    $query = "SELECT company_id FROM companies WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Update existing profile
        $query = "UPDATE companies 
                 SET company_name = :company_name, 
                     description = :description,
                     industry = :industry,
                     location = :location,
                     website = :website" .
                 ($logo_path ? ", logo = :logo" : "") .
                 " WHERE user_id = :user_id";
    } else {
        // Create new profile
        $query = "INSERT INTO companies (user_id, company_name, description, industry, location, website" . 
                ($logo_path ? ", logo" : "") . ") 
                VALUES (:user_id, :company_name, :description, :industry, :location, :website" .
                ($logo_path ? ", :logo" : "") . ")";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":company_name", $company_name);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":industry", $industry);
    $stmt->bindParam(":location", $location);
    $stmt->bindParam(":website", $website);
    if ($logo_path) {
        $stmt->bindParam(":logo", $logo_path);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Company profile updated successfully!";
        header("Location: ../../views/employer/dashboard.php");
    } else {
        $_SESSION['error'] = "Failed to update company profile. Please try again.";
        header("Location: ../../views/employer/company-profile.php");
    }
    exit();
}
?>
