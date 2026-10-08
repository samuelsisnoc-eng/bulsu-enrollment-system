<?php
require_once '../includes/session.php';
requireStudent();
require_once '../config/database.php';

$conn = getConnection();

// Get student's program and course
$studentId = intval($_SESSION['student_id']);
$student = $conn->query("SELECT s.*, p.program_name, p.program_code FROM students s LEFT JOIN programs p ON s.program_id = p.id WHERE s.id = $studentId")->fetch_assoc();

$courseCode = $conn->real_escape_string($student['course'] ?? '');
$section = $conn->real_escape_string($student['section'] ?? '');
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

$where = "sch.course_code = '$courseCode'";
if ($section !== '') {
    $where .= " AND sch.section = '$section'";
}
if ($programId > 0) {
    $where = "sch.program_id = $programId AND $where";
}

// Get schedule for this program/course/section
$schedule = $conn->query("
    SELECT sch.*, sub.subject_name, sub.units
    FROM schedules sch
    LEFT JOIN subjects sub ON sch.subject_id = sub.id
    WHERE $where
    ORDER BY FIELD(sch.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), sch.start_time ASC
");

$schedules = [];
while ($row = $schedule->fetch_assoc()) {
    $schedules[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Class Schedule – BulSU Enrollment</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
.schedule-table { width: 100%; border-collapse: collapse; }
.schedule-table th, .schedule-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--border);
}
.schedule-table th { background: var(--bg-hover); font-weight: 600; }
.schedule-table tr:hover { background: var(--bg-hover); }
</style>
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
                <a href="/enrollment_system/student/calendar.php" class="sidebar-link"><span class="icon">📅</span> Academic Calendar</a>
                <a href="/enrollment_system/student/schedule.php" class="sidebar-link active"><span class="icon">🕒</span> Class Schedule</a>
            </div>
        </aside>
        <main class="main-content">
            <div class="page-header">
                <h1>Class Schedule</h1>
                <p>Your class schedule for <?= htmlspecialchars($student['program_name'] ?? 'your program') ?> - <?= htmlspecialchars($student['course']) ?> Section <?= htmlspecialchars($student['section'] ?? 'N/A') ?></p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Schedule Receipt</h3>
                    <div style="font-size:14px;color:var(--text-muted)">
                        Student: <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . ($student['middle_name'] ?? '')) ?><br>
                        Student No: <?= htmlspecialchars($student['student_no']) ?><br>
                        Academic Year: 2024-2025 | Semester: 1st
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($schedules)): ?>
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Title</th>
                                    <th>Units</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Professor</th>
                                    <th>Room</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $sch): ?>
                                <tr>
                                    <td style="font-family:'Syne',sans-serif;font-weight:600">
                                        <span class="badge" style="background:rgba(255,107,107,0.12);color:#ff6b6b;border:1px solid rgba(255,107,107,0.25)">
                                            <?= htmlspecialchars($sch['subject_id'] ? 'SUB' . str_pad($sch['subject_id'], 3, '0', STR_PAD_LEFT) : 'TBA') ?>
                                        </span>
                                    </td>
                                    <td style="font-weight:500"><?= htmlspecialchars($sch['subject_name'] ?? 'Subject Name') ?></td>
                                    <td><?= $sch['units'] ?? 3 ?> units</td>
                                    <td>
                                        <span class="badge" style="background:rgba(0,184,148,0.12);color:var(--green);border:1px solid rgba(0,184,148,0.25)">
                                            <?= htmlspecialchars($sch['day_of_week']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('g:i A', strtotime($sch['start_time'])) ?> -
                                        <?= date('g:i A', strtotime($sch['end_time'])) ?>
                                    </td>
                                    <td><?= htmlspecialchars($sch['professor_name'] ?? 'TBA') ?></td>
                                    <td>
                                        <span class="badge" style="background:rgba(116,185,255,0.12);color:#74b9ff;border:1px solid rgba(116,185,255,0.25)">
                                            <?= htmlspecialchars($sch['room_no'] ?? 'TBA') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div style="margin-top:20px;padding:16px;background:var(--bg-hover);border-radius:8px;">
                            <h4 style="margin:0 0 8px 0">Total Units: <?= array_sum(array_column($schedules, 'units')) ?? 0 ?></h4>
                            <p style="margin:0;color:var(--text-muted);font-size:14px">
                                This is your official class schedule. Please arrive 5 minutes before class starts.
                            </p>
                        </div>
                    <?php else: ?>
                        <div style="text-align:center;padding:40px;color:var(--text-muted)">
                            <div style="font-size:48px;margin-bottom:16px">🕒</div>
                            <h3>No Schedule Available</h3>
                            <p>Your class schedule will be posted soon.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="text-align:center;margin-top:20px">
                <button onclick="window.print()" class="btn btn-primary">
                    <span class="icon">🖨️</span> Print Schedule
                </button>
            </div>
        </main>
    </div>
</div>

<script src="/enrollment_system/assets/js/main.js"></script>
</body>
</html>