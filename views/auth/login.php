<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Recruitment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .bg-image {
            background-image: url('https://images.pexels.com/photos/3184465/pexels-photo-3184465.jpeg?auto=compress&cs=tinysrgb&w=1920');
            background-size: cover;
            background-position: center;
        }
        .form-container {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.85);
        }
    </style>
</head>
<body class="bg-image min-h-screen flex items-center justify-center py-12 px-4">
    <div class="form-container p-8 rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-primary-800">Sign In</h2>
            <a href="../../index.php" class="text-primary-600 hover:text-primary-800 flex items-center transition-colors duration-300">
                <i class="fas fa-arrow-left mr-2"></i> Back to Home
            </a>
        </div>
        
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p>' . $_SESSION['error'] . '</p>
                        </div>
                    </div>
                  </div>';
            unset($_SESSION['error']);
        }
        
        if (isset($_SESSION['success'])) {
            echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p>' . $_SESSION['success'] . '</p>
                        </div>
                    </div>
                  </div>';
            unset($_SESSION['success']);
        }
        ?>
        
        <form action="../../includes/auth/login_process.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="email">
                    Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input class="pl-10 w-full px-4 py-2 bg-white bg-opacity-80 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300" 
                           id="email" 
                           name="email" 
                           type="email" 
                           required>
                </div>
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">
                    Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input class="pl-10 w-full px-4 py-2 bg-white bg-opacity-80 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300" 
                           id="password" 
                           name="password" 
                           type="password" 
                           required>
                </div>
            </div>
            
            <div>
                <button class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-4 focus:ring-primary-300 transition-all duration-300 shadow-lg" 
                        type="submit">
                    Sign In
                </button>
            </div>
        </form>
        
        <div class="mt-6 space-y-4">
            <div class="text-center">
                <a href="register.php" class="text-primary-600 hover:text-primary-800 font-medium transition-colors duration-300">
                    Don't have an account? Register
                </a>
            </div>
            <div class="text-center">
                <a href="forgot_password.php" class="text-primary-600 hover:text-primary-800 font-medium transition-colors duration-300">
                    <i class="fas fa-key mr-1 text-sm"></i> Forgot your password?
                </a>
            </div>
        </div>
    </div>
</body>
</html>
