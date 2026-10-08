<?php
require_once '../includes/session.php';
requireStudent();
require_once '../config/database.php';
$conn = getConnection();

$studentId = $_SESSION['student_id'];
$student   = $conn->query("SELECT * FROM students WHERE id = $studentId")->fetch_assoc();

// Get ALL enrollments for this student
$enrollmentsResult = $conn->query("SELECT * FROM enrollments WHERE student_id = $studentId ORDER BY submitted_at DESC");
$allEnrollments = [];
while ($row = $enrollmentsResult->fetch_assoc()) {
    $allEnrollments[] = $row;
}

// Latest enrollment for status card
$enrollment = !empty($allEnrollments) ? $allEnrollments[0] : null;

// Get all APPROVED subjects across all enrollments
$approvedSubjects = [];
foreach ($allEnrollments as $e) {
    if ($e['status'] === 'Approved' && !empty($e['subjects'])) {
        $subs = array_map('trim', explode(',', $e['subjects']));
        foreach ($subs as $sub) {
            if (!empty($sub)) $approvedSubjects[] = [
                'subject'       => $sub,
                'academic_year' => $e['academic_year'],
                'semester'      => $e['semester'],
            ];
        }
    }
}

$yearLabels = ['1'=>'1st Year','2'=>'2nd Year','3'=>'3rd Year','4'=>'4th Year'];
$yearDisplay = $yearLabels[$student['year_level']] ?? $student['year_level'].' Year';

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard – BulSU Enrollment</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
.subject-table { width:100%; border-collapse:collapse; margin-top:8px; }
.subject-table th {
    padding: 10px 14px;
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--text-muted);
    border-bottom: 1px solid var(--card-border);
}
.subject-table td {
    padding: 12px 14px;
    font-size: 14px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.subject-table tr:last-child td { border-bottom: none; }
.subject-table tr:hover td { background: rgba(255,255,255,0.02); }
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
            <span style="color:var(--text-muted);font-size:14px">Hi, <?= htmlspecialchars($student['first_name']) ?> 👋</span>
            <a href="/enrollment_system/student/logout.php" class="nav-link btn-danger">Logout</a>
        </div>
    </nav>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Student</div>
                <a href="/enrollment_system/student/dashboard.php" class="sidebar-link active"><span class="icon">🏠</span> Dashboard</a>
                <a href="/enrollment_system/student/enroll.php" class="sidebar-link"><span class="icon">📝</span> Enroll Now</a>
                <a href="/enrollment_system/student/status.php" class="sidebar-link"><span class="icon">🔍</span> My Status</a>
                <a href="/enrollment_system/student/profile.php" class="sidebar-link"><span class="icon">👤</span> My Profile</a>                <a href="/enrollment_system/student/calendar.php" class="sidebar-link"><span class="icon">📅</span> Academic Calendar</a>
                <a href="/enrollment_system/student/schedule.php" class="sidebar-link"><span class="icon">🕒</span> Class Schedule</a>            </div>
        </aside>
        <main class="main-content">
            <div class="page-header">
                <h1>Welcome, <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>!</h1>
                <p><?= htmlspecialchars($student['course']) ?> – <?= $yearDisplay ?> | Student No. <?= htmlspecialchars($student['student_no']) ?></p>
            </div>

            <!-- Stats -->
            <div class="stats-grid" style="margin-bottom:32px">
                <div class="stat-card <?= !$enrollment ? 'blue' : ($enrollment['status'] === 'Approved' ? 'green' : ($enrollment['status'] === 'Rejected' ? 'red' : 'gold')) ?>">
                    <div class="stat-number" style="font-size:28px">
                        <?= $enrollment ? $enrollment['status'] : 'Not Enrolled' ?>
                    </div>
                    <div class="stat-label">Latest Enrollment Status</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-number" style="font-size:22px;margin-bottom:4px"><?= $yearDisplay ?></div>
                    <div class="stat-label">Year Level</div>
                </div>
                <div class="stat-card gold">
                    <div class="stat-number"><?= htmlspecialchars($student['course']) ?></div>
                    <div class="stat-label">Course</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-number"><?= count($approvedSubjects) ?></div>
                    <div class="stat-label">Approved Subjects</div>
                </div>
            </div>

            <!-- Approved Subjects Table -->
            <?php if (!empty($approvedSubjects)): ?>
            <div class="card" style="margin-bottom:24px">
                <div class="card-header">
                    <h2>✅ Approved Subjects</h2>
                    <span class="badge badge-approved"><?= count($approvedSubjects) ?> subject<?= count($approvedSubjects) > 1 ? 's' : '' ?></span>
                </div>
                <div class="card-body" style="padding:0">
                    <table class="subject-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject Code</th>
                                <th>Academic Year</th>
                                <th>Semester</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($approvedSubjects as $i => $sub): ?>
                        <tr>
                            <td style="color:var(--text-muted);font-size:12px"><?= $i + 1 ?></td>
                            <td style="font-family:'Syne',sans-serif;font-weight:600"><?= htmlspecialchars($sub['subject']) ?></td>
                            <td style="color:var(--text-muted)"><?= htmlspecialchars($sub['academic_year']) ?></td>
                            <td>
                                <span class="badge" style="background:rgba(0,184,148,0.12);color:var(--green);border:1px solid rgba(0,184,148,0.3)">
                                    <?= htmlspecialchars($sub['semester']) ?> Sem
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enrollment History - one row per subject with status -->
            <?php
            $historyRows = [];
            foreach ($allEnrollments as $e) {
                if (!empty($e['subjects'])) {
                    $subs = array_map('trim', explode(',', $e['subjects']));
                    foreach ($subs as $sub) {
                        if (!empty($sub)) {
                            $historyRows[] = [
                                'subject'       => $sub,
                                'academic_year' => $e['academic_year'],
                                'semester'      => $e['semester'],
                                'status'        => $e['status'],
                                'remarks'       => $e['remarks'] ?? '',
                                'submitted_at'  => $e['submitted_at'],
                            ];
                        }
                    }
                }
            }
            ?>
            <?php if (!empty($historyRows)): ?>
            <div class="card" style="margin-bottom:24px">
                <div class="card-header">
                    <h2>My Enrolled Subjects</h2>
                    <a href="/enrollment_system/student/enroll.php" class="btn btn-primary btn-sm">+ Enroll More</a>
                </div>
                <div class="card-body" style="padding:0">
                    <table class="subject-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Date Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($historyRows as $i => $row): ?>
                        <tr>
                            <td style="color:var(--text-muted);font-size:12px"><?= $i + 1 ?></td>
                            <td style="font-family:'Syne',sans-serif;font-weight:600"><?= htmlspecialchars($row['subject']) ?></td>
                            <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($row['academic_year']) ?></td>
                            <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($row['semester']) ?> Sem</td>
                            <td>
                                <?php
                                $statusIcon = ['Approved'=>'✅','Pending'=>'⏳','Rejected'=>'❌'];
                                $icon = $statusIcon[$row['status']] ?? '';
                                ?>
                                <span class="badge badge-<?= strtolower($row['status']) ?>">
                                    <?= $icon ?> <?= $row['status'] ?>
                                </span>
                            </td>
                            <td style="color:var(--text-muted);font-size:13px">
                                <?= !empty($row['remarks']) ? htmlspecialchars($row['remarks']) : '—' ?>
                            </td>
                            <td style="color:var(--text-muted);font-size:12px"><?= date('M d, Y', strtotime($row['submitted_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-body" style="text-align:center;padding:48px">
                    <h2 style="margin-bottom:8px">No Enrollment Submitted</h2>
                    <p style="color:var(--text-muted);margin-bottom:24px">You haven't submitted an enrollment request yet.</p>
                    <a href="/enrollment_system/student/enroll.php" class="btn btn-primary">Start Enrollment →</a>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>
<script src="/enrollment_system/assets/js/main.js"></script>
</body>
</html>