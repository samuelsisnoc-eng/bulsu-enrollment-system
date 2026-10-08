<?php
// student/ajax/submit_enrollment.php
require_once '../../includes/session.php';
requireStudent();
require_once '../../config/database.php';

header('Content-Type: application/json');

$conn        = getConnection();
$studentId   = $_SESSION['student_id'];
$academicYear = sanitize($conn, $_POST['academic_year'] ?? '');
$semester     = sanitize($conn, $_POST['semester'] ?? '');
$subjects     = sanitize($conn, $_POST['subjects'] ?? '');

if (empty($academicYear) || empty($semester)) {
    echo json_encode(['success' => false, 'message' => 'Please select academic year and semester.']);
    exit();
}

if (empty($subjects)) {
    echo json_encode(['success' => false, 'message' => 'Please select at least one subject.']);
    exit();
}

// Always allow new submission — just INSERT a new enrollment row
$sql = "INSERT INTO enrollments (student_id, academic_year, semester, subjects, status)
        VALUES ($studentId, '$academicYear', '$semester', '$subjects', 'Pending')";

if ($conn->query($sql)) {
    // Create notification for new enrollment
    $student = $conn->query("SELECT student_no, last_name, first_name, middle_name, course FROM students WHERE id = $studentId")->fetch_assoc();
    $studentName = $student['last_name'] . ', ' . $student['first_name'] . ' ' . ($student['middle_name'] ?? '');
    $studentNo = $student['student_no'];
    $course = $student['course'];

    $conn->query("INSERT INTO notifications (student_id, student_no, student_name, course, enrollment_date) VALUES ($studentId, '$studentNo', '$studentName', '$course', NOW())");

    echo json_encode(['success' => true, 'message' => 'Enrollment submitted successfully! Status: Pending.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit: ' . $conn->error]);
}

$conn->close();
?>