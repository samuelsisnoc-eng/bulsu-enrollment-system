<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit();
}

$conn = getConnection();
$conn->query("CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL,
    subject_name VARCHAR(150) NOT NULL,
    units INT NOT NULL DEFAULT 3,
    program_id INT,
    college_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE,
    UNIQUE KEY unique_subject_program (subject_code, program_id)
)");

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $code = $conn->real_escape_string(trim($_POST['subject_code'] ?? ''));
    $name = $conn->real_escape_string(trim($_POST['subject_name'] ?? ''));
    $units = intval($_POST['units'] ?? 3);
    $programId = intval($_POST['program_id'] ?? 0);
    $collegeId = intval($_POST['college_id'] ?? 0);

    if (empty($code) || empty($name) || $programId <= 0 || $collegeId <= 0) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    if ($conn->query("INSERT INTO subjects (subject_code, subject_name, units, program_id, college_id) VALUES ('$code', '$name', $units, $programId, $collegeId)")) {
        echo json_encode(['success' => true, 'message' => 'Subject added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} elseif ($action === 'edit') {
    $id = intval($_POST['subject_id'] ?? 0);
    $code = $conn->real_escape_string(trim($_POST['subject_code'] ?? ''));
    $name = $conn->real_escape_string(trim($_POST['subject_name'] ?? ''));
    $units = intval($_POST['units'] ?? 3);
    $programId = intval($_POST['program_id'] ?? 0);
    $collegeId = intval($_POST['college_id'] ?? 0);

    if ($conn->query("UPDATE subjects SET subject_code='$code', subject_name='$name', units=$units, program_id=$programId, college_id=$collegeId WHERE id=$id")) {
        echo json_encode(['success' => true, 'message' => 'Subject updated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} elseif ($action === 'delete') {
    $id = intval($_POST['subject_id'] ?? 0);
    if ($conn->query("DELETE FROM subjects WHERE id=$id")) {
        echo json_encode(['success' => true, 'message' => 'Subject deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

$conn->close();
?>