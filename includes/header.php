<?php
    session_start();
    $base_url = '/'; 
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/style.css"> 
</head>

<body class="bg-gray-50">
    <nav class="bg-[#0B1F51] shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <a href="<?php echo $base_url; ?>index.php" class="flex items-center py-2 px-2">
                        <img src="<?php echo $base_url; ?>pictures/Crown.jpg" alt="Crown Institute Logo"
                            class="h-16 w-16">
                        <div class="ml-2">
                            <span class="font-bold text-xl text-white block">Crown Institute</span>
                            <span class="text-sm text-[#FFB800] italic">From Possibility to Actuality</span>
                        </div>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-1">
                    <a href="<?php echo $base_url; ?>index.php"
                        class="py-4 px-2 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-white border-b-4 border-[#FFB800]' : 'text-gray-300'; ?> font-semibold hover:text-white transition duration-300">Home</a>
                    <a href="<?php echo $base_url; ?>about.php"
                        class="py-4 px-2 <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'text-white border-b-4 border-[#FFB800]' : 'text-gray-300'; ?> font-semibold hover:text-white transition duration-300">About</a>
                    <a href="<?php echo $base_url; ?>courseinfo.php"
                        class="py-4 px-2 <?php echo basename($_SERVER['PHP_SELF']) == 'courseinfo.php' ? 'text-white border-b-4 border-[#FFB800]' : 'text-gray-300'; ?> font-semibold hover:text-white transition duration-300">Course
                        Info</a>

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] == 'student'): ?>
                    <a href="<?php echo $base_url; ?>student-dashboard.php"
                        class="py-4 px-2 <?php echo basename($_SERVER['PHP_SELF']) == 'student-dashboard.php' ? 'text-white border-b-4 border-[#FFB800]' : 'text-gray-300'; ?> font-semibold hover:text-white transition duration-300">Dashboard</a>
                    <a href="<?php echo $base_url; ?>courses.php"
                        class="py-4 px-2 <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'text-white border-b-4 border-[#FFB800]' : 'text-gray-300'; ?> font-semibold hover:text-white transition duration-300">All
                        Courses</a>
                    <?php elseif ($_SESSION['user_role'] == 'teacher'): ?>
                    <a href="<?php echo $base_url; ?>teacher-dashboard.php"
                        class="py-4 px-2 <?php echo basename($_SERVER['PHP_SELF']) == 'teacher-dashboard.php' ? 'text-white border-b-4 border-[#FFB800]' : 'text-gray-300'; ?> font-semibold hover:text-white transition duration-300">Dashboard</a>
                    <?php endif; ?>
                    <a href="<?php echo $base_url; ?>actions/logout_action.php"
                        class="py-2 px-4 bg-[#FFB800] text-[#0B1F51] font-semibold rounded-lg hover:bg-[#e5a600] transition duration-300">Logout</a>
                    <?php else: ?>
                    <a href="<?php echo $base_url; ?>login.php"
                        class="py-2 px-4 bg-[#FFB800] text-[#0B1F51] font-semibold rounded-lg hover:bg-[#e5a600] transition duration-300">Login</a>
                    <a href="<?php echo $base_url; ?>signup.php"
                        class="py-2 px-3 text-gray-300 font-semibold hover:text-white transition duration-300">Sign
                        Up</a>
                    <?php endif; ?>
                </div>
                <div class="md:hidden flex items-center">
                    <button class="outline-none mobile-menu-button">
                        <svg class=" w-6 h-6 text-gray-300 hover:text-white " x-show="!showMenu" fill="none"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="hidden mobile-menu">
            <ul class="">
                <li><a href="<?php echo $base_url; ?>index.php"
                        class="block text-sm px-2 py-4 text-white bg-[#FFB800] font-semibold">Home</a></li>
                <li><a href="<?php echo $base_url; ?>about.php"
                        class="block text-sm px-2 py-4 hover:bg-[#FFB800] transition duration-300">About</a></li>
                <li><a href="<?php echo $base_url; ?>courseinfo.php"
                        class="block text-sm px-2 py-4 hover:bg-[#FFB800] transition duration-300">Course Info</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_role'] == 'student'): ?>
                <li><a href="<?php echo $base_url; ?>student-dashboard.php"
                        class="block text-sm px-2 py-4 hover:bg-[#FFB800] transition duration-300">Dashboard</a></li>
                <li><a href="<?php echo $base_url; ?>courses.php"
                        class="block text-sm px-2 py-4 hover:bg-[#FFB800] transition duration-300">All Courses</a></li>
                <?php elseif ($_SESSION['user_role'] == 'teacher'): ?>
                <li><a href="<?php echo $base_url; ?>teacher-dashboard.php"
                        class="block text-sm px-2 py-4 hover:bg-[#FFB800] transition duration-300">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="<?php echo $base_url; ?>actions/logout_action.php"
                        class="block text-sm px-2 py-4 hover:bg-[#FFB800] transition duration-300">Logout</a></li>
                <?php else: ?>
                <li><a href="<?php echo $base_url; ?>login.php"
                        class="block text-sm px-2 py-4 hover:bg-[#FFB800] transition duration-300">Login</a></li>
                <li><a href="<?php echo $base_url; ?>signup.php"
                        class="block text-sm px-2 py-4 hover:bg-[#FFB800] transition duration-300">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <script>
        const btn = document.querySelector("button.mobile-menu-button");
        const menu = document.querySelector(".mobile-menu");

        btn.addEventListener("click", () => {
            menu.classList.toggle("hidden");
        });
        </script>
    </nav>