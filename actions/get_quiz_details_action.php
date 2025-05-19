<?php
session_start();
require_once '../config/db_connect.php';
header('Content-Type: application/json');

$base_url = '/';

// check if user is login as teacher, else show unauthorized
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// check if course_id is given in URL
if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
    try {
        // find quiz related with that course id
        $stmt = $pdo->prepare("SELECT quiz_id, title, questions_json FROM Quizzes WHERE course_id = ?");
        $stmt->execute([$course_id]);
        $quiz = $stmt->fetch();

        // if quiz found send it back as json
        if ($quiz) {
            echo json_encode($quiz);
        } else {
            echo json_encode(null); // no quiz found for course
        }
    } catch (PDOException $e) {
        // if something wrong in database
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // if no course id in url then show error
    echo json_encode(['error' => 'Course ID not provided']);
}
exit();
?>