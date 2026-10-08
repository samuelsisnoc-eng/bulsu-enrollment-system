<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit();
}

$conn = getConnection();
$conn->query("CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT,
    course_code VARCHAR(20),
    section VARCHAR(10),
    subject_id INT,
    academic_year VARCHAR(20) DEFAULT '2024-2025',
    semester VARCHAR(20) DEFAULT '1st',
    day_of_week VARCHAR(10),
    start_time TIME,
    end_time TIME,
    room_no VARCHAR(20),
    professor_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
)");

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $programId = intval($_POST['program_id'] ?? 0);
    $courseCode = $conn->real_escape_string(trim($_POST['course_code'] ?? ''));
    $section = $conn->real_escape_string(trim($_POST['section'] ?? ''));
    $subjectId = intval($_POST['subject_id'] ?? 0);
    $dayOfWeek = $conn->real_escape_string(trim($_POST['day_of_week'] ?? ''));
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $professorName = $conn->real_escape_string(trim($_POST['professor_name'] ?? ''));
    $roomNo = $conn->real_escape_string(trim($_POST['room_no'] ?? ''));

    if (empty($programId) || empty($courseCode) || empty($section) || empty($dayOfWeek) || empty($startTime) || empty($endTime) || empty($professorName) || empty($roomNo)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
        exit();
    }

    $subjectVal = $subjectId > 0 ? $subjectId : 'NULL';
    if ($conn->query("INSERT INTO schedules (program_id, course_code, section, subject_id, day_of_week, start_time, end_time, professor_name, room_no) VALUES ($programId, '$courseCode', '$section', $subjectVal, '$dayOfWeek', '$startTime', '$endTime', '$professorName', '$roomNo')")) {
        echo json_encode(['success' => true, 'message' => 'Schedule added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} elseif ($action === 'edit') {
    $id = intval($_POST['schedule_id'] ?? 0);
    $programId = intval($_POST['program_id'] ?? 0);
    $courseCode = $conn->real_escape_string(trim($_POST['course_code'] ?? ''));
    $section = $conn->real_escape_string(trim($_POST['section'] ?? ''));
    $subjectId = intval($_POST['subject_id'] ?? 0);
    $dayOfWeek = $conn->real_escape_string(trim($_POST['day_of_week'] ?? ''));
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $professorName = $conn->real_escape_string(trim($_POST['professor_name'] ?? ''));
    $roomNo = $conn->real_escape_string(trim($_POST['room_no'] ?? ''));

    $subjectVal = $subjectId > 0 ? $subjectId : 'NULL';
    if ($conn->query("UPDATE schedules SET program_id=$programId, course_code='$courseCode', section='$section', subject_id=$subjectVal, day_of_week='$dayOfWeek', start_time='$startTime', end_time='$endTime', professor_name='$professorName', room_no='$roomNo' WHERE id=$id")) {
        echo json_encode(['success' => true, 'message' => 'Schedule updated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} elseif ($action === 'delete') {
    $id = intval($_POST['schedule_id'] ?? 0);
    if ($conn->query("DELETE FROM schedules WHERE id=$id")) {
        echo json_encode(['success' => true, 'message' => 'Schedule deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

$conn->close();
?>