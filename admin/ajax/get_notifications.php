<?php
// admin/ajax/get_notifications.php
require_once '../../includes/session.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit();
}

$conn = getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'count') {
    // Get unread notification count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE is_read = FALSE");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode(['success' => true, 'count' => $result['count']]);
    
} elseif ($action === 'list') {
    // Get list of notifications
    $limit = intval($_GET['limit'] ?? 20);
    $stmt = $conn->prepare("SELECT * FROM notifications ORDER BY enrollment_date DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    echo json_encode(['success' => true, 'notifications' => $notifications]);
    
} elseif ($action === 'mark_read') {
    // Mark all notifications as read
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE is_read = FALSE");
    $success = $stmt->execute();
    echo json_encode(['success' => $success]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

$stmt->close();
$conn->close();
?>