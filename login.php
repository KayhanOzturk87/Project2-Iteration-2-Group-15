   <?php 
    $page_title = "Login - Crown Institute";
    include 'includes/header.php'; 

    $form_errors = [];
    $form_data = []; 
    if (isset($_SESSION['errors'])) {
        $form_errors = $_SESSION['errors'];
        unset($_SESSION['errors']);
    }
     if (isset($_SESSION['form_data'])) { 
        $form_data = $_SESSION['form_data'];
        unset($_SESSION['form_data']);
    }
    if (isset($_SESSION['message'])) {
        $success_message = $_SESSION['message'];
        unset($_SESSION['message']);
    }
    ?>

   <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
       <div class="max-w-md w-full space-y-8">
           <div>
               <div class="flex justify-center">
                   <img src="<?php echo $base_url; ?>pictures/Crown.jpg" alt="Crown Institute Logo" class="h-24 w-24">
               </div>
               <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                   Sign in to your account
               </h2>
               <p class="mt-2 text-center text-sm text-gray-600">
                   Or <a href="<?php echo $base_url; ?>signup.php"
                       class="font-medium text-blue-900 hover:text-blue-800">create a new account</a>
               </p>
           </div>

           <?php if (isset($success_message)): ?>
           <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
               role="alert">
               <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
           </div>
           <?php endif; ?>

           <?php if (!empty($form_errors)): ?>
           <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
               <strong class="font-bold">Login Failed!</strong>
               <ul class="mt-2 list-disc list-inside">
                   <?php foreach ($form_errors as $error): ?>
                   <li><?php echo htmlspecialchars($error); ?></li>
                   <?php endforeach; ?>
               </ul>
           </div>
           <?php endif; ?>

           <form class="mt-8 space-y-6" action="<?php echo $base_url; ?>actions/login_action.php" method="POST">
               <div class="space-y-4">
                   <div>
                       <label class="block text-sm font-medium text-gray-700">Account Type</label>
                       <div class="mt-2 grid grid-cols-2 gap-3">
                           <div class="relative">
                               <input class="peer hidden" id="student" name="accountType" type="radio" value="student"
                                   <?php echo (!isset($form_data['accountType']) || $form_data['accountType'] == 'student') ? 'checked' : ''; ?>>
                               <label for="student"
                                   class="block cursor-pointer rounded-lg border border-gray-300 bg-white py-2 px-3 text-center hover:bg-gray-50 focus:outline-none peer-checked:border-[#FFB800] peer-checked:bg-[#FFB800] peer-checked:text-[#0B1F51] peer-checked:ring-1 peer-checked:ring-[#FFB800]">
                                   <span
                                       class="text-sm font-medium text-gray-900 peer-checked:text-[#0B1F51]">Student</span>
                               </label>
                           </div>
                           <div class="relative">
                               <input class="peer hidden" id="teacher" name="accountType" type="radio" value="teacher"
                                   <?php echo (isset($form_data['accountType']) && $form_data['accountType'] == 'teacher') ? 'checked' : ''; ?>>
                               <label for="teacher"
                                   class="block cursor-pointer rounded-lg border border-gray-300 bg-white py-2 px-3 text-center hover:bg-gray-50 focus:outline-none peer-checked:border-[#FFB800] peer-checked:bg-[#FFB800] peer-checked:text-[#0B1F51] peer-checked:ring-1 peer-checked:ring-[#FFB800]">
                                   <span
                                       class="text-sm font-medium text-gray-900 peer-checked:text-[#0B1F51]">Teacher</span>
                               </label>
                           </div>
                       </div>
                   </div>

                   <div>
                       <label id="loginLabel" for="loginInput" class="block text-sm font-medium text-gray-700">School
                           ID</label>
                       <input id="loginInput" name="loginInput" type="text" required
                           value="<?php echo htmlspecialchars($form_data['loginInput'] ?? ''); ?>"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-900 focus:border-blue-900 focus:z-10 sm:text-sm"
                           placeholder="Enter your School ID">
                   </div>

                   <div>
                       <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                       <input id="password" name="password" type="password" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-900 focus:border-blue-900 focus:z-10 sm:text-sm"
                           placeholder="Enter your password">
                   </div>
               </div>

               <div class="flex items-center justify-between">
                   <div class="flex items-center">
                       <input id="remember-me" name="remember-me" type="checkbox"
                           class="h-4 w-4 text-[#0B1F51] focus:ring-[#0B1F51] border-gray-300 rounded">
                       <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                           Remember me
                       </label>
                   </div>

                   <div class="text-sm">
                       <a href="#" class="font-medium text-[#0B1F51] hover:text-[#0a1b45]">
                           Forgot your password?
                       </a>
                   </div>
               </div>

               <div>
                   <button type="submit"
                       class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-[#0B1F51] hover:bg-[#0a1b45] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0B1F51]">
                       <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                           <i class="fas fa-sign-in-alt"></i>
                       </span>
                       Sign in
                   </button>
               </div>
           </form>
       </div>
   </div>
   <script>
document.querySelectorAll('input[name="accountType"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const loginLabel = document.getElementById('loginLabel');
        const loginInput = document.getElementById('loginInput');

        if (this.value === 'student') {
            loginLabel.textContent = 'School ID';
            loginInput.placeholder = 'Enter your School ID';
            loginInput.type = 'text';
        } else { // teacher
            loginLabel.textContent = 'Teacher Email';
            loginInput.placeholder = 'Enter your email address';
            loginInput.type = 'email';
        }
    });
});
document.querySelector('input[name="accountType"]:checked').dispatchEvent(new Event('change'));
   </script>

   <?php include 'includes/footer.php'; ?>