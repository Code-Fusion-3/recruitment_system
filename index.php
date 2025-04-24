<?php
require_once 'config/config.php';
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Recruitment System - Find Your Dream Job or Perfect Candidate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section {
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold text-blue-600">Smart Recruitment System</h1>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="#home" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 border-b-2 border-blue-500">Home</a>
                        <a href="#features" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">Features</a>
                        <a href="#services" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">Services</a>
                        <a href="#how-it-works" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">How It Works</a>
                        <a href="#about" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">About Us</a>
                        <a href="#contact" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">Contact</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="views/auth/login.php" class="text-gray-600 hover:text-gray-800 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="views/auth/register.php" class="ml-4 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition duration-150 ease-in-out">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section h-screen flex items-center justify-center text-white">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold tracking-tight mb-4">
                Smart Recruitment System
            </h1>
            <p class="text-xl sm:text-2xl md:text-3xl mb-8">
                Connecting talent with opportunity through intelligent matching
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="views/auth/register.php?role=jobseeker" class="bg-blue-600 text-white px-6 py-3 rounded-md text-lg font-medium hover:bg-blue-700 transition duration-150 ease-in-out">
                    Find a Job
                </a>
                <a href="views/auth/register.php?role=employer" class="bg-white text-blue-600 px-6 py-3 rounded-md text-lg font-medium hover:bg-gray-100 transition duration-150 ease-in-out">
                    Post a Job
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Powerful Features for Modern Recruitment
                </h2>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Our platform offers cutting-edge tools to streamline the recruitment process for both employers and job seekers.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Feature 1 -->
                <div class="bg-gray-50 rounded-lg p-8 shadow-md feature-card">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-search-dollar text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Smart Job Matching</h3>
                    <p class="text-gray-600">
                        Our intelligent algorithm matches job seekers with the most relevant opportunities based on skills, experience, and preferences.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gray-50 rounded-lg p-8 shadow-md feature-card">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-file-alt text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Resume Management</h3>
                    <p class="text-gray-600">
                        Upload, store, and manage your resume securely. Employers can easily review and organize candidate applications.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gray-50 rounded-lg p-8 shadow-md feature-card">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-calendar-check text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Interview Scheduling</h3>
                    <p class="text-gray-600">
                        Streamline the interview process with our built-in scheduling system. Receive notifications and reminders automatically.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gray-50 rounded-lg p-8 shadow-md feature-card">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-building text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Company Profiles</h3>
                    <p class="text-gray-600">
                        Employers can create detailed company profiles to showcase their culture, benefits, and attract top talent.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gray-50 rounded-lg p-8 shadow-md feature-card">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-chart-line text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Analytics Dashboard</h3>
                    <p class="text-gray-600">
                        Track application status, job posting performance, and recruitment metrics with our comprehensive analytics.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gray-50 rounded-lg p-8 shadow-md feature-card">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-bell text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Real-time Notifications</h3>
                    <p class="text-gray-600">
                        Stay informed with instant notifications about application updates, interview invitations, and new job matches.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Our Services
                </h2>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Comprehensive recruitment solutions for employers and job seekers
                </p>
            </div>

            <div class="grid grid-cols-1 gap-10 md:grid-cols-2">
                <!-- For Job Seekers -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4">
                        <h3 class="text-2xl font-bold text-white">For Job Seekers</h3>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <span class="flex-shrink-0 h-6 w-6 text-blue-600"><i class="fas fa-check-circle"></i></span>
                                <span class="ml-3 text-gray-700">Create a professional profile to showcase your skills</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 h-6 w-6 text-blue-600"><i class="fas fa-check-circle"></i></span>
                                <span class="ml-3 text-gray-700">Upload and manage your resume securely</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 h-6 w-6 text-blue-600"><i class="fas fa-check-circle"></i></span>
                                <span class="ml-3 text-gray-700">Receive personalized job recommendations</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 h-6 w-6 text-blue-600"><i class="fas fa-check-circle"></i></span>
                                <span class="ml-3 text-gray-700">Track application status in real-time</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 h-6 w-6 text-blue-600"><i class="fas fa-check-circle"></i></span>
                                <span class="ml-3 text-gray-700">Schedule and manage interviews efficiently</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                            <a href="views/auth/register.php?role=jobseeker" class="block w-full bg-blue-600 text-white text-center px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150 ease-in-out">
                                Sign Up as Job Seeker
                            </a>
                        </div>
                    </div>
                </div>

                <!-- For Employers -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4">
                        <h3 class="text-2xl font-bold text-white">For Employers</h3>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <span class="flex-shrink-0 h-6 w-6 text-blue-600"><i class="fas fa-check-circle"></i></span>
                                <span class="ml-3 text-gray-700">Create and manage company profile</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 h-6 w-6 text-blue-600"><i class="fas fa-check-circle"></i></span>
                                <span class="ml-3 text-gray-700">Post job openings with detailed descriptions</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 h-6 w-6 text-blue-600"><i class="fas fa-check-circle"></i></span>
                                <span class="ml-3 text-gray-700">Review and filter applications efficiently</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 h-6 w-6 text-blue-600"><i class="fas fa-check-circle"></i></span>
                                <span class="ml-3 text-gray-700">Schedule interviews with promising candidates</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 h-6 w-6 text-blue-600"><i class="fas fa-check-circle"></i></span>
                                <span class="ml-3 text-gray-700">Access analytics to optimize recruitment process</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                        <a href="views/auth/register.php?role=employer" class="block w-full bg-blue-600 text-white text-center px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150 ease-in-out">
                                Sign Up as Employer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

      <!-- How It Works Section -->
      <section id="how-it-works" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    How It Works
                </h2>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Our platform simplifies the recruitment process for everyone involved
                </p>
            </div>

            <div class="relative">
                <!-- Timeline line -->
                <div class="hidden md:block absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-blue-200"></div>
                
                <!-- Step 1 -->
                <div class="relative mb-16">
                    <div class="md:flex items-center">
                        <div class="md:w-1/2 pr-8 md:text-right">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Create Your Profile</h3>
                            <p class="text-gray-600">
                                Sign up as a job seeker or employer and create your detailed profile to get started.
                            </p>
                        </div>
                        <div class="hidden md:flex absolute left-1/2 transform -translate-x-1/2 w-12 h-12 rounded-full bg-blue-600 text-white items-center justify-center">
                            <span class="font-bold">1</span>
                        </div>
                        <div class="md:w-1/2 pl-8 mt-8 md:mt-0">
                            <div class="max-w-xs mx-auto">
                                <img src="https://images.unsplash.com/photo-1517292987719-0369a794ec0f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Create Profile" class="rounded-lg shadow-md h-48 w-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2 -->
                <div class="relative mb-16">
                    <div class="md:flex items-center">
                        <div class="md:w-1/2 pr-8 md:text-right md:order-2">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Post or Find Jobs</h3>
                            <p class="text-gray-600">
                                Employers can post job openings while job seekers can search and apply for relevant positions.
                            </p>
                        </div>
                        <div class="hidden md:flex absolute left-1/2 transform -translate-x-1/2 w-12 h-12 rounded-full bg-blue-600 text-white items-center justify-center">
                            <span class="font-bold">2</span>
                        </div>
                        <div class="md:w-1/2 pl-8 mt-8 md:mt-0 md:order-1">
                            <div class="max-w-xs mx-auto">
                                <img src="https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Post or Find Jobs" class="rounded-lg shadow-md h-48 w-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="relative mb-16">
                    <div class="md:flex items-center">
                        <div class="md:w-1/2 pr-8 md:text-right">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Apply and Review</h3>
                            <p class="text-gray-600">
                                Job seekers apply with their resume and cover letter. Employers review applications and shortlist candidates.
                            </p>
                        </div>
                        <div class="hidden md:flex absolute left-1/2 transform -translate-x-1/2 w-12 h-12 rounded-full bg-blue-600 text-white items-center justify-center">
                            <span class="font-bold">3</span>
                        </div>
                        <div class="md:w-1/2 pl-8 mt-8 md:mt-0">
                            <div class="max-w-xs mx-auto">
                                <img src="https://images.unsplash.com/photo-1573497620053-ea5300f94f21?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Apply and Review" class="rounded-lg shadow-md h-48 w-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 4 -->
                <div class="relative">
                    <div class="md:flex items-center">
                        <div class="md:w-1/2 pr-8 md:text-right md:order-2">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Interview and Hire</h3>
                            <p class="text-gray-600">
                                Schedule interviews with promising candidates and complete the hiring process through our platform.
                            </p>
                        </div>
                        <div class="hidden md:flex absolute left-1/2 transform -translate-x-1/2 w-12 h-12 rounded-full bg-blue-600 text-white items-center justify-center">
                            <span class="font-bold">4</span>
                        </div>
                        <div class="md:w-1/2 pl-8 mt-8 md:mt-0 md:order-1">
                            <div class="max-w-xs mx-auto">
                                <img src="https://images.unsplash.com/photo-1560264280-88b68371db39?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Interview and Hire" class="rounded-lg shadow-md h-48 w-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- About Us Section -->
    <section id="about" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:flex lg:items-center lg:justify-between">
                <div class="lg:w-1/2 lg:pr-12">
                    <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl mb-6">
                        About Us
                    </h2>
                    <p class="text-lg text-gray-600 mb-6">
                        Smart Recruitment System is a cutting-edge platform designed to revolutionize the hiring process. Our mission is to connect talented individuals with the right opportunities while helping companies find the perfect candidates efficiently.
                    </p>
                    <p class="text-lg text-gray-600 mb-6">
                        Founded in 2023, our team of recruitment experts and technology innovators came together to address the challenges faced by both job seekers and employers in the modern job market.
                    </p>
                    <p class="text-lg text-gray-600 mb-6">
                        We leverage advanced technologies including intelligent matching algorithms and streamlined communication tools to make recruitment simpler, faster, and more effective for everyone involved.
                    </p>
                    <div class="flex items-center space-x-4 mt-8">
                        <a href="#" class="text-blue-600 hover:text-blue-800"><i class="fab fa-facebook-f text-2xl"></i></a>
                        <a href="#" class="text-blue-600 hover:text-blue-800"><i class="fab fa-twitter text-2xl"></i></a>
                        <a href="#" class="text-blue-600 hover:text-blue-800"><i class="fab fa-linkedin-in text-2xl"></i></a>
                        <a href="#" class="text-blue-600 hover:text-blue-800"><i class="fab fa-instagram text-2xl"></i></a>
                    </div>
                </div>
                <div class="mt-10 lg:mt-0 lg:w-1/2">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Our Team" class="rounded-lg shadow-xl">
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    What Our Users Say
                </h2>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Success stories from job seekers and employers
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Testimonial 1 -->
                <div class="bg-gray-50 rounded-lg p-8 shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">
                        "I found my dream job within two weeks of signing up! The platform matched me with opportunities that perfectly aligned with my skills and career goals."
                    </p>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                        <img class="h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=387&q=80" alt="User">
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">Sarah Johnson</h3>
                            <p class="text-sm text-gray-500">Software Developer</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-gray-50 rounded-lg p-8 shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">
                        "As an employer, this platform has transformed our recruitment process. We've reduced our hiring time by 40% and found exceptional talent that fits our company culture."
                    </p>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                        <img class="h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=387&q=80" alt="User">
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">Michael Brown</h3>
                            <p class="text-sm text-gray-500">HR Director, TechInnovate</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-gray-50 rounded-lg p-8 shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">
                        "The interview scheduling feature saved me so much time and hassle. I could track all my applications in one place and prepare effectively for each opportunity."
                    </p>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                        <img class="h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=387&q=80" alt="User">
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">David Wilson</h3>
                            <p class="text-sm text-gray-500">Marketing Specialist</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Contact Us
                </h2>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Have questions or need assistance? We're here to help!
                </p>
            </div>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                <div>
                    <form class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" id="name" name="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" name="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border">
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                            <input type="text" id="subject" name="subject" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border">
                        </div>
                        <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea id="message" name="message" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border"></textarea>
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150 ease-in-out">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
                <div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Get In Touch</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 text-blue-600">
                                    <i class="fas fa-map-marker-alt text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-700">123 Recruitment Street, Kigali, Rwanda</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 text-blue-600">
                                    <i class="fas fa-envelope text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-700">info@smartrecruitment.com</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 text-blue-600">
                                    <i class="fas fa-phone text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-700">+250 78 123 4567</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Business Hours</h3>
                            <div class="space-y-2">
                                <p class="text-gray-700">Monday - Friday: 9:00 AM - 6:00 PM</p>
                                <p class="text-gray-700">Saturday: 10:00 AM - 2:00 PM</p>
                                <p class="text-gray-700">Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-12 bg-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                    Ready to Transform Your Recruitment Process?
                </h2>
                <p class="mt-4 text-xl text-blue-100">
                    Join thousands of satisfied users on our platform today.
                </p>
                <div class="mt-8 flex justify-center">
                    <a href="views/auth/register.php" class="bg-white text-blue-600 px-6 py-3 rounded-md text-lg font-medium hover:bg-gray-100 transition duration-150 ease-in-out">
                        Get Started Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">Smart Recruitment System</h3>
                    <p class="text-gray-400">
                        Connecting talent with opportunity through intelligent matching.
                    </p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#home" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="#features" class="text-gray-400 hover:text-white">Features</a></li>
                        <li><a href="#services" class="text-gray-400 hover:text-white">Services</a></li>
                        <li><a href="#about" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#contact" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">For Job Seekers</h3>
                    <ul class="space-y-2">
                        <li><a href="views/auth/register.php?role=jobseeker" class="text-gray-400 hover:text-white">Create Account</a></li>
                        <li><a href="views/auth/login.php" class="text-gray-400 hover:text-white">Browse Jobs</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Career Resources</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Resume Tips</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">For Employers</h3>
                    <ul class="space-y-2">
                        <li><a href="views/auth/register.php?role=employer" class="text-gray-400 hover:text-white">Create Account</a></li>
                        <li><a href="views/auth/login.php" class="text-gray-400 hover:text-white">Post a Job</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Recruitment Tips</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Pricing</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    &copy; <?php echo date('Y'); ?> Smart Recruitment System. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
