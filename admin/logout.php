<?php
require_once '../includes/session.php';
session_destroy();
header('Location: /enrollment_system/admin/login.php');
exit();
?>
