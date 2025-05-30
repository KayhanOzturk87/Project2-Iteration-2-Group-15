<?php
// $page_title should ideally be set before including header.php to affect the <title> tag.
// We'll set a default here and try to update the main heading dynamically.
$page_title_default = "Course Details - Crown Institute";

if (empty($base_url)) $base_url = '/'; // Ensure $base_url is correct
include 'includes/header.php'; // This starts session and includes common head elements

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "login.php?error=auth_required");
    exit();
}
require_once __DIR__ . '/config/db_connect.php';

$page_specific_title = $page_title_default; // Initialize with default

if (!isset($_GET['course_id'])) {
    $_SESSION['error_message'] = "Course ID not specified.";
    header("Location: " . $base_url . ($_SESSION['user_role'] === 'student' ? "student-dashboard.php" : "teacher-dashboard.php"));
    exit();
}

$course_id = $_GET['course_id'];
$course = null;
$is_student_enrolled = false;
$unit_titles = [];
$course_materials = [];

try {
    $stmt = $pdo->prepare("SELECT c.*, u.name as instructor_name
                           FROM Courses c
                           LEFT JOIN Users u ON c.instructor_id = u.user_id
                           WHERE c.course_id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();

    if (!$course) {
        $_SESSION['error_message'] = "Course not found.";
        header("Location: " . $base_url . "courses.php");
        exit();
    }

   
    $page_specific_title = htmlspecialchars($course['title']) . " - Unit Page";


    // Check if student is enrolled (if logged in as student)
    if ($_SESSION['user_role'] === 'student') {
        $stmt_enroll = $pdo->prepare("SELECT 1 FROM Enrollments WHERE user_id = ? AND course_id = ?");
        $stmt_enroll->execute([$_SESSION['user_id'], $course_id]);
        if ($stmt_enroll->fetch()) {
            $is_student_enrolled = true;
        }
    }

    // Fetch Unit Titles
    if (isset($course['units_titles_json'])) {
        $decoded_units = json_decode($course['units_titles_json'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_units)) {
            $unit_titles = $decoded_units;
        } else {
            error_log("JSON decode error for units_titles_json, course_id: " . $course_id);
        }
    }

    // Fetch Course Materials
    $stmt_materials = $pdo->prepare("SELECT material_id, file_name, file_path, title, description, upload_date
                                     FROM CourseMaterials
                                     WHERE course_id = ? ORDER BY upload_date DESC");
    $stmt_materials->execute([$course_id]);
    $course_materials = $stmt_materials->fetchAll();


} catch (PDOException $e) {
    $page_error = "Error fetching course details: " . $e->getMessage();
}

?>
<!-- Top Navigation for Course Specific Links -->
<nav class="bg-[#0B1F51] text-white shadow-lg">
    <div class="max-w-full px-4 mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-center h-auto sm:h-16 py-2 sm:py-0">
            <div class="flex items-center space-x-8 mb-2 sm:mb-0">
                <a href="#" class="text-xl font-semibold truncate"
                    title="<?php echo htmlspecialchars($course['title'] ?? 'Course'); ?>">
                    <?php echo htmlspecialchars($course['title'] ?? 'Course'); ?>
                </a>
            </div>
            <div class="flex flex-wrap justify-center sm:justify-start space-x-2 sm:space-x-4">
                <a href="<?php echo $base_url . ($_SESSION['user_role'] === 'student' ? 'student-dashboard.php' : 'teacher-dashboard.php'); ?>"
                    class="px-3 py-2 text-white hover:text-[#FFB800]">Dashboard</a>
                <?php if ($_SESSION['user_role'] === 'student' && $is_student_enrolled && $course): ?>
                <a href="<?php echo $base_url; ?>quiz.php?course_id=<?php echo $course['course_id']; ?>"
                    class="px-3 py-2 text-white hover:text-[#FFB800]">Take Quiz</a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex items-center space-x-4 mt-2 sm:mt-0">
                <span class="text-sm text-gray-300">Welcome,
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="<?php echo $base_url; ?>actions/logout_action.php" class="text-white hover:text-[#FFB800]"
                    title="Logout">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="flex flex-col md:flex-row min-h-[calc(100vh-4rem)]">
    <!-- Adjusted min-height -->
    <!-- Left Sidebar for Units -->
    <div
        class="w-full md:w-64 bg-[#0B1F51] text-white flex-shrink-0 md:sticky md:top-16 md:self-start md:max-h-[calc(100vh-4rem)] md:overflow-y-auto">
        <div class="p-4">
            <h3 class="text-lg font-semibold mb-3 text-[#FFB800]">Course Units</h3>
            <div id="sidebar" class="space-y-1">
                <?php if (empty($unit_titles)): ?>
                <p class="px-4 py-2 text-gray-400">No units defined for this course yet.</p>
                <?php else: ?>
                <?php foreach ($unit_titles as $index => $unit_title):
                    $unit_placeholder_content = "Placeholder content for " . htmlspecialchars($unit_title) . ". This section would typically contain detailed text, videos, or resources related to the unit topic. For now, it is a simple demonstration.";
                ?>
                <a href="#unit-<?php echo $index + 1; ?>"
                    onclick="displayUnitContent(event, <?php echo $index; ?>, '<?php echo htmlspecialchars(addslashes($unit_title)); ?>', '<?php echo htmlspecialchars(addslashes($unit_placeholder_content)); ?>')"
                    class="block px-4 py-2 rounded hover:bg-[#FFB800] hover:text-[#0B1F51] transition-colors truncate"
                    title="<?php echo htmlspecialchars($unit_title); ?>">
                    <?php echo ($index + 1) . ". " . htmlspecialchars($unit_title); ?>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 p-6 sm:p-8 bg-gray-50">
        <?php if (isset($page_error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?php echo htmlspecialchars($page_error); ?>
        </div>
        <?php elseif ($course): ?>
        <div class="max-w-4xl mx-auto">
            <h1 id="mainContentTitle" class="text-3xl font-bold text-[#0B1F51] mb-3">
                <?php echo htmlspecialchars($course['title']); ?></h1>
            <p class="text-sm text-gray-500 mb-6">Instructor:
                <?php echo htmlspecialchars($course['instructor_name'] ?? 'N/A'); ?></p>

            <div id="mainCourseContent" class="bg-white p-6 rounded-lg shadow min-h-[200px] mb-8">
                <h2 class="text-2xl font-semibold text-[#0B1F51] mb-4">Course Overview</h2>
                <div class="prose max-w-none text-gray-700">
                    <?php echo nl2br(htmlspecialchars($course['description'] ?? 'No detailed description provided.')); ?>
                </div>
            </div>

            <!-- Display Course Materials -->
            <?php if ($is_student_enrolled || $_SESSION['user_role'] === 'teacher'): // Show materials if student is enrolled OR if user is a teacher ?>
            <?php if (!empty($course_materials)): ?>
            <div class="mt-8 bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold text-[#0B1F51] mb-4">Course Materials</h3>
                <ul class="list-none p-0 space-y-3">
                    <?php foreach ($course_materials as $material): ?>
                    <li class="border p-3 rounded-md hover:shadow-sm transition-shadow bg-gray-50">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <div class="flex-grow mb-2 sm:mb-0 sm:mr-4">
                                <strong class="text-gray-800 block truncate"
                                    title="<?php echo htmlspecialchars($material['title'] ?: $material['file_name']); ?>">
                                    <?php echo htmlspecialchars($material['title'] ?: $material['file_name']); ?>
                                </strong>
                                <?php if ($material['description']): ?>
                                <p class="text-sm text-gray-600 mt-1 truncate"
                                    title="<?php echo htmlspecialchars($material['description']); ?>">
                                    <?php echo htmlspecialchars($material['description']); ?></p>
                                <?php endif; ?>
                                <p class="text-xs text-gray-400">Uploaded:
                                    <?php echo date("M j, Y", strtotime($material['upload_date'])); ?></p>
                            </div>
                            <a href="<?php echo $base_url; ?>actions/download_material.php?material_id=<?php echo $material['material_id']; ?>"
                                class="w-full sm:w-auto flex-shrink-0 px-4 py-2 bg-[#FFB800] text-[#0B1F51] text-sm font-semibold rounded-md hover:bg-[#e5a600] transition text-center"
                                download="<?php echo htmlspecialchars($material['file_name']); ?>">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php elseif ($_SESSION['user_role'] === 'student'): // Enrolled student but no materials ?>
            <div class="mt-8 bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold text-[#0B1F51] mb-4">Course Materials</h3>
                <p class="text-gray-600">No materials have been uploaded for this course yet.</p>
            </div>
            <?php endif; ?>
            <?php elseif ($_SESSION['user_role'] === 'student' && !$is_student_enrolled): // Student not enrolled ?>
            <div class="mt-8 bg-white p-6 rounded-lg shadow">
                <p class="text-gray-700">You must be enrolled in this course to view its materials.
                    <a href="<?php echo $base_url; ?>courses.php?category=<?php echo urlencode($course['category'] ?? ''); ?>#course-<?php echo $course['course_id']; ?>"
                        class="text-blue-600 hover:underline">Enroll Now</a>
                </p>
            </div>
            <?php endif; ?>

        </div>
        <?php else: ?>
        <p class="text-gray-600">Course details could not be loaded.</p>
        <?php endif; ?>
    </div>
</div>

<!-- AI Chatbot HTML Structure -->
<div class="fixed bottom-4 right-4 z-50">
    <div id="chatbot" class="bg-white rounded-lg shadow-xl w-80 sm:w-96 hidden">
        <div class="bg-[#0B1F51] text-white p-3 sm:p-4 rounded-t-lg flex justify-between items-center">
            <h3 class="font-semibold text-sm sm:text-base">Learning Assistant</h3>
            <button onclick="toggleChatbot()" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="chatMessages" class="h-72 sm:h-96 overflow-y-auto p-4 space-y-2 sm:space-y-4">
        </div>
        <div class="p-3 sm:p-4 border-t">
            <div class="flex space-x-2">
                <input type="text" id="chatInput" placeholder="Ask a question..."
                    class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FFB800] text-sm">
                <button onclick="sendMessageToBackend()"
                    class="px-4 py-2 bg-[#0B1F51] text-white rounded-lg hover:bg-[#0a1b45] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#FFB800]">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
    <button onclick="toggleChatbot()"
        class="bg-[#FFB800] text-[#0B1F51] p-3 sm:p-4 rounded-full shadow-lg hover:bg-[#e5a600] float-right focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0B1F51]"
        id="chatbotToggle">
        <i class="fas fa-comment-dots text-xl sm:text-2xl"></i>
    </button>
</div>


<script>
const base_url_js = '<?php echo $base_url; ?>';

// Unit Content Display Logic
function displayUnitContent(event, index, title, content) {
    event.preventDefault();
    const mainContentArea = document.getElementById('mainCourseContent');
    const mainContentTitle = document.getElementById('mainContentTitle');

    mainContentTitle.textContent = title;
    mainContentArea.innerHTML = `
            <h2 class="text-2xl font-semibold text-[#0B1F51] mb-4">${escapeHtml(title)}</h2>
            <div class="prose max-w-none text-gray-700">${content.replace(/\n/g, '<br>')}</div>
            <hr class="my-6">
            <p class="text-sm text-gray-500">This is placeholder content for Unit ${index + 1}. Real content would be loaded here.</p>
        `;

    document.querySelectorAll('#sidebar a').forEach(link => link.classList.remove('bg-[#FFB800]', 'text-[#0B1F51]'));
    const activeLink = event.currentTarget;
    if (activeLink) {
        activeLink.classList.add('bg-[#FFB800]', 'text-[#0B1F51]');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Attempt to display first unit or course overview
    const firstUnitLink = document.querySelector('#sidebar a');
    if (firstUnitLink && firstUnitLink.hasAttribute('onclick')) {
        // A more robust way to trigger the first unit display
        const onclickAttr = firstUnitLink.getAttribute('onclick');
        try {
            // This is a simplified way to extract params, might need refinement
            const params = onclickAttr.match(/displayUnitContent\((?:event,\s*)?(\d+),\s*'(.*?)',\s*'(.*?)'\)/);
            if (params && params.length === 4) {
                const index = parseInt(params[1]);
                const title = params[2].replace(/\\'/g, "'");
                const content = params[3].replace(/\\'/g, "'");
                // Call with a mock event if your function expects it
                displayUnitContent({
                    preventDefault: () => {}
                }, index, title, content);
            } else {
                loadCourseOverview(); // Fallback if parsing fails
            }
        } catch (e) {
            console.error("Error auto-displaying first unit:", e);
            loadCourseOverview(); // Fallback
        }
    } else {
        loadCourseOverview(); // If no units, or first link not suitable.
    }

    // Chatbot initialization
    if (document.getElementById('chatMessages')) {
        addBotMessage("Hello! I'm your Learning Assistant. How can I help you?");
    }
});

function loadCourseOverview() {
    // This function ensures the course overview is shown if no unit is clicked or on initial load without units
    const mainContentArea = document.getElementById('mainCourseContent');
    const mainContentTitle = document.getElementById('mainContentTitle');
    const courseTitlePHP = <?php echo json_encode($course['title'] ?? 'Course Overview'); ?>;
    const courseDescriptionPHP =
        <?php echo json_encode(nl2br(htmlspecialchars($course['description'] ?? 'No detailed description provided.'))); ?>;

    mainContentTitle.textContent = courseTitlePHP;
    mainContentArea.innerHTML = `
            <h2 class="text-2xl font-semibold text-[#0B1F51] mb-4">Course Overview</h2>
            <div class="prose max-w-none text-gray-700">${courseDescriptionPHP}</div>
        `;
}

function escapeHtml(unsafe) {
    if (typeof unsafe !== 'string') return unsafe;
    return unsafe
        .replace(/&/g, "&")
        .replace(/</g, "<")
        .replace(/>/g, ">")
        .replace(/"/g, "")
        .replace(/'/g, "'");
}

// Chatbot JS Functionality (same as before)
function toggleChatbot() {
    const chatbot = document.getElementById('chatbot');
    const chatbotToggle = document.getElementById('chatbotToggle');
    chatbot.classList.toggle('hidden');
    chatbotToggle.classList.toggle('hidden');
}

function addBotMessage(message) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    const messageDiv = document.createElement('div');
    messageDiv.className = 'flex items-start space-x-2 mb-3';
    messageDiv.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-[#0B1F51] flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-white text-sm"></i>
            </div>
            <div class="bg-gray-100 rounded-lg p-3 max-w-[80%]">
                <p class="text-sm text-gray-700">${message}</p>
            </div>
        `;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function addUserMessage(message) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    const messageDiv = document.createElement('div');
    messageDiv.className = 'flex items-start space-x-2 justify-end mb-3';
    messageDiv.innerHTML = `
            <div class="bg-[#FFB800] text-[#0B1F51] rounded-lg p-3 max-w-[80%]">
                <p class="text-sm font-medium">${message}</p>
            </div>
        `;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

async function sendMessageToBackend() {
    const input = document.getElementById('chatInput');
    if (!input) return;
    const message = input.value.trim();

    if (message) {
        addUserMessage(message);
        input.value = '';
        addBotMessage("Thinking...");
        const thinkingMsgElement = document.getElementById('chatMessages').lastChild;
        try {
            const response = await fetch(base_url_js + 'actions/chatbot_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: message
                }),
            });
            if (thinkingMsgElement) thinkingMsgElement.remove();
            if (!response.ok) {
                addBotMessage('Sorry, I encountered an error. Please try again.');
                return;
            }
            const data = await response.json();
            if (data && data.reply) {
                addBotMessage(data.reply);
            } else {
                addBotMessage("I didn't get a clear response. Try again?");
            }
        } catch (error) {
            if (thinkingMsgElement) thinkingMsgElement.remove();
            addBotMessage('Sorry, I could not connect. Please check your connection.');
        }
    }
}

const chatInputElement = document.getElementById('chatInput');
if (chatInputElement) {
    chatInputElement.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendMessageToBackend();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>