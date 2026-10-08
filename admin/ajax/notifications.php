<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit();
}

$conn = getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'get_unread') {
    $result = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE is_read = FALSE");
    $row = $result->fetch_assoc();
    echo json_encode(['success' => true, 'count' => $row['count']]);

} elseif ($action === 'get_unread_details') {
    $result = $conn->query("
        SELECT n.*, s.first_name, s.last_name, s.middle_name
        FROM notifications n
        LEFT JOIN students s ON n.student_id = s.id
        WHERE n.is_read = FALSE
        ORDER BY n.enrollment_date DESC
    ");
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $row['student_name'] = $row['last_name'] . ', ' . $row['first_name'] . ' ' . ($row['middle_name'] ?? '');
        $notifications[] = $row;
    }
    echo json_encode(['success' => true, 'notifications' => $notifications]);

} elseif ($action === 'mark_read') {
    $notificationId = intval($_POST['notification_id'] ?? 0);
    if ($notificationId > 0) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ?");
        $stmt->bind_param("i", $notificationId);
        $success = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>