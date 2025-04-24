<?php
/**
 * WhatsApp integration utility class
 * Uses WhatsApp Business API for sending messages
 */
class WhatsAppUtil {
    private $api_key;
    private $api_url;
    private $from_phone_number;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Load configuration from environment
        $this->api_key = getenv('WHATSAPP_API_KEY');
        $this->api_url = getenv('WHATSAPP_API_URL');
        $this->from_phone_number = getenv('WHATSAPP_PHONE_NUMBER');
        
        // Check if configuration is available
        if (empty($this->api_key) || empty($this->api_url) || empty($this->from_phone_number)) {
            error_log('WhatsApp API configuration is missing. Please check your .env file.');
        }
    }
    
    /**
     * Send WhatsApp message
     * 
     * @param string $to_phone_number Recipient phone number with country code
     * @param string $message Message text
     * @return bool Success status
     */
    public function sendMessage($to_phone_number, $message) {
        // Check if configuration is available
        if (empty($this->api_key) || empty($this->api_url) || empty($this->from_phone_number)) {
            error_log('WhatsApp API configuration is missing. Message not sent.');
            return false;
        }
        
        // Validate phone number format
        if (!$this->validatePhoneNumber($to_phone_number)) {
            error_log('Invalid phone number format: ' . $to_phone_number);
            return false;
        }
        
        // Prepare API request
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to_phone_number,
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];
        
        // Initialize cURL session
        $ch = curl_init($this->api_url);
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json'
        ]);
        
        // Execute cURL request
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Log any errors
        if (curl_errno($ch)) {
            error_log('WhatsApp API request failed: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        // Log response for debugging
        error_log('WhatsApp API response: ' . $response);
        
        // Check if request was successful
        return ($status_code >= 200 && $status_code < 300);
    }
    
    /**
     * Send interview notification
     * 
     * @param string $to_phone_number Recipient phone number
     * @param array $interview_data Interview details
     * @return bool Success status
     */
    public function sendInterviewNotification($to_phone_number, $interview_data) {
        $message = "Hello! You have been scheduled for an interview.\n\n";
        $message .= "Job: {$interview_data['job_title']}\n";
        $message .= "Company: {$interview_data['company_name']}\n";
        $message .= "Date: " . date('F j, Y g:i A', strtotime($interview_data['interview_date'])) . "\n";
        $message .= "Type: " . ucfirst($interview_data['interview_type']) . "\n\n";
        
        if (!empty($interview_data['notes'])) {
            $message .= "Notes: {$interview_data['notes']}\n\n";
        }
        
        $message .= "Please log in to your account for more details.";
        
        return $this->sendMessage($to_phone_number, $message);
    }
    
    /**
     * Validate phone number format
     * 
     * @param string $phone_number Phone number to validate
     * @return bool Is valid
     */
    private function validatePhoneNumber($phone_number) {
        // Basic validation - should start with + and contain only digits
        return preg_match('/^\+[0-9]{10,15}$/', $phone_number);
    }
}
?>
