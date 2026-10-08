<?php
// admin/ajax/delete_student.php
require_once '../../includes/session.php';
requireAdmin();
require_once '../../config/database.php';

header('Content-Type: application/json');

$conn = getConnection();
$id = intval($_POST['student_id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
    exit();
}

// Delete related records first (enrollments, notifications)
$conn->query("DELETE FROM enrollments WHERE student_id = $id");
$conn->query("DELETE FROM notifications WHERE student_id = $id");

// Delete the student
$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Student deleted.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Delete failed.']);
}

$stmt->close();
$conn->close();
?>
