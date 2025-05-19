<?php
session_start();
require_once '../config/db_connect.php';
$base_url = '/';

// check if user is not login or not teacher, then go login page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    $_SESSION['error_message'] = "Unauthorized access.";
    header("Location: " . $base_url . "login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// check if form submitted and course id is given
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];

    try {
        // get course image first before delete
        $stmt_img = $pdo->prepare("SELECT image_url FROM Courses WHERE course_id = ? AND instructor_id = ?");
        $stmt_img->execute([$course_id, $teacher_id]);
        $course = $stmt_img->fetch();

        if ($course) {
            // now delete course from database
            $sql = "DELETE FROM Courses WHERE course_id = ? AND instructor_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$course_id, $teacher_id]);

            // if deleted, check and remove image also if exist
            if ($stmt->rowCount() > 0) {
                if ($course['image_url'] && !filter_var($course['image_url'], FILTER_VALIDATE_URL)) {
                    $image_path = "../uploads/" . $course['image_url'];
                    if (file_exists($image_path)) {
                        unlink($image_path); // delete image file from folder
                    }
                }
                $_SESSION['success_message'] = "Course deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Could not delete course or you do not own this course.";
            }
        } else {
            $_SESSION['error_message'] = "Course not found or you do not own this course.";
        }
    } catch (PDOException $e) {
        // something wrong with database
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
} else {
    // request not proper or no course id
    $_SESSION['error_message'] = "Invalid request.";
}

header("Location: " . $base_url . "teacher-dashboard.php");
exit();
?>