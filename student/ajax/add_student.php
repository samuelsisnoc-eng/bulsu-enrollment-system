<?php
// student/ajax/add_student.php
require_once '../../includes/session.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired or unauthorized. Please login again.']);
    exit();
}

$conn = getConnection();

$lastName   = sanitize($conn, $_POST['last_name'] ?? '');
$firstName  = sanitize($conn, $_POST['first_name'] ?? '');
$middleName = sanitize($conn, $_POST['middle_name'] ?? '');
$email      = sanitize($conn, $_POST['email'] ?? '');
$phone      = sanitize($conn, $_POST['phone'] ?? '');
$gender     = sanitize($conn, $_POST['gender'] ?? '');
$birthdate  = sanitize($conn, $_POST['birthdate'] ?? '');
$address    = sanitize($conn, $_POST['address'] ?? '');
$course     = sanitize($conn, $_POST['course'] ?? '');
$year       = sanitize($conn, $_POST['year_level'] ?? '');
$section    = sanitize($conn, $_POST['section'] ?? '');
$studentNo  = sanitize($conn, $_POST['student_no'] ?? '');
$password   = $_POST['password'] ?? '';
$confirm    = $_POST['confirm_password'] ?? '';

// Validation
if (empty($lastName) || empty($firstName) || empty($email) || empty($gender) || empty($course) || empty($year) || empty($studentNo) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit();
}

if ($password !== $confirm) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit();
}

// Check duplicates
$check = $conn->prepare("SELECT id FROM students WHERE student_no = ? OR email = ?");
$check->bind_param("ss", $studentNo, $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Student number or email already registered.']);
    $check->close();
    $conn->close();
    exit();
}
$check->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO students (student_no, last_name, first_name, middle_name, email, phone, gender, birthdate, address, course, year_level, section, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssssssss", $studentNo, $lastName, $firstName, $middleName, $email, $phone, $gender, $birthdate, $address, $course, $year, $section, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Student added successfully! Redirecting...']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add student: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>