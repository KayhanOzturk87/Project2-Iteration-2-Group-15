<?php
session_start();
require_once '../config/db_connect.php';
$base_url = '/'; // Ensure this matches

if (!isset($_SESSION['user_id'])) { // Or specific role check if needed
    $_SESSION['error_message'] = "You must be logged in to download materials.";
    header("Location: " . $base_url . "login.php");
    exit();
}

if (!isset($_GET['material_id'])) {
    die("Material ID not specified.");
}

$material_id = $_GET['material_id'];
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT cm.file_name, cm.file_path, cm.file_type, cm.course_id 
                           FROM CourseMaterials cm
                           WHERE cm.material_id = ?");
    $stmt->execute([$material_id]);
    $material = $stmt->fetch();

    if (!$material) {
        die("Material not found.");
    }

    if ($_SESSION['user_role'] === 'student') {
        $stmt_enroll_check = $pdo->prepare("SELECT 1 FROM Enrollments WHERE user_id = ? AND course_id = ?");
        $stmt_enroll_check->execute([$user_id, $material['course_id']]);
        if (!$stmt_enroll_check->fetch()) {
            die("Access Denied: You are not enrolled in the course this material belongs to.");
        }
    }


    $file_on_server = "../uploads/materials/" . $material['file_path']; // Assuming file_path stores only the unique filename

    if (file_exists($file_on_server)) {
        header('Content-Description: File Transfer');
        $content_type = $material['file_type'] ?: mime_content_type($file_on_server) ?: 'application/octet-stream';
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: attachment; filename="' . basename($material['file_name']) . '"'); // Use original filename
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_on_server));
        flush(); 
        readfile($file_on_server);
        exit;
    } else {
        die("File not found on server. Please contact support.");
    }

} catch (PDOException $e) {
    error_log("Error downloading material: " . $e->getMessage());
    die("Error processing download request. Please try again.");
}
?>