 <?php 
    $page_title = "Crown Institute - From Possibility to Actuality";
    include 'includes/header.php'; 
    ?>

 <div class="relative bg-[#0B1F51] overflow-hidden">
     <div class="max-w-7xl mx-auto">
         <div class="relative z-10 pb-8 bg-[#0B1F51] sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
             <div class="pt-10 sm:pt-16 lg:pt-8 lg:pb-14 lg:overflow-hidden">
                 <div class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                     <div class="sm:text-center lg:text-left">
                         <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl">
                             <span class="block">Welcome to</span>
                             <span class="block text-[#FFB800]">Crown Institute</span>
                         </h1>
                         <p
                             class="mt-3 text-base text-blue-100 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                             Your gateway to quality education. Join thousands of students learning from top educators
                             worldwide.
                         </p>
                         <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                             <div class="rounded-md shadow">
                                 <a href="<?php echo $base_url; ?>signup.php"
                                     class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-[#0B1F51] bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                                     Get Started
                                 </a>
                             </div>
                             <div class="mt-3 sm:mt-0 sm:ml-3">
                                 <a href="<?php echo $base_url; ?>courses.php"
                                     class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-[#0B1F51] bg-[#FFB800] hover:bg-[#e5a600] md:py-4 md:text-lg md:px-10">
                                     Browse Courses
                                 </a>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
     <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
         <img class="h-56 w-full object-cover sm:h-72 md:h-96 lg:w-full lg:h-full"
             src="https://images.pexels.com/photos/4145153/pexels-photo-4145153.jpeg" alt="Students learning">
     </div>
 </div>

 <?php include 'includes/footer.php'; ?>