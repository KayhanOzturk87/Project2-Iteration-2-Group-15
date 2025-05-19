<?php
session_start();
require_once '../config/db_connect.php';
$base_url = '/';

// check if student is login, if not then stop them
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error_message'] = "Unauthorized action.";
    header("Location: " . $base_url . "login.php");
    exit();
}

// if user come with POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $course_id = $_POST['course_id'] ?? null;
    $quiz_id = $_POST['quiz_id'] ?? null;
    $final_score = $_POST['score'] ?? 0;
    $total_questions = $_POST['total_questions'] ?? 0;

    $redirect_url_params = "";

    // make sure quiz and course both is present
    if ($course_id && $quiz_id) {
        try {
            // update score for that course in Enrollments table
            $stmt = $pdo->prepare("UPDATE Enrollments SET quiz_score = ? WHERE user_id = ? AND course_id = ?");
            $stmt->execute([$final_score, $user_id, $course_id]);
            
            // send to quiz page with score and status
            $redirect_url_params = "?course_id=" . urlencode($course_id) . "&score=" . urlencode($final_score) . "&total=" . urlencode($total_questions) . "&status=completed";

        } catch (PDOException $e) {
            // if any error from database while saving score
            error_log("Error submitting quiz score: " . $e->getMessage());
            $redirect_url_params = "?course_id=" . urlencode($course_id) . "&status=error&message=" . urlencode("Database error during score submission.");
        }
    } else {
        // course or quiz id not found
        error_log("Missing course_id or quiz_id in submit_quiz_action.php");
        if ($course_id) {
             $redirect_url_params = "?course_id=" . urlencode($course_id) . "&status=error&message=" . urlencode("Quiz information missing.");
        } else {
             $_SESSION['error_message'] = "Could not submit quiz due to missing information.";
             header("Location: " . $base_url . "student-dashboard.php");
             exit();
        }
    }

    // after all work, redirect to quiz page
    header("Location: " . $base_url . "quiz.php" . $redirect_url_params);
    exit();

} else {
    // if someone open this page directly without submit
    $_SESSION['error_message'] = "Invalid access to quiz submission.";
    header("Location: " . $base_url . "student-dashboard.php");
    exit();
}
?>