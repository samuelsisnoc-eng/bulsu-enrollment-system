<?php
// admin/ajax/get_student.php
require_once '../../includes/session.php';
requireAdmin();
require_once '../../config/database.php';

header('Content-Type: application/json');

$conn = getConnection();
$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if ($student) {
    unset($student['password']); // Never send password
    echo json_encode(['success' => true, 'student' => $student]);
} else {
    echo json_encode(['success' => false, 'message' => 'Student not found.']);
}

$stmt->close();
$conn->close();
?>
