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
$conn->query("CREATE TABLE IF NOT EXISTS colleges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    college_code VARCHAR(20) NOT NULL UNIQUE,
    college_name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if ($action === 'add') {
    $code = $conn->real_escape_string(trim($_POST['college_code'] ?? ''));
    $name = $conn->real_escape_string(trim($_POST['college_name'] ?? ''));
    if (empty($code) || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'College code and name are required.']);
        exit();
    }
    if ($conn->query("INSERT INTO colleges (college_code, college_name) VALUES ('$code', '$name')")) {
        echo json_encode(['success' => true, 'message' => 'College added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} elseif ($action === 'edit') {
    $id   = intval($_POST['college_id'] ?? 0);
    $code = $conn->real_escape_string(trim($_POST['college_code'] ?? ''));
    $name = $conn->real_escape_string(trim($_POST['college_name'] ?? ''));
    if ($conn->query("UPDATE colleges SET college_code='$code', college_name='$name' WHERE id=$id")) {
        echo json_encode(['success' => true, 'message' => 'College updated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} elseif ($action === 'delete') {
    $id = intval($_POST['college_id'] ?? 0);
    if ($conn->query("DELETE FROM colleges WHERE id=$id")) {
        echo json_encode(['success' => true, 'message' => 'College deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }

} elseif ($action === 'get_colleges') {
    $result = $conn->query("SELECT id, college_code, college_name FROM colleges ORDER BY college_code ASC");
    $colleges = [];
    while ($row = $result->fetch_assoc()) {
        $colleges[] = $row;
    }
    echo json_encode(['success' => true, 'colleges' => $colleges]);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

$conn->close();
?>