<?php
// student/ajax/login.php
require_once '../../includes/session.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$conn = getConnection();
$studentNo = sanitize($conn, $_POST['student_no'] ?? '');
$password  = $_POST['password'] ?? '';

if (empty($studentNo) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM students WHERE student_no = ?");
$stmt->bind_param("s", $studentNo);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if ($student && password_verify($password, $student['password'])) {
    $_SESSION['student_id']   = $student['id'];
    $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
    echo json_encode(['success' => true, 'message' => 'Login successful! Redirecting...']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid student number or password.']);
}

$stmt->close();
$conn->close();
?>
