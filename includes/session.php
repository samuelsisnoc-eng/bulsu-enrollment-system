<?php
// includes/session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /enrollment_system/admin/login.php');
        exit();
    }
}

function requireStudent() {
    if (!isset($_SESSION['student_id'])) {
        header('Location: /enrollment_system/student/login.php');
        exit();
    }
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function isStudentLoggedIn() {
    return isset($_SESSION['student_id']);
}

function sanitize($conn, $input) {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($input))));
}
?>
