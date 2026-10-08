<?php
// admin/ajax/delete_enrollment.php
require_once '../../includes/session.php';
requireAdmin();
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$conn = getConnection();
$enrollmentId = intval($_POST['enrollment_id'] ?? 0);

if ($enrollmentId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid enrollment ID.']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM enrollments WHERE id = ?");
$stmt->bind_param('i', $enrollmentId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Enrollment deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Deletion failed.']);
}

$stmt->close();
$conn->close();
