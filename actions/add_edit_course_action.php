<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db_connect.php';
$base_url = '/'; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    $_SESSION['error_message'] = "Unauthorized access.";
    header("Location: " . $base_url . "login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id_from_form = $_POST['course_id'] ?? null;
    $quiz_id = $_POST['quiz_id'] ?? null;
    $title = trim($_POST['courseTitle']);
    $category = trim($_POST['courseCategory']);
    $description = trim($_POST['courseDescription']);
    $unit_titles = $_POST['unit_titles'] ?? [];
    $units_titles_json = json_encode(array_values(array_filter($unit_titles)));

    $quiz_title = trim($_POST['quiz_title']);
    $quiz_questions_input = $_POST['quiz_questions'] ?? [];
    $formatted_quiz_questions = [];
    if (!empty($quiz_questions_input)) {
        foreach ($quiz_questions_input as $key => $q_data) { // Changed to use $key
            if (isset($q_data['q']) && !empty(trim($q_data['q']))) {
                $options = isset($q_data['options']) ? array_values(array_filter($q_data['options'])) : [];
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
    if (isset($_FILES['courseImage']) && $_FILES['courseImage']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true) && !is_dir($target_dir)) {
                $_SESSION['error_message'] = "Failed to create course image upload directory.";
                header("Location: " . $base_url . "teacher-dashboard.php");
                exit();
            }
        }
        
        $image_original_name = basename($_FILES["courseImage"]["name"]);
        $image_sanitized_name = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "", $image_original_name);
        if(empty($image_sanitized_name)) $image_sanitized_name = "course_image";
        $image_extension = strtolower(pathinfo($image_original_name, PATHINFO_EXTENSION));
        $image_unique_name = time() . '_' . pathinfo($image_sanitized_name, PATHINFO_FILENAME) . '.' . $image_extension;
        
        $target_file = $target_dir . $image_unique_name;
        $allowed_image_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_extension, $allowed_image_types)) {
            if (move_uploaded_file($_FILES["courseImage"]["tmp_name"], $target_file)) {
                if ($image_url && !filter_var($image_url, FILTER_VALIDATE_URL) && file_exists($target_dir . $image_url)) {
                    @unlink($target_dir . $image_url);
                }
                $image_url = $image_unique_name;
            } else {
                $_SESSION['error_message'] = "Sorry, there was an error uploading your course image. Check directory permissions.";
                header("Location: " . $base_url . "teacher-dashboard.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed for course image.";
            header("Location: " . $base_url . "teacher-dashboard.php");
            exit();
        }
    } elseif (empty($image_url) && empty($course_id_from_form)) {
         $image_url = null;
    }

    $current_course_id_for_material = null;

    try {
        $pdo->beginTransaction();
        if ($course_id_from_form) {
            $current_course_id_for_material = $course_id_from_form;
            $sql_course = "UPDATE Courses SET title = ?, description = ?, category = ?, image_url = ?, units_titles_json = ? WHERE course_id = ? AND instructor_id = ?";
            $stmt_course = $pdo->prepare($sql_course);
            $stmt_course->execute([$title, $description, $category, $image_url, $units_titles_json, $course_id_from_form, $teacher_id]);

            if ($quiz_id) {
                $sql_quiz = "UPDATE Quizzes SET title = ?, questions_json = ? WHERE quiz_id = ? AND course_id = ?";
                $stmt_quiz = $pdo->prepare($sql_quiz);
                $stmt_quiz->execute([$quiz_title, $questions_json, $quiz_id, $course_id_from_form]);
            } elseif(!empty($quiz_title) && !empty($formatted_quiz_questions)) {
                $sql_quiz_check = $pdo->prepare("SELECT quiz_id FROM Quizzes WHERE course_id = ?");
                $sql_quiz_check->execute([$course_id_from_form]);
                if (!$sql_quiz_check->fetch()) {
                    $sql_quiz = "INSERT INTO Quizzes (course_id, title, questions_json) VALUES (?, ?, ?)";
                    $stmt_quiz = $pdo->prepare($sql_quiz);
                    $stmt_quiz->execute([$course_id_from_form, $quiz_title, $questions_json]);
                }
            }
            $_SESSION['success_message'] = "Course updated successfully!";
        } else {
            $sql_course = "INSERT INTO Courses (title, description, category, image_url, instructor_id, units_titles_json) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_course = $pdo->prepare($sql_course);
            $stmt_course->execute([$title, $description, $category, $image_url, $teacher_id, $units_titles_json]);
            $new_course_id = $pdo->lastInsertId();
            $current_course_id_for_material = $new_course_id;

            if (!empty($quiz_title) && !empty($formatted_quiz_questions)) {
                $sql_quiz = "INSERT INTO Quizzes (course_id, title, questions_json) VALUES (?, ?, ?)";
                $stmt_quiz = $pdo->prepare($sql_quiz);
                $stmt_quiz->execute([$new_course_id, $quiz_title, $questions_json]);
            }
            $_SESSION['success_message'] = "Course added successfully!";
        }

        if (isset($_FILES['new_material_file']) && $current_course_id_for_material) {
            if ($_FILES['new_material_file']['error'] == UPLOAD_ERR_OK) {
                $material_file_name_original = basename($_FILES["new_material_file"]["name"]);
                $material_title = trim($_POST['new_material_title']) ?: pathinfo($material_file_name_original, PATHINFO_FILENAME);
                $material_description = trim($_POST['new_material_description']);
                
                $material_target_dir = "../uploads/materials/";
                if (!is_dir($material_target_dir)) {
                    if (!mkdir($material_target_dir, 0777, true) && !is_dir($material_target_dir)) {
                        $_SESSION['error_message'] = ($_SESSION['error_message'] ?? "") . " Error: Material upload directory ('{$material_target_dir}') cannot be created.";
                        goto end_material_processing_block;
                    }
                }

                if (is_dir($material_target_dir) && is_writable($material_target_dir)) {
                    $sanitized_original_name = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "", $material_file_name_original);
                    if(empty($sanitized_original_name)) {
                        $sanitized_original_name = "uploaded_material";
                    }
                    $file_extension = strtolower(pathinfo($material_file_name_original, PATHINFO_EXTENSION));
                    $base_name_sanitized = pathinfo($sanitized_original_name, PATHINFO_FILENAME);
                    $material_unique_filename = time() . '_' . $base_name_sanitized . '.' . $file_extension;
                    
                    $material_target_file_path = $material_target_dir . $material_unique_filename;
                    $material_file_type = mime_content_type($_FILES['new_material_file']['tmp_name']);
                    if (!$material_file_type) {
                        $material_file_type = $_FILES['new_material_file']['type'] ?: 'application/octet-stream';
                    }

                    if (move_uploaded_file($_FILES["new_material_file"]["tmp_name"], $material_target_file_path)) {
                        $sql_material = "INSERT INTO CourseMaterials (course_id, uploader_id, file_name, file_path, file_type, title, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt_material = $pdo->prepare($sql_material);
                        $stmt_material->execute([$current_course_id_for_material, $teacher_id, $material_file_name_original, $material_unique_filename, $material_file_type, $material_title, $material_description]);
                        
                        $current_success_message = $_SESSION['success_message'] ?? '';
                        $_SESSION['success_message'] = rtrim($current_success_message, '.') . ". New material uploaded.";
                    } else {
                        $upload_error_message = "Failed to move uploaded material file.";
                        if (!is_writable($material_target_dir)) {
                            $upload_error_message .= " The target directory ('uploads/materials/') is not writable by the server.";
                        } else {
                             $system_error = error_get_last();
                             $upload_error_message .= " System error: " . ($system_error['message'] ?? 'Unknown system error during move.');
                        }
                        $_SESSION['error_message'] = ($_SESSION['error_message'] ?? "") . " " . $upload_error_message;
                    }
                } else {
                     $_SESSION['error_message'] = ($_SESSION['error_message'] ?? "") . " Error: Material upload directory ('uploads/materials/') does not exist or is not writable.";
                }

            } elseif ($_FILES['new_material_file']['error'] != UPLOAD_ERR_NO_FILE) {
                $php_upload_errors = [
                    UPLOAD_ERR_INI_SIZE   => "Material file too large (server limit).",
                    UPLOAD_ERR_FORM_SIZE  => "Material file too large (form limit).",
                    UPLOAD_ERR_PARTIAL    => "Material file only partially uploaded.",
                    UPLOAD_ERR_CANT_WRITE => "Cannot write material file to disk.",
                    UPLOAD_ERR_EXTENSION  => "Material upload stopped by a PHP extension.",
                ];
                $error_code = $_FILES['new_material_file']['error'];
                $specific_error_message = $php_upload_errors[$error_code] ?? "Unknown upload error for material (Code: {$error_code}).";
                $_SESSION['error_message'] = ($_SESSION['error_message'] ?? "") . " " . $specific_error_message;
            }
            end_material_processing_block:;
        }

        $pdo->commit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = "Database error: " . $e->getMessage() . " (Code: " . $e->getCode() . " at line " . $e->getLine() . ")";
        
        header("Location: " . $base_url . "teacher-dashboard.php"); // Redirect on DB error
        exit();
    } catch (Exception $e) { // Catch any other general exceptions
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = "General error: " . $e->getMessage();
        header("Location: " . $base_url . "teacher-dashboard.php");
        exit();
    }
    
    header("Location: " . $base_url . "teacher-dashboard.php");
    exit();
} else {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: " . $base_url . "teacher-dashboard.php");
    exit();
}
?>