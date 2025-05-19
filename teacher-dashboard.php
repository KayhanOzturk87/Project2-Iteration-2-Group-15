<?php
    $page_title = "Teacher Dashboard - Crown Institute";
 
    if (empty($base_url)) $base_url = '/'; 
    include 'includes/header.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
        header("Location: " . $base_url . "login.php?error=unauthorized");
        exit();
    }
    require_once __DIR__ . '/config/db_connect.php'; // DB connection

    $teacher_id = $_SESSION['user_id'];
    $courses = [];
    try {
        $stmt = $pdo->prepare("SELECT c.*, COUNT(e.enrollment_id) as student_count 
                               FROM Courses c 
                               LEFT JOIN Enrollments e ON c.course_id = e.course_id
                               WHERE c.instructor_id = ?
                               GROUP BY c.course_id
                               ORDER BY c.created_at DESC");
        $stmt->execute([$teacher_id]);
        $courses = $stmt->fetchAll();
    } catch (PDOException $e) {
        $dashboard_error = "Error fetching courses: " . $e->getMessage();
    }

    $success_message = $_SESSION['success_message'] ?? null;
    $error_message = $_SESSION['error_message'] ?? null;
    unset($_SESSION['success_message'], $_SESSION['error_message']);
    ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Course Management</h1>
        <button onclick="openAddCourseModal()"
            class="bg-[#FFB800] hover:bg-[#e5a600] text-[#0B1F51] font-medium py-2 px-4 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i> Add New Course
        </button>
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
    <?php if (isset($dashboard_error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <?php echo htmlspecialchars($dashboard_error); ?>
    </div>
    <?php endif; ?>


    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Students</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Units
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="courseTableBody">
                    <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">You have
                            not created any courses yet.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full object-cover"
                                        src="<?php echo !empty($course['image_url']) ? (filter_var($course['image_url'], FILTER_VALIDATE_URL) ? $course['image_url'] : $base_url . 'uploads/' . htmlspecialchars($course['image_url'])) : 'https://images.pexels.com/photos/4145153/pexels-photo-4145153.jpeg'; ?>"
                                        alt="<?php echo htmlspecialchars($course['title']); ?>">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($course['title']); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo substr(htmlspecialchars($course['description'] ?? ''), 0, 50); ?>...
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-[#FFB800] text-[#0B1F51]">
                                <?php echo htmlspecialchars(ucfirst($course['category'])); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($course['student_count']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php 
                                        $unit_titles = json_decode($course['units_titles_json'] ?? '[]', true);
                                        echo count($unit_titles);
                                        ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?php echo $base_url; ?>unit-page.php?course_id=<?php echo $course['course_id']; ?>"
                                target="_blank" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                            <button onclick="editCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)"
                                class="text-[#0B1F51] hover:text-[#0a1b45] mr-3">Edit</button>
                            <form action="<?php echo $base_url; ?>actions/delete_course_action.php" method="POST"
                                class="inline-block"
                                onsubmit="return confirm('Are you sure you want to delete this course and its quiz? This action cannot be undone.');">
                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="courseModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form id="courseForm" action="<?php echo $base_url; ?>actions/add_edit_course_action.php" method="POST"
                enctype="multipart/form-data">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitleText">Add New Course</h3>
                    <input type="hidden" id="courseId" name="course_id" value="">
                    <input type="hidden" id="quizId" name="quiz_id" value="">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="courseTitle" class="block text-sm font-medium text-gray-700">Course
                                Title</label>
                            <input type="text" id="courseTitle" name="courseTitle" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#0B1F51] focus:border-[#0B1F51] sm:text-sm">
                        </div>
                        <div>
                            <label for="courseCategory" class="block text-sm font-medium text-gray-700">Category</label>
                            <select id="courseCategory" name="courseCategory"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#0B1F51] focus:border-[#0B1F51] sm:text-sm rounded-md">
                                <option value="programming">Programming</option>
                                <option value="design">Design</option>
                                <option value="business">Business</option>
                                <option value="science">Science</option>
                                <option value="mathematics">Mathematics</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="courseDescription"
                            class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="courseDescription" name="courseDescription" rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#0B1F51] focus:border-[#0B1F51] sm:text-sm"></textarea>
                    </div>
                    <div class="mt-4">
                        <label for="courseImage" class="block text-sm font-medium text-gray-700">Course Image (Optional,
                            replaces current if new one uploaded)</label>
                        <input type="file" id="courseImage" name="courseImage" accept="image/jpeg, image/png, image/gif"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#FFB800] file:text-[#0B1F51] hover:file:bg-[#e5a600]">
                        <input type="hidden" name="existing_image_url" id="existingImageUrl">
                        <div id="currentImagePreview" class="mt-2"></div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Course Unit Titles</label>
                        <div id="unitsListContainer" class="space-y-2 mt-2">
                        </div>
                        <button type="button" onclick="addUnitField()"
                            class="mt-2 inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-[#0B1F51] bg-[#FFB800] hover:bg-[#e5a600] focus:outline-none">
                            <i class="fas fa-plus mr-1"></i> Add Unit Title
                        </button>
                    </div>

                    <div class="mt-6 border-t pt-4">
                        <label class="block text-sm font-medium text-gray-700">Course Quiz (One Quiz per Course)</label>
                        <div class="mt-2">
                            <label for="quizTitle" class="block text-sm font-medium text-gray-700">Quiz Title</label>
                            <input type="text" id="quizTitle" name="quiz_title" required
                                placeholder="E.g., Mid-term Quiz for [Course Title]"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#0B1F51] focus:border-[#0B1F51] sm:text-sm">
                        </div>
                        <div id="quizQuestionsContainer" class="space-y-4 mt-2">
                        </div>
                        <button type="button" onclick="addQuizQuestionField()"
                            class="mt-2 inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-[#0B1F51] bg-[#FFB800] hover:bg-[#e5a600] focus:outline-none">
                            <i class="fas fa-plus mr-1"></i> Add Quiz Question
                        </button>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#0B1F51] text-base font-medium text-white hover:bg-[#0a1b45] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0B1F51] sm:ml-3 sm:w-auto sm:text-sm">
                        Save Course
                    </button>
                    <button type="button" onclick="closeCourseModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0B1F51] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const base_url = '<?php echo $base_url; ?>';

function openAddCourseModal() {
    document.getElementById('courseForm').reset();
    document.getElementById('courseId').value = '';
    document.getElementById('quizId').value = '';
    document.getElementById('modalTitleText').textContent = 'Add New Course';
    document.getElementById('unitsListContainer').innerHTML = '';
    document.getElementById('quizQuestionsContainer').innerHTML = '';
    document.getElementById('currentImagePreview').innerHTML = '';
    document.getElementById('existingImageUrl').value = '';
    addQuizQuestionField();
    document.getElementById('courseModal').classList.remove('hidden');
}

function closeCourseModal() {
    document.getElementById('courseModal').classList.add('hidden');
}

function editCourse(courseData) {
    document.getElementById('courseForm').reset();
    document.getElementById('modalTitleText').textContent = 'Edit Course';

    document.getElementById('courseId').value = courseData.course_id;
    document.getElementById('courseTitle').value = courseData.title;
    document.getElementById('courseCategory').value = courseData.category;
    document.getElementById('courseDescription').value = courseData.description || '';

    const currentImagePreview = document.getElementById('currentImagePreview');
    currentImagePreview.innerHTML = '';
    if (courseData.image_url) {
        document.getElementById('existingImageUrl').value = courseData.image_url;
        const img_src = courseData.image_url.startsWith('http') ? courseData.image_url : base_url + 'uploads/' +
            courseData.image_url;
        currentImagePreview.innerHTML =
            `<p class="text-xs text-gray-500 mt-1">Current Image:</p><img src="${img_src}" alt="Current Image" class="h-20 w-auto rounded mt-1">`;
    } else {
        document.getElementById('existingImageUrl').value = '';
    }


    // Populate units
    const unitsContainer = document.getElementById('unitsListContainer');
    unitsContainer.innerHTML = '';
    const unitTitles = JSON.parse(courseData.units_titles_json || '[]');
    if (unitTitles.length > 0) {
        unitTitles.forEach(title => addUnitField(title));
    } else {
        addUnitField();
    }

    fetchQuizForCourse(courseData.course_id);


    document.getElementById('courseModal').classList.remove('hidden');
}

async function fetchQuizForCourse(courseId) {
    const quizContainer = document.getElementById('quizQuestionsContainer');
    quizContainer.innerHTML = '';
    document.getElementById('quizTitle').value = '';
    document.getElementById('quizId').value = '';

    try {

        const response = await fetch(
            `<?php echo $base_url; ?>actions/get_quiz_details_action.php?course_id=${courseId}`);
        if (!response.ok) {
            console.error('Failed to fetch quiz details');
            addQuizQuestionField();
            return;
        }
        const quizData = await response.json();

        if (quizData && quizData.quiz_id) {
            document.getElementById('quizId').value = quizData.quiz_id;
            document.getElementById('quizTitle').value = quizData.title;
            const questions = JSON.parse(quizData.questions_json || '[]');
            if (questions.length > 0) {
                questions.forEach(q => addQuizQuestionField(q.q, q.options, q.answer));
            } else {
                addQuizQuestionField();
            }
        } else {
            addQuizQuestionField();
        }
    } catch (error) {
        console.error("Error fetching quiz:", error);
        addQuizQuestionField();
    }
}


function addUnitField(value = '') {
    const container = document.getElementById('unitsListContainer');
    const unitDiv = document.createElement('div');
    unitDiv.className = 'flex items-center space-x-2 mb-2';
    unitDiv.innerHTML = `
                <input type="text" name="unit_titles[]" placeholder="Unit Title (e.g., Week 1: Introduction)" value="${value}"
                       class="flex-1 border border-gray-300 rounded-md shadow-sm py-1 px-2 text-sm focus:outline-none focus:ring-[#0B1F51] focus:border-[#0B1F51]">
                <button type="button" onclick="this.parentElement.remove()" 
                        class="text-red-500 hover:text-red-700 p-1 rounded-full"><i class="fas fa-times-circle"></i></button>
            `;
    container.appendChild(unitDiv);
}

let questionCounter = 0;

function addQuizQuestionField(questionText = '', options = ['', ''], correctAnswerIndex = 0) {
    questionCounter++;
    const container = document.getElementById('quizQuestionsContainer');
    const questionDiv = document.createElement('div');
    questionDiv.className = 'p-3 border rounded-md bg-gray-50 mb-3';
    questionDiv.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700">Question ${questionCounter}</label>
                    <button type="button" onclick="this.closest('.border').remove(); questionCounter--;" class="text-red-500 hover:text-red-700"><i class="fas fa-trash-alt"></i></button>
                </div>
                <input type="text" name="quiz_questions[${questionCounter-1}][q]" placeholder="Question text" value="${questionText}" required class="w-full mb-2 p-1 border rounded">
                <label class="block text-xs font-medium text-gray-600">Options (Mark correct one):</label>
                ${[0,1,2,3].map(optIndex => `
                    <div class="flex items-center my-1">
                        <input type="radio" name="quiz_questions[${questionCounter-1}][answer]" value="${optIndex}" ${optIndex == correctAnswerIndex ? 'checked' : ''} required class="mr-2 h-4 w-4 text-[#0B1F51] focus:ring-[#0B1F51]">
                        <input type="text" name="quiz_questions[${questionCounter-1}][options][]" placeholder="Option ${optIndex+1}" value="${options[optIndex] || ''}" required class="flex-1 p-1 border rounded text-sm">
                    </div>
                `).join('')}
            `;
    container.appendChild(questionDiv);
}
</script>

<?php include 'includes/footer.php'; ?>