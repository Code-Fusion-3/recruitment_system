<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/env.php'; // Add this line to load environment variables
require_once '../utils/whatsapp.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../../views/auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $application_id = $_POST['application_id'];
    $interview_date = $_POST['interview_date'];
    $interview_type = $_POST['interview_type'];
    $notes = $_POST['notes'] ?? '';
    $status = $_POST['status'] ?? 'scheduled';
    $interview_id = $_POST['interview_id'] ?? null;
    
    // Validate data
    if (empty($application_id) || empty($interview_date) || empty($interview_type)) {
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: ../../views/employer/schedule_interview.php?application_id=" . $application_id);
        exit();
    }
    
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $db->beginTransaction();
        
        // Check if this is an update or a new interview
        if ($interview_id) {
            // Update existing interview
            $query = "UPDATE interviews 
                     SET interview_date = :interview_date, 
                         interview_type = :interview_type, 
                         notes = :notes, 
                         status = :status 
                     WHERE interview_id = :interview_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":interview_id", $interview_id);
        } else {
            // Insert new interview
            $query = "INSERT INTO interviews (application_id, interview_date, interview_type, notes, status) 
                     VALUES (:application_id, :interview_date, :interview_type, :notes, :status)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":application_id", $application_id);
        }
        
        $stmt->bindParam(":interview_date", $interview_date);
        $stmt->bindParam(":interview_type", $interview_type);
        $stmt->bindParam(":notes", $notes);
        $stmt->bindParam(":status", $status);
        
        if ($stmt->execute()) {
            // If this is a new interview, update application status to shortlisted
            if (!$interview_id) {
                $update_query = "UPDATE applications SET status = 'shortlisted' WHERE application_id = :application_id AND status = 'pending'";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(":application_id", $application_id);
                $update_stmt->execute();
            }
            
            // Get applicant details for notification
            $applicant_query = "SELECT u.first_name, u.last_name, u.email, u.phone, j.title as job_title, c.company_name
                              FROM applications a
                              JOIN users u ON a.user_id = u.user_id
                              JOIN jobs j ON a.job_id = j.job_id
                              JOIN companies c ON j.company_id = c.company_id
                              WHERE a.application_id = :application_id";
            $applicant_stmt = $db->prepare($applicant_query);
            $applicant_stmt->bindParam(":application_id", $application_id);
            $applicant_stmt->execute();
            $applicant = $applicant_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Create notification for the applicant
            $notification_query = "INSERT INTO notifications (user_id, title, message) 
                                 VALUES (:user_id, :title, :message)";
            $notification_stmt = $db->prepare($notification_query);
            $notification_stmt->bindParam(":user_id", $applicant['user_id']);
            
            $notification_title = $interview_id ? "Interview Updated" : "Interview Scheduled";
            $notification_message = $interview_id 
                ? "Your interview for {$applicant['job_title']} at {$applicant['company_name']} has been updated."
                : "You have been scheduled for an interview for {$applicant['job_title']} at {$applicant['company_name']}.";
            
            $notification_stmt->bindParam(":title", $notification_title);
            $notification_stmt->bindParam(":message", $notification_message);
            $notification_stmt->execute();
            
            // Send WhatsApp notification if phone number is available
            if (!empty($applicant['phone'])) {
                // Prepare interview data for WhatsApp notification
                $interview_data = [
                    'job_title' => $applicant['job_title'],
                    'company_name' => $applicant['company_name'],
                    'interview_date' => $interview_date,
                    'interview_type' => $interview_type,
                    'notes' => $notes
                ];
                
                // Format phone number for WhatsApp (ensure it has country code)
                $phone_number = $applicant['phone'];
                if (substr($phone_number, 0, 1) !== '+') {
                    // Add default country code if missing (adjust as needed)
                    $phone_number = '+1' . $phone_number;
                }
                
                // Send WhatsApp notification
                try {
                    $whatsapp = new WhatsAppUtil();
                    $whatsapp->sendInterviewNotification($phone_number, $interview_data);
                } catch (Exception $e) {
                    // Log error but continue with the process
                    error_log('WhatsApp notification failed: ' . $e->getMessage());
                }
            }
            
            $db->commit();
            $_SESSION['success'] = $interview_id ? "Interview updated successfully." : "Interview scheduled successfully.";
        } else {
            $db->rollBack();
            $_SESSION['error'] = "Failed to save interview.";
        }
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    }
    
    header("Location: ../../views/employer/schedule_interview.php?application_id=" . $application_id);
    exit();
}

// If not POST request, redirect back
header("Location: ../../views/employer/applications.php");
exit();
?>
