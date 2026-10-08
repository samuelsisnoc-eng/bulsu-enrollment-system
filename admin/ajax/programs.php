<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (in_array($action, ['add', 'edit', 'delete'])) {
    if (!isset($_SESSION['admin_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not authorized.']);
        exit();
    }
}

$conn = getConnection();
$conn->query("CREATE TABLE IF NOT EXISTS programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_code VARCHAR(20) NOT NULL UNIQUE,
    program_name VARCHAR(150) NOT NULL,
    college_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE SET NULL
)");

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add') {
    $code    = $conn->real_escape_string(trim($_POST['program_code'] ?? ''));
    $name    = $conn->real_escape_string(trim($_POST['program_name'] ?? ''));
    $college = intval($_POST['college_id'] ?? 0);
    if (empty($code) || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Program code and name are required.']);
        exit();
    }
    $cVal = $college > 0 ? $college : 'NULL';
    if ($conn->query("INSERT INTO programs (program_code, program_name, college_id) VALUES ('$code', '$name', $cVal)")) {
        echo json_encode(['success' => true, 'message' => 'Program added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} elseif ($action === 'edit') {
    $id      = intval($_POST['program_id'] ?? 0);
    $code    = $conn->real_escape_string(trim($_POST['program_code'] ?? ''));
    $name    = $conn->real_escape_string(trim($_POST['program_name'] ?? ''));
    $college = intval($_POST['college_id'] ?? 0);
    $cVal    = $college > 0 ? $college : 'NULL';
    if ($conn->query("UPDATE programs SET program_code='$code', program_name='$name', college_id=$cVal WHERE id=$id")) {
        echo json_encode(['success' => true, 'message' => 'Program updated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} elseif ($action === 'delete') {
    $id = intval($_POST['program_id'] ?? 0);
    if ($conn->query("DELETE FROM programs WHERE id=$id")) {
        echo json_encode(['success' => true, 'message' => 'Program deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} elseif ($action === 'get_programs') {
    $collegeId = intval($_GET['college_id'] ?? 0);
    $query = "SELECT id, program_code, program_name FROM programs";
    if ($collegeId > 0) {
        $query .= " WHERE college_id = $collegeId";
    }
    $query .= " ORDER BY program_code ASC";
    $result = $conn->query($query);
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    echo json_encode(['success' => true, 'programs' => $programs]);

} elseif ($action === 'get_programs_by_college_code') {
    $collegeCode = $conn->real_escape_string($_GET['college_code'] ?? '');
    $query = "SELECT p.id, p.program_code, p.program_name FROM programs p
              LEFT JOIN colleges c ON p.college_id = c.id
              WHERE c.college_code = '$collegeCode'
              ORDER BY p.program_code ASC";
    $result = $conn->query($query);
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    echo json_encode(['success' => true, 'programs' => $programs]);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

$conn->close();
?>