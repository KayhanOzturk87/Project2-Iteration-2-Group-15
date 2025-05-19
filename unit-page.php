<?php
if (empty($base_url)) $base_url = '/learning_assistant/'; 
include 'includes/header.php'; 

if (!isset($_SESSION['user_id'])) { 
    header("Location: " . $base_url . "login.php?error=auth_required");
    exit();
}
require_once __DIR__ . '/config/db_connect.php';

if (!isset($_GET['course_id'])) {
    $_SESSION['error_message'] = "Course ID not specified.";
    header("Location: " . $base_url . ($_SESSION['user_role'] === 'student' ? "student-dashboard.php" : "teacher-dashboard.php"));
    exit();
}

$course_id = $_GET['course_id'];
$course = null;
$is_student_enrolled = false;

try {
    $stmt = $pdo->prepare("SELECT c.*, u.name as instructor_name
                           FROM Courses c
                           JOIN Users u ON c.instructor_id = u.user_id
                           WHERE c.course_id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();

    if (!$course) {
        $_SESSION['error_message'] = "Course not found.";
        header("Location: " . $base_url . "courses.php"); // Redirect to general courses page
        exit();
    }

 
    if ($_SESSION['user_role'] === 'student') {
        $stmt_enroll = $pdo->prepare("SELECT 1 FROM Enrollments WHERE user_id = ? AND course_id = ?");
        $stmt_enroll->execute([$_SESSION['user_id'], $course_id]);
        if ($stmt_enroll->fetch()) {
            $is_student_enrolled = true;
        }
    }

} catch (PDOException $e) {
    $page_error = "Error fetching course details: " . $e->getMessage();
}

$unit_titles = [];
if ($course && isset($course['units_titles_json'])) {
    $unit_titles = json_decode($course['units_titles_json'], true);
    if (json_last_error() !== JSON_ERROR_NONE) { 
        $unit_titles = []; 
        error_log("JSON decode error for units_titles_json, course_id: " . $course_id);
    }
}
?>
<nav class="bg-[#0B1F51] text-white shadow-lg">
    <div class="max-w-full px-4 mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-center h-auto sm:h-16 py-2 sm:py-0">
            <div class="flex items-center space-x-8 mb-2 sm:mb-0">
                <a href="#"
                    class="text-xl font-semibold"><?php echo htmlspecialchars($course['title'] ?? 'Course'); ?></a>
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
                <a href="<?php echo $base_url; ?>actions/logout_action.php" class="text-white hover:text-[#FFB800]">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="flex min-h-[calc(100vh-4rem)]">
    <div class="w-full sm:w-64 bg-[#0B1F51] text-white flex-shrink-0">
        <div class="p-4 sticky top-16">
            <h3 class="text-lg font-semibold mb-3 text-[#FFB800]">Course Units</h3>
            <div id="sidebar" class="space-y-1">
                <?php if (empty($unit_titles)): ?>
                <p class="px-4 py-2 text-gray-400">No units defined for this course yet.</p>
                <?php else: ?>
                <?php foreach ($unit_titles as $index => $unit_title): ?>
                <a href="#unit-<?php echo $index + 1; ?>"
                    onclick="displayUnitContent(event, <?php echo $index; ?>, '<?php echo htmlspecialchars(addslashes($unit_title)); ?>', 'Placeholder content for <?php echo htmlspecialchars(addslashes($unit_title)); ?>. This section would typically contain detailed text, videos, or resources related to the unit topic. For now, it is a simple demonstration.')"
                    class="block px-4 py-2 rounded hover:bg-[#FFB800] hover:text-[#0B1F51] transition-colors truncate"
                    title="<?php echo htmlspecialchars($unit_title); ?>">
                    <?php echo ($index + 1) . ". " . htmlspecialchars($unit_title); ?>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

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
                <?php echo htmlspecialchars($course['instructor_name']); ?></p>

            <div id="mainCourseContent" class="prose max-w-none bg-white p-6 rounded-lg shadow min-h-[300px]">
                <h2 class="text-2xl font-semibold text-[#0B1F51] mb-4">Course Overview</h2>
                <?php echo nl2br(htmlspecialchars($course['description'] ?? 'No detailed description provided.')); ?>
            </div>
        </div>
        <?php else: ?>
        <p class="text-gray-600">Course details could not be loaded.</p>
        <?php endif; ?>
    </div>
</div>

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
            <h2 class="text-2xl font-semibold text-[#0B1F51] mb-4">${title}</h2>
            <div class="text-gray-700">${content.replace(/\n/g, '<br>')}</div>
            <hr class="my-6">
            <p class="text-sm text-gray-500">This is placeholder content for Unit ${index + 1}. Real content would be dynamically loaded.</p>
        `;

    document.querySelectorAll('#sidebar a').forEach(link => link.classList.remove('bg-[#FFB800]', 'text-[#0B1F51]'));
    const activeLink = event.currentTarget;
    if (activeLink) {
        activeLink.classList.add('bg-[#FFB800]', 'text-[#0B1F51]');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const firstUnitLink = document.querySelector('#sidebar a');
    if (firstUnitLink) {
        const onclickAttr = firstUnitLink.getAttribute('onclick');
        if (onclickAttr) {
            try {
                const paramsMatch = onclickAttr.match(
                    /displayUnitContent\((?:event,\s*)?(\d+),\s*'(.*?)',\s*'(.*?)'\)/);
                if (paramsMatch && paramsMatch.length === 4) {
                    const index = parseInt(paramsMatch[1]);
                    const title = paramsMatch[2].replace(/\\'/g, "'");
                    const content = paramsMatch[3].replace(/\\'/g, "'");
                    displayUnitContent({
                        preventDefault: () => {}
                    }, index, title, content);
                }
            } catch (e) {
                console.error("Error auto-clicking first unit:", e);
            }
        }
    }

    // Chatbot initialization
    if (document.getElementById('chatMessages')) {
        addBotMessage("Hello! I'm your Learning Assistant. How can I help you with basic questions today?");
    }
});


// Chatbot JS Functionality
function toggleChatbot() {
    const chatbot = document.getElementById('chatbot');
    const chatbotToggle = document.getElementById('chatbotToggle');
    chatbot.classList.toggle('hidden');
    chatbotToggle.classList.toggle('hidden');
}

function addBotMessage(message) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return; // Guard clause
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
    if (!chatMessages) return; // Guard clause
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
    if (!input) return; // Guard clause
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
                console.error('Chatbot request failed:', response.statusText);
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
            console.error('Error sending message to chatbot:', error);
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