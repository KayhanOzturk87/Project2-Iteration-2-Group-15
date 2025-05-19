<?php 
$page_title = "Sign Up - Crown Institute";
if (empty($base_url)) $base_url = '/'; 
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
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <img src="<?php echo $base_url; ?>pictures/Crown.jpg" alt="Crown Institute Logo" class="h-24 w-24">
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Already have an account? <a href="<?php echo $base_url; ?>login.php"
                    class="font-medium text-blue-900 hover:text-blue-800">Sign in</a>
            </p>
        </div>

        <?php if (!empty($form_errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Oops!</strong>
            <ul class="mt-2 list-disc list-inside">
                <?php foreach ($form_errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="<?php echo $base_url; ?>actions/signup_action.php" method="POST">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="fullname" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input id="fullname" name="fullname" type="text" required
                        value="<?php echo htmlspecialchars($form_data['fullname'] ?? ''); ?>"
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-900 focus:border-blue-900 focus:z-10 sm:text-sm"
                        placeholder="John Doe">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Account Type</label>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        <div class="relative">
                            <input class="peer hidden" id="studentRadio" name="accountType" type="radio" value="student"
                                <?php echo (!isset($form_data['accountType']) || $form_data['accountType'] == 'student') ? 'checked' : ''; ?>>
                            <label for="studentRadio"
                                class="block cursor-pointer rounded-lg border border-gray-300 bg-white py-2 px-3 text-center hover:bg-gray-50 focus:outline-none peer-checked:border-[#FFB800] peer-checked:bg-[#FFB800] peer-checked:text-[#0B1F51] peer-checked:ring-1 peer-checked:ring-[#FFB800]">
                                <span
                                    class="text-sm font-medium text-gray-900 peer-checked:text-[#0B1F51]">Student</span>
                            </label>
                        </div>
                        <div class="relative">
                            <input class="peer hidden" id="teacherRadio" name="accountType" type="radio" value="teacher"
                                <?php echo (isset($form_data['accountType']) && $form_data['accountType'] == 'teacher') ? 'checked' : ''; ?>>
                            <label for="teacherRadio"
                                class="block cursor-pointer rounded-lg border border-gray-300 bg-white py-2 px-3 text-center hover:bg-gray-50 focus:outline-none peer-checked:border-[#FFB800] peer-checked:bg-[#FFB800] peer-checked:text-[#0B1F51] peer-checked:ring-1 peer-checked:ring-[#FFB800]">
                                <span
                                    class="text-sm font-medium text-gray-900 peer-checked:text-[#0B1F51]">Teacher</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input id="email" name="email" type="email" required
                        value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-900 focus:border-blue-900 focus:z-10 sm:text-sm"
                        placeholder="you@example.com">
                </div>

                <div id="schoolIdContainer" class="hidden">
                    <label for="school_id" class="block text-sm font-medium text-gray-700">School ID</label>
                    <input id="school_id" name="school_id" type="text"
                        value="<?php echo htmlspecialchars($form_data['school_id'] ?? ''); ?>"
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-900 focus:border-blue-900 focus:z-10 sm:text-sm"
                        placeholder="Enter your School ID">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" required
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-900 focus:border-blue-900 focus:z-10 sm:text-sm"
                        placeholder="Min. 6 characters">
                </div>
                <div>
                    <label for="confirm-password" class="block text-sm font-medium text-gray-700">Confirm
                        Password</label>
                    <input id="confirm-password" name="confirm-password" type="password" required
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-900 focus:border-blue-900 focus:z-10 sm:text-sm"
                        placeholder="Re-enter password">
                </div>
            </div>

            <div class="flex items-center">
                <input id="terms" name="terms" type="checkbox" required
                    class="h-4 w-4 text-[#0B1F51] focus:ring-[#0B1F51] border-gray-300 rounded">
                <label for="terms" class="ml-2 block text-sm text-gray-900">
                    I agree to the <a href="#" class="text-[#0B1F51] hover:text-[#0a1b45]">Terms of Service</a> and <a
                        href="#" class="text-[#0B1F51] hover:text-[#0a1b45]">Privacy Policy</a>
                </label>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-[#0B1F51] hover:bg-[#0a1b45] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0B1F51]">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-user-plus"></i>
                    </span>
                    Create Account
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const studentRadio = document.getElementById('studentRadio');
const teacherRadio = document.getElementById('teacherRadio');
const schoolIdContainer = document.getElementById('schoolIdContainer');
const schoolIdInput = document.getElementById('school_id');

function toggleSchoolIdField() {
    if (studentRadio.checked) {
        schoolIdContainer.classList.remove('hidden');
        schoolIdInput.required = true;
    } else {
        schoolIdContainer.classList.add('hidden');
        schoolIdInput.required = false;

    }
}

studentRadio.addEventListener('change', toggleSchoolIdField);
teacherRadio.addEventListener('change', toggleSchoolIdField);

toggleSchoolIdField();
</script>

<?php include 'includes/footer.php'; ?>