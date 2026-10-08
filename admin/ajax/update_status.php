<?php
// admin/ajax/update_status.php
require_once '../../includes/session.php';
requireAdmin();
require_once '../../config/database.php';

header('Content-Type: application/json');

$conn = getConnection();
$enrollmentId = intval($_POST['enrollment_id'] ?? 0);
$status       = sanitize($conn, $_POST['status'] ?? '');
$remarks      = sanitize($conn, $_POST['remarks'] ?? '');

if (!in_array($status, ['Approved', 'Rejected', 'Pending'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit();
}

$stmt = $conn->prepare("UPDATE enrollments SET status = ?, remarks = ? WHERE id = ?");
$stmt->bind_param("ssi", $status, $remarks, $enrollmentId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => "Enrollment $status successfully."]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed.']);
}

$stmt->close();
$conn->close();
?>