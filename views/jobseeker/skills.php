<?php
session_start();
require_once '../../config/database.php';
//   

// Check if user is a jobseeker
if ($_SESSION['role'] !== 'jobseeker') {
    header("Location: ../auth/login.php");
    exit();
}

// Get user skills
$user_id = $_SESSION['user_id'];
$query = "SELECT s.skill_id, s.skill_name FROM skills s 
          JOIN user_skills us ON s.skill_id = us.skill_id 
          WHERE us.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$user_skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all available skills for dropdown
$query = "SELECT skill_id, skill_name FROM skills ORDER BY skill_name";
$stmt = $db->prepare($query);
$stmt->execute();
$all_skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once '../templates/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manage Your Skills</h1>
        <a href="dashboard.php" class="text-blue-500 hover:text-blue-700">Back to Dashboard</a>
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
        <h2 class="text-xl font-bold mb-4">Your Current Skills</h2>
        
        <div class="flex flex-wrap gap-2 mb-6">
            <?php if (count($user_skills) > 0): ?>
                <?php foreach ($user_skills as $skill): ?>
                    <div class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full flex items-center">
                        <span><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                        <form action="../../includes/jobseeker/remove_skill.php" method="POST" class="ml-2">
                            <input type="hidden" name="skill_id" value="<?php echo $skill['skill_id']; ?>">
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500">You haven't added any skills yet.</p>
            <?php endif; ?>
        </div>

        <h3 class="font-semibold mb-2">Add Skills</h3>
        <form action="../../includes/jobseeker/add_skill.php" method="POST" class="flex gap-2">
            <select name="skill_id" class="shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-grow">
                <option value="">Select a skill to add</option>
                <?php foreach ($all_skills as $skill): ?>
                    <option value="<?php echo $skill['skill_id']; ?>"><?php echo htmlspecialchars($skill['skill_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Add Skill
            </button>
        </form>

        <div class="mt-4">
            <h3 class="font-semibold mb-2">Can't find your skill? Add a new one:</h3>
            <form action="../../includes/jobseeker/create_skill.php" method="POST" class="flex gap-2">
                <input type="text" name="skill_name" placeholder="Enter new skill" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Create Skill
                </button>
            </form>
        </div>
    </div>
</div>

  