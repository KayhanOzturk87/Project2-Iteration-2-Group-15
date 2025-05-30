<?php
$page_title = "Student Dashboard - Crown Institute";
if (empty($base_url)) $base_url = '/';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: " . $base_url . "login.php?error=unauthorized");
    exit();
}
require_once __DIR__ . '/config/db_connect.php';

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['user_name'];

$enrolled_courses = [];
$overall_summary_data = ['total_enrolled' => 0, 'courses_completed_via_quiz' => 0];

try {
    $stmt = $pdo->prepare("
        SELECT 
            c.course_id, c.title, c.image_url, c.category, 
            e.progress, e.quiz_score, 
            u.name as instructor_name,
            q.quiz_id, 
            IF(q.questions_json IS NOT NULL AND JSON_VALID(q.questions_json), JSON_LENGTH(q.questions_json), 0) as quiz_total_questions 
        FROM Enrollments e
        JOIN Courses c ON e.course_id = c.course_id
        JOIN Users u ON c.instructor_id = u.user_id
        LEFT JOIN Quizzes q ON c.course_id = q.course_id
        WHERE e.user_id = ?
        ORDER BY e.last_accessed DESC
    ");
    $stmt->execute([$student_id]);
    $enrolled_courses = $stmt->fetchAll();

    $overall_summary_data['total_enrolled'] = count($enrolled_courses);
    foreach ($enrolled_courses as $course) {
        if (isset($course['progress']) && $course['progress'] >= 100) {
             $overall_summary_data['courses_completed_via_quiz']++;
        }
    }

} catch (PDOException $e) {
    $dashboard_error = "Error fetching dashboard data: " . $e->getMessage();
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">My Learning Dashboard</h1>
        <div class="text-sm text-gray-600" id="welcomeMessage">Welcome back,
            <?php echo htmlspecialchars($student_name); ?>!</div>
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-[#0B1F51]">My Enrolled Courses</h2>
            <?php if (empty($enrolled_courses)): ?>
            <p class="text-gray-600">You are not enrolled in any courses yet. <a
                    href="<?php echo $base_url; ?>courses.php" class="text-blue-600 hover:underline">Browse
                    courses</a>.</p>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($enrolled_courses as $course): ?>
                <?php
                    $course_progress_percent = 0;
                    if (isset($course['quiz_total_questions']) && $course['quiz_total_questions'] > 0 && isset($course['quiz_score'])) {
                        $course_progress_percent = round(((int)$course['quiz_score'] / (int)$course['quiz_total_questions']) * 100);
                    } elseif (isset($course['progress'])) { // Fallback to stored progress if quiz data isn't definitive
                        $course_progress_percent = (int)$course['progress'];
                    }
                    $course_progress_percent = max(0, min(100, $course_progress_percent)); // Clamp between 0 and 100
                ?>
                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex flex-col sm:flex-row justify-between items-start">
                        <div class="flex items-center mb-2 sm:mb-0">
                            <img class="h-16 w-16 rounded-md object-cover mr-4 flex-shrink-0"
                                src="<?php echo !empty($course['image_url']) ? (filter_var($course['image_url'], FILTER_VALIDATE_URL) ? $course['image_url'] : $base_url . 'uploads/' . htmlspecialchars($course['image_url'])) : 'https://images.pexels.com/photos/546819/pexels-photo-546819.jpeg'; ?>"
                                alt="<?php echo htmlspecialchars($course['title']); ?>">
                            <div>
                                <h3 class="font-semibold text-lg text-gray-800">
                                    <?php echo htmlspecialchars($course['title']); ?></h3>
                                <p class="text-xs text-gray-500">Category:
                                    <?php echo htmlspecialchars(ucfirst($course['category'])); ?></p>
                                <p class="text-xs text-gray-500">Instructor:
                                    <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                            </div>
                        </div>
                        <div class="text-left sm:text-right w-full sm:w-auto mt-2 sm:mt-0">
                            <div class="text-[#0B1F51] font-semibold"><?php echo $course_progress_percent; ?>% Complete
                            </div>
                            <div class="w-full sm:w-32 h-2 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-[#FFB800] rounded-full"
                                    style="width: <?php echo $course_progress_percent; ?>%"></div>
                            </div>
                            <?php if ($course['quiz_score'] !== null && isset($course['quiz_total_questions'])): ?>
                            <p class="text-xs text-gray-500 mt-1">Last Quiz:
                                <?php echo htmlspecialchars($course['quiz_score']); ?>/<?php echo htmlspecialchars($course['quiz_total_questions']); ?>
                            </p>
                            <?php elseif ($course['quiz_score'] !== null): ?>
                            <p class="text-xs text-gray-500 mt-1">Last Quiz Score:
                                <?php echo htmlspecialchars($course['quiz_score']); ?> (Total N/A)</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?php echo $base_url; ?>unit-page.php?course_id=<?php echo $course['course_id']; ?>"
                        class="mt-3 w-full sm:w-auto inline-block py-2 px-4 bg-[#0B1F51] text-white text-sm rounded hover:bg-[#0a1b45] transition-colors text-center">
                        Continue Learning
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4 text-[#0B1F51]">Learning Progress</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-[#0B1F51]">
                            <?php echo $overall_summary_data['total_enrolled']; ?></div>
                        <div class="text-sm text-gray-600">Courses Enrolled</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">
                            <?php echo $overall_summary_data['courses_completed_via_quiz']; ?></div>
                        <div class="text-sm text-gray-600">Courses Completed</div>
                    </div>
                </div>
                <a href="<?php echo $base_url; ?>courses.php"
                    class="mt-4 w-full block py-2 px-4 bg-[#FFB800] text-[#0B1F51] font-semibold rounded-lg hover:bg-[#e5a600] transition duration-300 text-center">
                    Browse More Courses
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4 text-[#0B1F51]">Upcoming Assignments</h2>
                <p class="text-gray-500 text-sm">No upcoming assignments for now. Check your course pages for details.
                </p>
            </div>
        </div>
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

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('chatMessages')) {
        addBotMessage("Hello! I'm your Learning Assistant. How can I help you today?");
    }
});

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