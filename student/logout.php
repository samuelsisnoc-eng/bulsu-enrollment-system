<?php
require_once '../includes/session.php';
session_destroy();
header('Location: /enrollment_system/student/login.php');
exit();
?>
