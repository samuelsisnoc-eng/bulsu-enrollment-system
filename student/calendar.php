<?php
require_once '../includes/session.php';
requireStudent();
require_once '../config/database.php';

$conn = getConnection();

// Get student's program and course
$studentId = intval($_SESSION['student_id']);
$student = $conn->query("SELECT s.*, p.program_name, p.program_code FROM students s LEFT JOIN programs p ON s.program_id = p.id WHERE s.id = $studentId")->fetch_assoc();

$courseCode = $conn->real_escape_string($student['course'] ?? '');
$programId = isset($student['program_id']) ? intval($student['program_id']) : 0;

if ($programId === 0 && $courseCode !== '') {
    $programRow = $conn->query("SELECT id, program_name FROM programs WHERE program_code = '$courseCode' LIMIT 1");
    if ($programRow && $programRow->num_rows > 0) {
        $programData = $programRow->fetch_assoc();
        $programId = intval($programData['id']);
        if (empty($student['program_name'])) {
            $student['program_name'] = $programData['program_name'];
        }
    }
}

$where = "course_code = '$courseCode'";
if ($programId > 0) {
    $where = "program_id = $programId AND course_code = '$courseCode'";
}

// Get academic calendar for this program/course
$calendar = $conn->query("
    SELECT * FROM academic_calendar
    WHERE $where
    ORDER BY event_date ASC
");

$events = [];
while ($row = $calendar->fetch_assoc()) {
    $events[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academic Calendar – BulSU Enrollment</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>
<body>
<div class="wrapper">
    <nav class="navbar">
        <a href="/enrollment_system/index.php" class="navbar-brand">
            <div class="logo-icon">🎓</div>
            <span>BulSU Enrollment</span>
        </a>
        <div class="navbar-nav">
            <span style="color:var(--text-muted);font-size:14px">Hi, <?= htmlspecialchars($_SESSION['student_name'] ?? 'Student') ?></span>
            <a href="/enrollment_system/student/logout.php" class="nav-link btn-danger">Logout</a>
        </div>
    </nav>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Student Portal</div>
                <a href="/enrollment_system/student/dashboard.php" class="sidebar-link"><span class="icon">📊</span> Dashboard</a>
                <a href="/enrollment_system/student/profile.php" class="sidebar-link"><span class="icon">👤</span> Profile</a>
                <a href="/enrollment_system/student/enroll.php" class="sidebar-link"><span class="icon">📝</span> Enroll</a>
                <a href="/enrollment_system/student/status.php" class="sidebar-link"><span class="icon">📋</span> Status</a>
                <a href="/enrollment_system/student/calendar.php" class="sidebar-link active"><span class="icon">📅</span> Academic Calendar</a>
                <a href="/enrollment_system/student/schedule.php" class="sidebar-link"><span class="icon">🕒</span> Class Schedule</a>
            </div>
        </aside>
        <main class="main-content">
            <div class="page-header">
                <h1>Academic Calendar</h1>
                <p>Your academic schedule for <?= htmlspecialchars($student['program_name'] ?? 'your program') ?> - <?= htmlspecialchars($student['course']) ?></p>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if (!empty($events)): ?>
                        <div class="timeline">
                            <?php foreach ($events as $event): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h3 class="timeline-title"><?= htmlspecialchars($event['event_title']) ?></h3>
                                    <p class="timeline-date">
                                        <?= date('F j, Y', strtotime($event['event_date'])) ?>
                                        <?php if ($event['semester']): ?>
                                        <span class="badge" style="background:rgba(116,185,255,0.12);color:#74b9ff;border:1px solid rgba(116,185,255,0.25);margin-left:8px;">
                                            <?= htmlspecialchars($event['semester']) ?> Semester
                                        </span>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($event['event_description']): ?>
                                    <p class="timeline-description"><?= htmlspecialchars($event['event_description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align:center;padding:40px;color:var(--text-muted)">
                            <div style="font-size:48px;margin-bottom:16px">📅</div>
                            <h3>No Academic Events</h3>
                            <p>Your academic calendar will be updated soon.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--primary);
    border: 3px solid var(--bg);
}

.timeline-content {
    background: var(--bg-card);
    padding: 20px;
    border-radius: 8px;
    border: 1px solid var(--border);
}

.timeline-title {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--text);
}

.timeline-date {
    margin: 0 0 12px 0;
    font-size: 14px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
}

.timeline-description {
    margin: 0;
    color: var(--text);
    line-height: 1.5;
}
</style>

<script src="/enrollment_system/assets/js/main.js"></script>
</body>
</html>