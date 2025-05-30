    <?php
    $page_title = "Available Courses - Crown Institute";
    if (empty($base_url)) $base_url = '/';
    include 'includes/header.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $base_url . "login.php?error=auth_required");
        exit();
    }
    require_once __DIR__ . '/config/db_connect.php';

    $all_courses = [];
    $enrolled_course_ids = [];
    $filter_category = $_GET['category'] ?? 'all';

    try {
        $sql = "SELECT c.*, u.name as instructor_name 
                FROM Courses c 
                JOIN Users u ON c.instructor_id = u.user_id";
        $params = [];
        if ($filter_category !== 'all') {
            $sql .= " WHERE c.category = ?";
            $params[] = $filter_category;
        }
        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $all_courses = $stmt->fetchAll();

        if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student') {
            $stmt_enrolled = $pdo->prepare("SELECT course_id FROM Enrollments WHERE user_id = ?");
            $stmt_enrolled->execute([$_SESSION['user_id']]);
            $enrolled_course_ids = $stmt_enrolled->fetchAll(PDO::FETCH_COLUMN);
        }

    } catch (PDOException $e) {
        $page_error = "Error fetching courses: " . $e->getMessage();
    }
    
    $success_message = $_SESSION['success_message'] ?? null;
    $error_message = $_SESSION['error_message'] ?? null;
    unset($_SESSION['success_message'], $_SESSION['error_message']);

    $categories_stmt = $pdo->query("SELECT DISTINCT category FROM Courses ORDER BY category");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

    ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Available Courses</h1>

            <form method="GET" action="<?php echo $base_url; ?>courses.php"
                class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 w-full md:w-auto">
                <div class="relative">
                    <select id="categoryFilter" name="category"
                        class="appearance-none bg-white border border-gray-300 rounded-md py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"
                            <?php echo ($filter_category == $cat) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($cat)); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <button type="submit"
                    class="bg-[#0B1F51] hover:bg-[#0a1b45] text-white font-medium py-2 px-4 rounded-md text-sm">
                    Apply Filters
                </button>
            </form>
        </div>

        <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>
        <?php if (isset($page_error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?php echo htmlspecialchars($page_error); ?>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="courseGrid">
            <?php if (empty($all_courses)): ?>
            <p class="text-gray-600 col-span-full text-center">No courses found matching your criteria.</p>
            <?php else: ?>
            <?php foreach ($all_courses as $course): 
                    $is_enrolled = in_array($course['course_id'], $enrolled_course_ids);
                    $rating_placeholder = 4.0 + (rand(0,10)/10); 
                    $students_placeholder = rand(20, 200); 
                    
                    $stmt_enroll_count = $pdo->prepare("SELECT COUNT(*) as count FROM Enrollments WHERE course_id = ?");
                    $stmt_enroll_count->execute([$course['course_id']]);
                    $enroll_count_data = $stmt_enroll_count->fetch();
                    $actual_students = $enroll_count_data['count'];
                ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="h-48 overflow-hidden">
                    <img class="w-full h-full object-cover"
                        src="<?php echo !empty($course['image_url']) ? (filter_var($course['image_url'], FILTER_VALIDATE_URL) ? $course['image_url'] : $base_url . 'uploads/' . htmlspecialchars($course['image_url'])) : 'https://images.pexels.com/photos/546819/pexels-photo-546819.jpeg'; ?>"
                        alt="<?php echo htmlspecialchars($course['title']); ?>">
                </div>
                <div class="p-6">
                    <div>
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                                <?php 
                                switch ($course['category']) {
                                    case 'information technology': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'accounting': echo 'bg-purple-100 text-purple-800'; break;
                                    case 'business': echo 'bg-green-100 text-green-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                                ?>
                            ">
                            <?php echo htmlspecialchars(ucfirst($course['category'])); ?>
                        </span>
                        <h3 class="mt-2 text-lg font-semibold text-gray-900">
                            <?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="text-xs text-gray-500">By: <?php echo htmlspecialchars($course['instructor_name']); ?>
                        </p>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 h-16 overflow-hidden">
                        <?php echo substr(htmlspecialchars($course['description'] ?? ''), 0, 100); ?>...</p>
                    <div class="mt-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex items-center">
                                <?php for($i=0; $i<5; $i++): ?>
                                <i
                                    class="fas fa-star <?php echo ($i < floor($rating_placeholder)) ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="ml-2 text-sm text-gray-600"><?php echo number_format($rating_placeholder,1); ?>
                                (<?php echo $actual_students; ?> students)</span>
                        </div>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student'): ?>
                        <?php if ($is_enrolled): ?>
                        <a href="<?php echo $base_url; ?>unit-page.php?course_id=<?php echo $course['course_id']; ?>"
                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                            View Course
                        </a>
                        <?php else: ?>
                        <button
                            onclick="openEnrollmentModal(<?php echo $course['course_id']; ?>, '<?php echo htmlspecialchars(addslashes($course['title'])); ?>')"
                            class="px-4 py-2 bg-[#0B1F51] text-white text-sm font-medium rounded-md hover:bg-[#0a1b45]">
                            Enroll Now
                        </button>
                        <?php endif; ?>
                        <?php elseif (!isset($_SESSION['user_id'])): // Not logged in ?>
                        <a href="<?php echo $base_url; ?>login.php?redirect=courses.php"
                            class="px-4 py-2 bg-[#0B1F51] text-white text-sm font-medium rounded-md hover:bg-[#0a1b45]">
                            Login to Enroll
                        </a>
                        <?php endif; // Teachers don't see enroll button on this page ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student'): ?>
    <div id="enrollmentModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="enrollForm" action="<?php echo $base_url; ?>actions/enroll_action.php" method="POST">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2" id="modalTitle">Enroll in Course
                        </h3>
                        <p class="text-sm text-gray-600 mb-4" id="modalDescription">You are about to enroll in this
                            course. Confirm to proceed.</p>
                        <input type="hidden" name="course_id_to_enroll" id="courseIdToEnroll">
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#0B1F51] text-base font-medium text-white hover:bg-[#0a1b45] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0B1F51] sm:ml-3 sm:w-auto sm:text-sm">
                            Confirm Enrollment
                        </button>
                        <button type="button" onclick="closeEnrollmentModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0B1F51] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
function openEnrollmentModal(courseId, courseTitle) {
    document.getElementById('courseIdToEnroll').value = courseId;
    document.getElementById('modalTitle').textContent = `Enroll in ${courseTitle}`;
    document.getElementById('modalDescription').textContent =
        `You are about to enroll in "${courseTitle}". Confirm to proceed.`;
    document.getElementById('enrollmentModal').classList.remove('hidden');
}

function closeEnrollmentModal() {
    document.getElementById('enrollmentModal').classList.add('hidden');
}
    </script>
    <?php endif; ?>


    <?php include 'includes/footer.php'; ?>