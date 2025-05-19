<?php
require_once '../config/db_connect.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$base_url = '/'; 
$errors = [];
$name = '';
$email_input = '';
$role = '';
$school_id_input = null;

// check if form is submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['fullname']);
    $role = $_POST['accountType'] ?? '';
    $email_input = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    
    // if student then also collect school id
    if ($role === 'student') {
        $school_id_input = trim($_POST['school_id']);
    }

    // check required fields are filled or not
    if (empty($name)) $errors[] = "Full name is required.";
    if (empty($role)) $errors[] = "Account type is required.";
    if (empty($email_input)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // school id is only needed for student
    if ($role === 'student' && empty($school_id_input)) {
        $errors[] = "School ID is required for students.";
    }

    // password validation steps
    if (empty($password)) $errors[] = "Password is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    // check if email or school id already used
    if (empty($errors)) {
        $stmt_email = $pdo->prepare("SELECT user_id FROM Users WHERE email = ?");
        $stmt_email->execute([$email_input]);
        if ($stmt_email->fetch()) {
            $errors[] = "This email address is already registered.";
        }

        if ($role === 'student' && !empty($school_id_input)) {
            $stmt_school_id = $pdo->prepare("SELECT user_id FROM Users WHERE school_id = ? AND role = 'student'");
            $stmt_school_id->execute([$school_id_input]);
            if ($stmt_school_id->fetch()) {
                $errors[] = "This School ID is already registered for a student account.";
            }
        }
    }

    // if no error then save user to database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            if ($role === 'student') {
                $sql = "INSERT INTO Users (name, email, password, role, school_id) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $email_input, $hashed_password, $role, $school_id_input]);
            } else { 
                $sql = "INSERT INTO Users (name, email, password, role, school_id) VALUES (?, ?, ?, ?, NULL)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $email_input, $hashed_password, $role]);
            }

            // after registration success redirect to login
            $_SESSION['message'] = "Registration successful! Please login.";
            header("Location: " . $base_url . "login.php");
            exit();

        } catch (PDOException $e) {
            // handle duplicate entry or general error
            if ($e->getCode() == 23000) { 
                 $errors[] = "An account with this email or School ID might already exist.";
            } else {
                $errors[] = "Database error during registration. Please try again later.";
            }
        }
    }

    // if any error, send back to signup page with old data
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $base_url . "signup.php");
        exit();
    }
} else {
    // if user come without POST request just go back
    header("Location: " . $base_url . "signup.php");
    exit();
}
?>