<?php
// student/ajax/check_status.php
require_once '../../includes/session.php';
requireStudent();
require_once '../../config/database.php';

header('Content-Type: application/json');

$conn = getConnection();
$studentId = $_SESSION['student_id'];
$result = $conn->query("SELECT status, remarks, academic_year, semester FROM enrollments WHERE student_id = $studentId ORDER BY id DESC LIMIT 1");
$enrollment = $result->fetch_assoc();

if ($enrollment) {
    echo json_encode(['success' => true, 'status' => $enrollment['status'], 'remarks' => $enrollment['remarks'], 'academic_year' => $enrollment['academic_year'], 'semester' => $enrollment['semester']]);
} else {
    echo json_encode(['success' => true, 'status' => 'Not Submitted', 'remarks' => '']);
}

$conn->close();
?>