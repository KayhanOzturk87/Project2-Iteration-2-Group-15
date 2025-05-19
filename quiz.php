<?php
$page_title = "Quiz - Crown Institute";
if (empty($base_url)) $base_url = '/';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error_message'] = "You must be logged in as a student to take a quiz.";
    header("Location: " . $base_url . "login.php");
    exit();
}
require_once __DIR__ . '/config/db_connect.php';

$quiz_status = $_GET['status'] ?? null;
$final_score_from_url = $_GET['score'] ?? null;
$total_questions_from_url = $_GET['total'] ?? null;
$status_message = $_GET['message'] ?? null;


if (!isset($_GET['course_id']) && !$quiz_status) { 
    $_SESSION['error_message'] = "Course ID not specified for the quiz.";
    header("Location: " . $base_url . "student-dashboard.php");
    exit();
}
$course_id = $_GET['course_id'] ?? null; 

$quiz_data = null;
$course_title_display = "Course Quiz"; // Default
$questions_from_db = [];
if ($course_id) { 
    try {
        $stmt = $pdo->prepare("SELECT q.quiz_id, q.title as quiz_title, q.questions_json, c.title as course_title 
                               FROM Quizzes q
                               JOIN Courses c ON q.course_id = c.course_id
                               WHERE q.course_id = ?");
        $stmt->execute([$course_id]);
        $quiz_data = $stmt->fetch();

        if ($quiz_data) {
            $course_title_display = htmlspecialchars($quiz_data['course_title']);
            $page_title = htmlspecialchars($quiz_data['quiz_title']) . " for " . $course_title_display;
            $questions_from_db = json_decode($quiz_data['questions_json'] ?? '[]', true);
        } else {
            // If not showing results screen, and no quiz found, then error
            if ($quiz_status !== 'completed' && $quiz_status !== 'error') {
                 $_SESSION['error_message'] = "No quiz found for this course.";
                 header("Location: " . $base_url . "unit-page.php?course_id=" . $course_id);
                 exit();
            }
        }
    } catch (PDOException $e) {
        $page_error = "Error fetching quiz data: " . $e->getMessage();
    }
} elseif ($quiz_status !== 'completed' && $quiz_status !== 'error') {
     $_SESSION['error_message'] = "Course ID missing for quiz.";
     header("Location: " . $base_url . "student-dashboard.php");
     exit();
}


if (($quiz_status !== 'completed' && $quiz_status !== 'error') && $quiz_data && empty($questions_from_db)) {
     $_SESSION['error_message'] = "This quiz currently has no questions.";
     header("Location: " . $base_url . "unit-page.php?course_id=" . $course_id);
     exit();
}
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto">
        <?php if (isset($page_error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?php echo htmlspecialchars($page_error); ?>
        </div>
        <?php endif; ?>
        <?php if ($status_message && $quiz_status === 'error'): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            Error: <?php echo htmlspecialchars(urldecode($status_message)); ?> Please try again or contact support.
            <br><a href="<?php echo $base_url; ?>student-dashboard.php" class="font-bold hover:underline">Go to
                Dashboard</a>
        </div>
        <?php endif; ?>


        <div id="resultsScreen"
            class="bg-white rounded-lg shadow-md p-8 text-center <?php echo ($quiz_status === 'completed' || ($quiz_status === 'error' && $final_score_from_url !== null)) ? '' : 'hidden'; ?>">
            <h2 class="text-3xl font-bold text-[#0B1F51] mb-4">Quiz Attempt Finished!</h2>
            <?php if ($quiz_status === 'completed'): ?>
            <div id="scoreDisplay" class="text-xl text-gray-700 mb-6">
                Your score: <?php echo htmlspecialchars($final_score_from_url); ?> out of
                <?php echo htmlspecialchars($total_questions_from_url); ?>
            </div>
            <p class="text-sm text-gray-600 mb-4">Your score has been recorded.</p>
            <?php elseif ($quiz_status === 'error' && $status_message): ?>
            <?php endif; ?>
            <div class="flex justify-center space-x-4 mt-6">
                <?php if ($course_id): ?>
                <a href="<?php echo $base_url; ?>quiz.php?course_id=<?php echo $course_id; ?>"
                    class="bg-[#FFB800] text-[#0B1F51] font-semibold py-3 px-6 rounded-lg hover:bg-[#e5a600] transition duration-300">
                    Try Again
                </a>
                <?php endif; ?>
                <a href="<?php echo $base_url . ($course_id ? 'unit-page.php?course_id=' . $course_id : 'student-dashboard.php'); ?>"
                    class="bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg hover:bg-gray-300 transition duration-300">
                    <?php echo $course_id ? 'Back to Course' : 'Back to Dashboard'; ?>
                </a>
            </div>
        </div>

        <div id="welcomeScreen"
            class="bg-white rounded-lg shadow-md p-8 text-center <?php echo ($quiz_data && $quiz_status !== 'completed' && $quiz_status !== 'error') ? '' : 'hidden'; ?>">
            <?php if ($quiz_data): // Ensure quiz_data exists before trying to access its properties ?>
            <h1 class="text-3xl font-bold text-[#0B1F51] mb-2"><?php echo htmlspecialchars($quiz_data['quiz_title']); ?>
            </h1>
            <p class="text-md text-gray-700 mb-6">For course: <?php echo $course_title_display; ?></p>
            <p class="text-gray-600 mb-8">Test your knowledge! There are <?php echo count($questions_from_db); ?>
                questions.</p>
            <button onclick="startQuiz()"
                class="bg-[#FFB800] text-[#0B1F51] font-semibold py-3 px-6 rounded-lg hover:bg-[#e5a600] transition duration-300">
                Start Quiz
            </button>
            <?php elseif (!$page_error && !$status_message): // If no quiz data, but no error was explicitly set, implies an issue like no course_id for start state ?>
            <p class="text-red-600">Could not load quiz. Please ensure you have selected a valid course.</p>
            <a href="<?php echo $base_url; ?>student-dashboard.php"
                class="mt-4 inline-block bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg hover:bg-gray-300 transition duration-300">
                Back to Dashboard
            </a>
            <?php endif; ?>
        </div>

        <div id="quizContainer" class="bg-white rounded-lg shadow-md p-8 hidden">
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="questionText" class="text-2xl font-semibold text-[#0B1F51]"></h2>
                    <span id="questionNumber" class="text-lg font-medium text-gray-600"></span>
                </div>
                <div id="optionsContainer" class="space-y-4"></div>
            </div>
            <div id="feedback" class="text-center py-4 font-semibold hidden"></div>
            <form id="quizScoreForm" action="<?php echo $base_url; ?>actions/submit_quiz_action.php" method="POST"
                class="hidden">
                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id ?? ''); ?>">
                <input type="hidden" name="quiz_id"
                    value="<?php echo htmlspecialchars($quiz_data['quiz_id'] ?? ''); ?>">
                <input type="hidden" name="score" id="finalScoreInput">
                <input type="hidden" name="total_questions" id="totalQuestionsInput">
            </form>
        </div>
    </div>
</div>

<script>
const questions = <?php echo json_encode($questions_from_db); ?>;
let currentQuestionIndex = 0;
let studentScore = 0;

function startQuiz() {
    document.getElementById("welcomeScreen").classList.add("hidden");
    document.getElementById("resultsScreen").classList.add("hidden"); // Ensure results not shown
    document.getElementById("quizContainer").classList.remove("hidden");
    currentQuestionIndex = 0;
    studentScore = 0;
    showQuestion();
}

function showQuestion() {
    if (questions.length === 0) {
        document.getElementById("quizContainer").innerHTML =
            "<p class='text-red-500'>No questions available for this quiz.</p>";
        return;
    }
    const questionData = questions[currentQuestionIndex];
    document.getElementById("questionText").textContent = questionData.q;
    document.getElementById("questionNumber").textContent = `Question ${currentQuestionIndex + 1}/${questions.length}`;

    const optionsContainer = document.getElementById("optionsContainer");
    optionsContainer.innerHTML = "";

    questionData.options.forEach((option, index) => {
        const button = document.createElement("button");
        button.className =
            "w-full text-left p-4 rounded-lg border border-gray-300 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#FFB800] transition duration-150";
        button.onclick = () => selectAnswer(index, button);
        button.textContent = option;
        button.dataset.index = index;
        optionsContainer.appendChild(button);
    });
    document.getElementById("feedback").classList.add("hidden");

    let nextButton = document.getElementById('nextQuestionButton');
    if (!nextButton) {
        nextButton = document.createElement('button');
        nextButton.id = 'nextQuestionButton';
        nextButton.className =
            'mt-6 w-full bg-[#0B1F51] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#0a1b45] transition duration-300 hidden';
        nextButton.onclick = moveToNextQuestion;
        const formEl = document.getElementById('quizScoreForm');
        if (formEl) {
            formEl.parentNode.insertBefore(nextButton, formEl);
        } else {
            document.getElementById('quizContainer').appendChild(nextButton);
        }
    }
    nextButton.classList.add('hidden');
}

function selectAnswer(selectedIndex, selectedButton) {
    const buttons = document.querySelectorAll("#optionsContainer button");
    buttons.forEach(btn => {
        btn.classList.remove('ring-2', 'ring-[#FFB800]', 'bg-blue-50');
        btn.disabled = true;
    });
    selectedButton.classList.add('ring-2', 'ring-[#0B1F51]', 'bg-blue-100');

    checkAnswerUI(selectedIndex);
}

function checkAnswerUI(selectedIndex) {
    const questionData = questions[currentQuestionIndex];
    const feedbackEl = document.getElementById("feedback");
    const buttons = document.querySelectorAll("#optionsContainer button");

    if (parseInt(selectedIndex) === parseInt(questionData.answer)) {
        studentScore++;
        feedbackEl.textContent = "Correct! ✅";
        feedbackEl.className = "text-center py-2 font-semibold text-green-600 text-lg";
        buttons[selectedIndex].classList.remove('bg-blue-100', 'ring-[#0B1F51]');
        buttons[selectedIndex].classList.add("bg-green-100", "border-green-500", "ring-green-500");
    } else {
        feedbackEl.textContent = `Incorrect. The correct answer was: ${questionData.options[questionData.answer]}`;
        feedbackEl.className = "text-center py-2 font-semibold text-red-600 text-lg";
        buttons[selectedIndex].classList.remove('bg-blue-100', 'ring-[#0B1F51]');
        buttons[selectedIndex].classList.add("bg-red-100", "border-red-500", "ring-red-500");
        buttons[questionData.answer].classList.add("bg-green-100", "border-green-500", "ring-green-500");
    }

    feedbackEl.classList.remove("hidden");

    const nextButton = document.getElementById('nextQuestionButton');
    if (currentQuestionIndex < questions.length - 1) {
        nextButton.textContent = 'Next Question';
    } else {
        nextButton.textContent = 'Show Results';
    }
    nextButton.classList.remove('hidden');
}

function moveToNextQuestion() {
    currentQuestionIndex++;
    if (currentQuestionIndex < questions.length) {
        showQuestion();
    } else {
        submitAndShowResults();
    }
}

function submitAndShowResults() {
    document.getElementById("finalScoreInput").value = studentScore;
    document.getElementById("totalQuestionsInput").value = questions.length;
    document.getElementById("quizScoreForm").submit();
}
</script>

<?php include 'includes/footer.php'; ?>