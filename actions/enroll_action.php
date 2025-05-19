<?php
session_start();
require_once '../config/db_connect.php';
$base_url = '/';

// check if student is login or not, if not go to login page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error_message'] = "You must be logged in as a student to enroll.";
    header("Location: " . $base_url . "login.php");
    exit();
}

// check if post request and course id is given to enroll
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id_to_enroll'])) {
    $course_id = $_POST['course_id_to_enroll'];
    $user_id = $_SESSION['user_id'];

    try {
        // check if student already enrolled in that course
        $stmt_check = $pdo->prepare("SELECT enrollment_id FROM Enrollments WHERE user_id = ? AND course_id = ?");
        $stmt_check->execute([$user_id, $course_id]);
        if ($stmt_check->fetch()) {
            $_SESSION['error_message'] = "You are already enrolled in this course.";
            header("Location: " . $base_url . "courses.php");
            exit();
        }

        // if not enrolled yet then add the student in the course
        $sql = "INSERT INTO Enrollments (user_id, course_id, progress) VALUES (?, ?, 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $course_id]);

        // show success message and move to dashboard
        $_SESSION['success_message'] = "Successfully enrolled in the course!";
        header("Location: " . $base_url . "student-dashboard.php");
        exit();

    } catch (PDOException $e) {
        // if some error happen in database while enrolling
        $_SESSION['error_message'] = "Database error during enrollment: " . $e->getMessage();
        header("Location: " . $base_url . "courses.php");
        exit();
    }
} else {
    // if no course id or request is not post
    $_SESSION['error_message'] = "Invalid request for enrollment.";
    header("Location: " . $base_url . "courses.php");
    exit();
}
?>