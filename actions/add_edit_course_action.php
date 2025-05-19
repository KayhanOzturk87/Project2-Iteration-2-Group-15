<?php
session_start();
require_once '../config/db_connect.php';
$base_url = '/';

// check if user not login or not teacher, then send back to login page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    $_SESSION['error_message'] = "Unauthorized access.";
    header("Location: " . $base_url . "login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'] ?? null;
    $quiz_id = $_POST['quiz_id'] ?? null;
    $title = trim($_POST['courseTitle']);
    $category = trim($_POST['courseCategory']);
    $description = trim($_POST['courseDescription']);
    $unit_titles = $_POST['unit_titles'] ?? [];
    $units_titles_json = json_encode(array_values(array_filter($unit_titles)));

    $quiz_title = trim($_POST['quiz_title']);
    $quiz_questions_input = $_POST['quiz_questions'] ?? [];
    $formatted_quiz_questions = [];

    // here we check and clean all quiz questions before save
    if (!empty($quiz_questions_input)) {
        foreach ($quiz_questions_input as $q_data) {
            if (!empty(trim($q_data['q']))) {
                $options = array_values(array_filter($q_data['options'] ?? []));
                if (count($options) >= 2 && isset($q_data['answer'])) {
                    $formatted_quiz_questions[] = [
                        'q' => trim($q_data['q']),
                        'options' => $options,
                        'answer' => (int)$q_data['answer']
                    ];
                }
            }
        }
    }

    $questions_json = json_encode($formatted_quiz_questions);

    $image_url = $_POST['existing_image_url'] ?? null;

    // upload image if new file selected
    if (isset($_FILES['courseImage']) && $_FILES['courseImage']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $image_name = time() . '_' . basename($_FILES["courseImage"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        // only allow some image types
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["courseImage"]["tmp_name"], $target_file)) {
                // delete old image if new one uploaded
                if ($image_url && !filter_var($image_url, FILTER_VALIDATE_URL) && file_exists($target_dir . $image_url)) {
                    unlink($target_dir . $image_url);
                }
                $image_url = $image_name;
            } else {
                $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
                header("Location: " . $base_url . "teacher-dashboard.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            header("Location: " . $base_url . "teacher-dashboard.php");
            exit();
        }
    } elseif (empty($image_url) && empty($course_id)) {
         $image_url = null;
    }

    try {
        $pdo->beginTransaction();

        // update course if id is there, else create new
        if ($course_id) {
            $sql_course = "UPDATE Courses SET title = ?, description = ?, category = ?, image_url = ?, units_titles_json = ? WHERE course_id = ? AND instructor_id = ?";
            $stmt_course = $pdo->prepare($sql_course);
            $stmt_course->execute([$title, $description, $category, $image_url, $units_titles_json, $course_id, $teacher_id]);

            // update or insert quiz also
            if ($quiz_id) {
                $sql_quiz = "UPDATE Quizzes SET title = ?, questions_json = ? WHERE quiz_id = ? AND course_id = ?";
                $stmt_quiz = $pdo->prepare($sql_quiz);
                $stmt_quiz->execute([$quiz_title, $questions_json, $quiz_id, $course_id]);
            } elseif(!empty($quiz_title) && !empty($formatted_quiz_questions)) {
                $sql_quiz = "INSERT INTO Quizzes (course_id, title, questions_json) VALUES (?, ?, ?)";
                $stmt_quiz = $pdo->prepare($sql_quiz);
                $stmt_quiz->execute([$course_id, $quiz_title, $questions_json]);
            }
            $_SESSION['success_message'] = "Course updated successfully!";
        } else {
            $sql_course = "INSERT INTO Courses (title, description, category, image_url, instructor_id, units_titles_json) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_course = $pdo->prepare($sql_course);
            $stmt_course->execute([$title, $description, $category, $image_url, $teacher_id, $units_titles_json]);
            $new_course_id = $pdo->lastInsertId();

            // insert quiz if it exist for new course
            if (!empty($quiz_title) && !empty($formatted_quiz_questions)) {
                $sql_quiz = "INSERT INTO Quizzes (course_id, title, questions_json) VALUES (?, ?, ?)";
                $stmt_quiz = $pdo->prepare($sql_quiz);
                $stmt_quiz->execute([$new_course_id, $quiz_title, $questions_json]);
            }
            $_SESSION['success_message'] = "Course added successfully!";
        }

        $pdo->commit();
    } catch (PDOException $e) {
        // if any error come, then undo all work
        $pdo->rollBack();
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }

    // after everything done, go back to teacher dashboard
    header("Location: " . $base_url . "teacher-dashboard.php");
    exit();
}
?>