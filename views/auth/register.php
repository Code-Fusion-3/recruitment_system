<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Recruitment System</title>
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
            background-image: url('https://images.pexels.com/photos/3184292/pexels-photo-3184292.jpeg?auto=compress&cs=tinysrgb&w=1920');
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
    <?php
    $role = isset($_GET['role']) ? $_GET['role'] : 'user';
    $headerText = $role === 'jobseeker' ? 'Register as Job Seeker' : ($role === 'employer' ? 'Register as Employer' : 'Create Account');
    ?>
    <div class="form-container p-8 rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-primary-800"><?php echo $headerText; ?></h2>
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
        ?>
        
        <form action="../../includes/auth/register_process.php" method="POST" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="first_name">
                        First Name
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input class="pl-10 w-full px-4 py-2 bg-white bg-opacity-80 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300" 
                               id="first_name" 
                               name="first_name" 
                               type="text" 
                               required>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="last_name">
                        Last Name
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input class="pl-10 w-full px-4 py-2 bg-white bg-opacity-80 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300" 
                               id="last_name" 
                               name="last_name" 
                               type="text" 
                               required>
                    </div>
                </div>
            </div>
            
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
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="role">
                    Register as
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user-tag text-gray-400"></i>
                    </div>
                    <select class="pl-10 w-full px-4 py-2 bg-white bg-opacity-80 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300 appearance-none"
                            id="role"
                            name="role"
                            required>
                        <option value="jobseeker" <?php echo (isset($_GET['role']) && $_GET['role'] == 'jobseeker') ? 'selected' : ''; ?>>Job Seeker</option>
                        <option value="employer" <?php echo (isset($_GET['role']) && $_GET['role'] == 'employer') ? 'selected' : ''; ?>>Employer</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </div>
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
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="confirm_password">
                    Confirm Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input class="pl-10 w-full px-4 py-2 bg-white bg-opacity-80 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300" 
                           id="confirm_password" 
                           name="confirm_password" 
                           type="password" 
                           required>
                </div>
            </div>
            
            <div>
                <button class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-4 focus:ring-primary-300 transition-all duration-300 shadow-lg" 
                        type="submit">
                    Create Account
                </button>
            </div>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">Already have an account?</p>
            <a href="login.php" class="inline-block mt-2 text-primary-600 hover:text-primary-800 font-medium transition-colors duration-300">
                Sign in to your account
            </a>
        </div>
    </div>
    
    <script>
        // Optional: Add password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            // You could implement password strength checking here
        });
    </script>
</body>
</html>
