<?php
session_start();
require_once '../../config/database.php';
  // Create database connection
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$reply_to = isset($_GET['reply_to']) ? intval($_GET['reply_to']) : null;
$to_user_id = isset($_GET['to']) ? intval($_GET['to']) : null;

$subject = '';
$message = '';
$recipient = null;

// If replying to a message
if ($reply_to) {
    $query = "SELECT m.*, u.first_name, u.last_name, u.user_id as sender_user_id
              FROM messages m
              JOIN users u ON m.sender_id = u.user_id
              WHERE m.message_id = :message_id AND m.receiver_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":message_id", $reply_to);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $original_message = $stmt->fetch(PDO::FETCH_ASSOC);
        $to_user_id = $original_message['sender_user_id'];
        $recipient = $original_message['first_name'] . ' ' . $original_message['last_name'];
        $subject = 'Re: ' . ($original_message['subject'] ? $original_message['subject'] : '(No subject)');
        $message = "\n\n\n----- Original Message -----\n" . $original_message['message_text'];
    }
}

// If sending to a specific user
if ($to_user_id && !$recipient) {
    $query = "SELECT first_name, last_name FROM users WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $to_user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $recipient = $user_data['first_name'] . ' ' . $user_data['last_name'];
    } else {
        $to_user_id = null;
    }
}

// Get potential recipients based on user role
$recipients = [];

if ($_SESSION['role'] == 'jobseeker') {
    // Jobseekers can message employers they've applied to
    $query = "SELECT DISTINCT u.user_id, u.first_name, u.last_name, c.company_name
              FROM users u
              JOIN companies c ON u.user_id = c.user_id
              JOIN jobs j ON c.company_id = j.company_id
              JOIN applications a ON j.job_id = a.job_id
              WHERE a.user_id = :user_id
              ORDER BY u.first_name, u.last_name";
} else if ($_SESSION['role'] == 'employer') {
    // Employers can message jobseekers who applied to their jobs
    $query = "SELECT DISTINCT u.user_id, u.first_name, u.last_name
              FROM users u
              JOIN applications a ON u.user_id = a.user_id
              JOIN jobs j ON a.job_id = j.job_id
              JOIN companies c ON j.company_id = c.company_id
              WHERE c.user_id = :user_id
              ORDER BY u.first_name, u.last_name";
} else {
    // Admins can message anyone
    $query = "SELECT user_id, first_name, last_name, role
              FROM users
              WHERE user_id != :user_id
              ORDER BY role, first_name, last_name";
}

$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once '../templates/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Compose Message</h1>
        <a href="inbox.php" class="text-blue-500 hover:text-blue-700">Back to Inbox</a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="../../includes/messages/send_message.php" method="POST" class="space-y-4">
            <?php if ($reply_to): ?>
                <input type="hidden" name="reply_to" value="<?php echo $reply_to; ?>">
            <?php endif; ?>
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="recipient">
                    To:
                </label>
                <?php if ($to_user_id): ?>
                    <input type="hidden" name="recipient_id" value="<?php echo $to_user_id; ?>">
                    <input type="text" value="<?php echo htmlspecialchars($recipient); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" readonly>
                <?php else: ?>
                    <select id="recipient" name="recipient_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">Select recipient</option>
                        <?php foreach ($recipients as $r): ?>
                            <option value="<?php echo $r['user_id']; ?>">
                                <?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?>
                                <?php if (isset($r['company_name'])): ?>
                                    (<?php echo htmlspecialchars($r['company_name']); ?>)
                                <?php elseif (isset($r['role'])): ?>
                                    (<?php echo ucfirst($r['role']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="subject">
                    Subject:
                </label>
                <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="message">
                    Message:
                </label>
                <textarea id="message" name="message" rows="10" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?php echo htmlspecialchars($message); ?></textarea>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Send Message
                </button>
            </div>
        </form>
    </div>
</div>

