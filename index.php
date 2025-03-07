<?php
require_once 'config/config.php';
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">Smart Recruitment System</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="views/auth/login.php" class="text-gray-600 hover:text-gray-800">Login</a>
                    <a href="views/auth/register.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto mt-10 px-4">
        <h2 class="text-3xl font-bold text-center text-gray-800">Welcome to Smart Recruitment System</h2>
        <p class="text-center mt-4 text-gray-600">Find your dream job or hire the perfect candidate</p>
    </main>
</body>
</html>