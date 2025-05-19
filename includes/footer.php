<?php $base_url = '/'; // Adjust if your project is in a sub-sub-folder ?>
<footer class="bg-gray-800 text-white ">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center">
                    <img src="<?php echo $base_url; ?>pictures/Crown.jpg" alt="Crown Institute Logo" class="h-10 w-10">
                    <h3 class="ml-2 text-sm font-semibold text-gray-400 tracking-wider uppercase">Crown Institute</h3>
                </div>
                <p class="mt-4 text-base text-gray-300">
                    From Possibility to Actuality
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Navigation</h3>
                <ul class="mt-4 space-y-4">
                    <li><a href="<?php echo $base_url; ?>index.php"
                            class="text-base text-gray-300 hover:text-white">Home</a></li>
                    <li><a href="<?php echo $base_url; ?>about.php"
                            class="text-base text-gray-300 hover:text-white">About</a></li>
                    <li><a href="<?php echo $base_url; ?>courseinfo.php"
                            class="text-base text-gray-300 hover:text-white">Course Info</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo $base_url; ?>actions/logout_action.php"
                            class="text-base text-gray-300 hover:text-white">Logout</a></li>
                    <?php else: ?>
                    <li><a href="<?php echo $base_url; ?>login.php"
                            class="text-base text-gray-300 hover:text-white">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Legal</h3>
                <ul class="mt-4 space-y-4">
                    <li><a href="#" class="text-base text-gray-300 hover:text-white">Privacy Policy</a></li>
                    <li><a href="#" class="text-base text-gray-300 hover:text-white">Terms of Service</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Connect</h3>
                <div class="mt-4 flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        <div class="mt-12 border-t border-gray-700 pt-8">
            <p class="text-base text-gray-400 text-center">
                &copy; <?php echo date("Y"); ?> Crown Institute. All rights reserved.
            </p>
        </div>
    </div>
</footer>
</body>

</html>