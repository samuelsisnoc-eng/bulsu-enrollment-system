<?php
// student/ajax/update_profile.php
require_once '../../includes/session.php';
requireStudent();
require_once '../../config/database.php';

header('Content-Type: application/json');

$conn = getConnection();
$studentId = (int)$_SESSION['student_id'];

$firstName  = sanitize($conn, $_POST['first_name'] ?? '');
$lastName   = sanitize($conn, $_POST['last_name'] ?? '');
$middleName = sanitize($conn, $_POST['middle_name'] ?? '');
$email      = sanitize($conn, $_POST['email'] ?? '');
$phone      = sanitize($conn, $_POST['phone'] ?? '');
$gender     = sanitize($conn, $_POST['gender'] ?? '');
$birthdate  = sanitize($conn, $_POST['birthdate'] ?? '');
$address    = sanitize($conn, $_POST['address'] ?? '');
$course     = sanitize($conn, $_POST['course'] ?? '');
$year       = sanitize($conn, $_POST['year_level'] ?? '');
$newPass    = $_POST['new_password'] ?? '';
$confirmPass= $_POST['confirm_password'] ?? '';

if (empty($firstName) || empty($lastName) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'First name, last name, and email are required.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit();
}

$emailCheck = $conn->query("SELECT id FROM students WHERE email='$email' AND id != $studentId");
if ($emailCheck && $emailCheck->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already used by another account.']);
    exit();
}

if (!empty($newPass)) {
    if (strlen($newPass) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
        exit();
    }
    if ($newPass !== $confirmPass) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit();
    }
    $hashedPass = password_hash($newPass, PASSWORD_DEFAULT);
    $sql = "UPDATE students SET first_name='$firstName', last_name='$lastName', middle_name='$middleName', email='$email', phone='$phone', gender='$gender', birthdate='$birthdate', address='$address', course='$course', year_level='$year', password='$hashedPass' WHERE id=$studentId";
} else {
    $sql = "UPDATE students SET first_name='$firstName', last_name='$lastName', middle_name='$middleName', email='$email', phone='$phone', gender='$gender', birthdate='$birthdate', address='$address', course='$course', year_level='$year' WHERE id=$studentId";
}

if ($conn->query($sql)) {
    $_SESSION['student_name'] = $firstName . ' ' . $lastName;
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $conn->error]);
}

$conn->close();
?>