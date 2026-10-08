<?php
// admin/ajax/update_student.php
require_once '../../includes/session.php';
requireAdmin();
require_once '../../config/database.php';

header('Content-Type: application/json');

$conn = getConnection();
$id         = intval($_POST['student_id'] ?? 0);
$firstName  = sanitize($conn, $_POST['first_name'] ?? '');
$lastName   = sanitize($conn, $_POST['last_name'] ?? '');
$middleName = sanitize($conn, $_POST['middle_name'] ?? '');
$email      = sanitize($conn, $_POST['email'] ?? '');
$phone      = sanitize($conn, $_POST['phone'] ?? '');
$course     = sanitize($conn, $_POST['course'] ?? '');
$year       = sanitize($conn, $_POST['year_level'] ?? '');
$section    = sanitize($conn, $_POST['section'] ?? '');
$gender     = sanitize($conn, $_POST['gender'] ?? '');

if (!$id || empty($firstName) || empty($lastName) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing.']);
    exit();
}

$stmt = $conn->prepare("UPDATE students SET first_name=?, last_name=?, middle_name=?, email=?, phone=?, course=?, year_level=?, section=?, gender=? WHERE id=?");
$stmt->bind_param("sssssssssi", $firstName, $lastName, $middleName, $email, $phone, $course, $year, $section, $gender, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Student updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
