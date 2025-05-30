<?php
session_start();
require_once '../config/db_connect.php';
$base_url = '/'; // Ensure this matches your project

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error_message'] = "Unauthorized action.";
    header("Location: " . $base_url . "login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $course_id = $_POST['course_id'] ?? null;
    $quiz_id = $_POST['quiz_id'] ?? null;
    $final_score = isset($_POST['score']) ? (int)$_POST['score'] : 0;
    $total_questions = isset($_POST['total_questions']) ? (int)$_POST['total_questions'] : 0;

    $redirect_url_params = "";

    if ($course_id && $quiz_id) {
        try {
            $pdo->beginTransaction(); // Start transaction

            $stmt_score = $pdo->prepare("UPDATE Enrollments SET quiz_score = ? WHERE user_id = ? AND course_id = ?");
            $stmt_score->execute([$final_score, $user_id, $course_id]);
            
            $new_progress = 50; 

            if ($total_questions > 0 && $final_score === $total_questions) { // Perfect score
                $new_progress = 100; // Or maybe 75 if quiz isn't the whole course
            } elseif ($total_questions > 0 && $final_score >= ($total_questions * 0.5)) { // Scored at least 50% on quiz
                $new_progress = 75; // Or some other significant step
            }

            $stmt_progress = $pdo->prepare(
                "UPDATE Enrollments SET progress = GREATEST(progress, ?) 
                 WHERE user_id = ? AND course_id = ?"
            );
            $stmt_progress->execute([$new_progress, $user_id, $course_id]);

            $pdo->commit();
            $redirect_url_params = "?course_id=" . urlencode($course_id) . "&score=" . urlencode($final_score) . "&total=" . urlencode($total_questions) . "&status=completed";

        } catch (PDOException $e) {
            $pdo->rollBack(); // Rollback on error
            error_log("Error submitting quiz score or progress: " . $e->getMessage());
            $redirect_url_params = "?course_id=" . urlencode($course_id) . "&status=error&message=" . urlencode("Database error during score submission.");
        }
    } else {
        error_log("Missing course_id or quiz_id in submit_quiz_action.php");
        if ($course_id) {
             $redirect_url_params = "?course_id=" . urlencode($course_id) . "&status=error&message=" . urlencode("Quiz information missing.");
        } else {
             $_SESSION['error_message'] = "Could not submit quiz due to missing information.";
             header("Location: " . $base_url . "student-dashboard.php");
             exit();
        }
    }
    
    header("Location: " . $base_url . "quiz.php" . $redirect_url_params);
    exit();

} else {
    $_SESSION['error_message'] = "Invalid access to quiz submission.";
    header("Location: " . $base_url . "student-dashboard.php");
    exit();
}
?>