<?php
session_start();
require_once '../config/db_connect.php';
header('Content-Type: application/json');

$base_url = '/';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
    try {
        $stmt = $pdo->prepare("SELECT material_id, file_name, title, description, upload_date 
                               FROM CourseMaterials 
                               WHERE course_id = ? ORDER BY upload_date DESC");
        $stmt->execute([$course_id]);
        $materials = $stmt->fetchAll();
        echo json_encode($materials);
    } catch (PDOException $e) {
        error_log("Error fetching course materials: " . $e->getMessage());
        echo json_encode(['error' => 'Database error fetching materials.']);
    }
} else {
    echo json_encode(['error' => 'Course ID not provided.']);
}
exit();
?>