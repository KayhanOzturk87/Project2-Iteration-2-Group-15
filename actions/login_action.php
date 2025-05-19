<?php
require_once '../config/db_connect.php';
session_start(); 

$base_url = '/'; 
$errors = [];

// check if form is submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['accountType']; 
    $identifier = trim($_POST['loginInput']); 
    $password = $_POST['password'];

    // check if any field is empty, if yes then put error
    if (empty($role)) $errors[] = "Account type is required.";
    if (empty($identifier)) $errors[] = ($role === 'student') ? "School ID is required." : "Email is required.";
    if (empty($password)) $errors[] = "Password is required.";

    // if no error, then check login details
    if (empty($errors)) {
        try {
            // select right query depending on student or teacher
            if ($role === 'student') {
                $stmt = $pdo->prepare("SELECT * FROM Users WHERE school_id = ? AND role = 'student'");
            } elseif ($role === 'teacher') {
                $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ? AND role = 'teacher'");
            } else {
                $errors[] = "Invalid account type specified.";
                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                header("Location: /login.php");
                exit();
            }
            
            $stmt->execute([$identifier]);
            $user = $stmt->fetch();

            // if user found and password correct, then login
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                if ($role === 'student') $_SESSION['user_school_id'] = $user['school_id'];
                else $_SESSION['user_email'] = $user['email'];

                // go to right dashboard after login
                if ($user['role'] === 'student') {
                    header("Location: /student-dashboard.php");
                } elseif ($user['role'] === 'teacher') {
                    header("Location: /teacher-dashboard.php");
                }
                exit();
            } else {
                $errors[] = "Invalid credentials or account type mismatch.";
            }
        } catch (PDOException $e) {
            // if any error from database
            error_log("Database error during login: " . $e->getMessage());
            $errors[] = "A database error occurred. Please try again later.";
        }
    }

    // if there is any error, send back to login page with form data
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST; 
        header("Location: /login.php");
        exit();
    }

} else {
    // if someone come without post request, redirect to login
    header("Location: /login.php");
    exit();
}
?>