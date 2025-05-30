<?php
session_start();
require_once '../config/db_connect.php';
header('Content-Type: application/json');

$base_url = '/'; // Ensure this matches

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$teacher_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$material_id = $data['material_id'] ?? null;

if (!$material_id) {
    echo json_encode(['success' => false, 'message' => 'Material ID not provided.']);
    exit();
}

try {
    // First, get the file_path to delete the actual file from server
    $stmt_get = $pdo->prepare("SELECT file_path, course_id FROM CourseMaterials WHERE material_id = ?");
    $stmt_get->execute([$material_id]);
    $material = $stmt_get->fetch();

    if (!$material) {
        echo json_encode(['success' => false, 'message' => 'Material not found.']);
        exit();
    }
    
    // Check if the teacher owns the course this material belongs to (optional stricter check)
    $stmt_course_owner = $pdo->prepare("SELECT instructor_id FROM Courses WHERE course_id = ?");
    $stmt_course_owner->execute([$material['course_id']]);
    $course_owner = $stmt_course_owner->fetch();

    if (!$course_owner || $course_owner['instructor_id'] != $teacher_id) {
         echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this material.']);
        exit();
    }


    $stmt_delete = $pdo->prepare("DELETE FROM CourseMaterials WHERE material_id = ? AND uploader_id = ?");
    // Or, if only course owner can delete:
    // $stmt_delete = $pdo->prepare("DELETE cm FROM CourseMaterials cm JOIN Courses c ON cm.course_id = c.course_id WHERE cm.material_id = ? AND c.instructor_id = ?");
    
    $deleted = $stmt_delete->execute([$material_id, $teacher_id]); // Ensure teacher deleting is the uploader

    if ($deleted && $stmt_delete->rowCount() > 0) {
        // Delete the actual file
        $file_to_delete = "../uploads/materials/" . $material['file_path']; // Assuming file_path stores only the unique filename
        if (file_exists($file_to_delete)) {
            @unlink($file_to_delete);
        }
        echo json_encode(['success' => true, 'message' => 'Material deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not delete material or permission denied.']);
    }

} catch (PDOException $e) {
    error_log("Error deleting material: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
exit();
?>