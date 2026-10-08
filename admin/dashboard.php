<?php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
$conn = getConnection();

// Stats
$totalStudents  = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$totalEnrollments = $conn->query("SELECT COUNT(*) as c FROM enrollments")->fetch_assoc()['c'];
$totalPending   = $conn->query("SELECT COUNT(*) as c FROM enrollments WHERE status='Pending'")->fetch_assoc()['c'];
$totalApproved  = $conn->query("SELECT COUNT(*) as c FROM enrollments WHERE status='Approved'")->fetch_assoc()['c'];
$totalRejected  = $conn->query("SELECT COUNT(*) as c FROM enrollments WHERE status='Rejected'")->fetch_assoc()['c'];

// Male / Female count
$totalMale   = $conn->query("SELECT COUNT(*) as c FROM students WHERE gender='Male'")->fetch_assoc()['c'];
$totalFemale = $conn->query("SELECT COUNT(*) as c FROM students WHERE gender='Female'")->fetch_assoc()['c'];
$totalOther  = $conn->query("SELECT COUNT(*) as c FROM students WHERE gender='Other'")->fetch_assoc()['c'];

// Chart 1: Enrollments by Course
$byCourse = [];
$r = $conn->query("SELECT s.course, COUNT(*) as cnt FROM enrollments e JOIN students s ON e.student_id = s.id GROUP BY s.course ORDER BY cnt DESC");
while ($row = $r->fetch_assoc()) $byCourse[] = $row;

// Chart 2: Enrollments by Year Level
$byYear = [];
$r2 = $conn->query("SELECT s.year_level, COUNT(*) as cnt FROM enrollments e JOIN students s ON e.student_id = s.id GROUP BY s.year_level ORDER BY s.year_level");
while ($row = $r2->fetch_assoc()) $byYear[] = $row;

// Chart 3: Students by Course
$studentsByCourse = [];
$r3 = $conn->query("SELECT course, COUNT(*) as cnt FROM students GROUP BY course ORDER BY cnt DESC");
while ($row = $r3->fetch_assoc()) $studentsByCourse[] = $row;

// Chart 4: Enrollments per Academic Year
$byAcadYear = [];
$r4 = $conn->query("SELECT academic_year, COUNT(*) as cnt FROM enrollments GROUP BY academic_year ORDER BY academic_year");
while ($row = $r4->fetch_assoc()) $byAcadYear[] = $row;

// Chart 5: Enrollments per Semester
$bySemester = [];
$r5 = $conn->query("SELECT semester, COUNT(*) as cnt FROM enrollments GROUP BY semester");
while ($row = $r5->fetch_assoc()) $bySemester[] = $row;

// Chart 6: Daily enrollments (last 7 days)
$byDay = [];
$r6 = $conn->query("SELECT DATE(submitted_at) as day, COUNT(*) as cnt FROM enrollments WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(submitted_at) ORDER BY day");
while ($row = $r6->fetch_assoc()) $byDay[] = $row;

// Recent enrollments table
$recent = $conn->query("SELECT e.*, s.student_no, s.first_name, s.last_name, s.course, s.year_level FROM enrollments e JOIN students s ON e.student_id = s.id ORDER BY e.submitted_at DESC LIMIT 8");
$recentRows = [];
while ($row = $recent->fetch_assoc()) $recentRows[] = $row;

$conn->close();

// JSON encode for charts
$coursesJson        = json_encode(array_column($byCourse, 'course'));
$courseCountsJson   = json_encode(array_map('intval', array_column($byCourse, 'cnt')));
$yearsJson          = json_encode(array_map(fn($r) => $r['year_level'].'th Year', $byYear));
$yearCountsJson     = json_encode(array_map('intval', array_column($byYear, 'cnt')));
$sCourseJson        = json_encode(array_column($studentsByCourse, 'course'));
$sCourseCountsJson  = json_encode(array_map('intval', array_column($studentsByCourse, 'cnt')));
$acadYearJson       = json_encode(array_column($byAcadYear, 'academic_year'));
$acadYearCountsJson = json_encode(array_map('intval', array_column($byAcadYear, 'cnt')));
$semJson            = json_encode(array_column($bySemester, 'semester'));
$semCountsJson      = json_encode(array_map('intval', array_column($bySemester, 'cnt')));
$dayJson            = json_encode(array_column($byDay, 'day'));
$dayCountsJson      = json_encode(array_map('intval', array_column($byDay, 'cnt')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard – BulSU Enrollment</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.charts-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px; }
.charts-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
.charts-grid-half { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 24px; }
.chart-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: var(--radius); padding: 20px 24px; }
.chart-card h3 { font-size: 14px; font-weight: 700; margin-bottom: 16px; color: var(--text); font-family: 'Syne', sans-serif; }
.chart-card .chart-sub { font-size: 11px; color: var(--text-muted); margin-top: -12px; margin-bottom: 14px; }
.recent-table { width: 100%; border-collapse: collapse; }
.recent-table th { padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--card-border); }
.recent-table td { padding: 11px 12px; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.03); }
.recent-table tr:last-child td { border-bottom: none; }
@media (max-width: 900px) {
    .charts-grid-3, .charts-grid-2, .charts-grid-half { grid-template-columns: 1fr; }
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(4px);
}
.modal-content {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    max-width: 500px;
    width: 90%;
}
.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--card-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-body {
    padding: 24px;
}
.notification-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: 1px solid var(--card-border);
    border-radius: 8px;
    margin-bottom: 8px;
    background: rgba(255,255,255,0.02);
}
.notification-item .avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--highlight), var(--gold));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
}
.notification-item .details {
    flex: 1;
}
.notification-item .name {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 2px;
}
.notification-item .info {
    font-size: 12px;
    color: var(--text-muted);
}
.notification-item .time {
    font-size: 11px;
    color: var(--text-muted);
}
</style>
</head>
<body>
<div class="wrapper">
    <nav class="navbar">
        <a href="/enrollment_system/index.php" class="navbar-brand">
            <div class="logo-icon">🎓</div>
            <span>BulSU Admin</span>
        </a>
        <div class="navbar-nav">
            <span style="color:var(--text-muted);font-size:14px">Hi, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
            <a href="/enrollment_system/admin/logout.php" class="nav-link btn-danger">Logout</a>
        </div>
    </nav>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Main</div>
                <a href="/enrollment_system/admin/dashboard.php" class="sidebar-link active"><span class="icon">📊</span> Dashboard</a>
                <a href="/enrollment_system/admin/enrollments.php" class="sidebar-link"><span class="icon">📋</span> Enrollments</a>
                <a href="/enrollment_system/admin/schedules.php" class="sidebar-link"><span class="icon">📅</span> Schedule</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">Records</div>
                <a href="/enrollment_system/admin/students.php" class="sidebar-link"><span class="icon">👥</span> Students</a>
                <a href="/enrollment_system/admin/colleges.php" class="sidebar-link"><span class="icon">🏛️</span> Colleges</a>
                <a href="/enrollment_system/admin/programs.php" class="sidebar-link"><span class="icon">📚</span> Programs</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">Actions</div>
                <a href="/enrollment_system/admin/add_student.php" class="sidebar-link"><span class="icon">➕</span> Add Student</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">Notifications</div>
                <button id="notificationBtn" class="sidebar-link" style="width:100%;text-align:left;border:none;background:none;color:var(--text-muted);cursor:pointer;padding:11px 14px;border-radius:6px;margin-bottom:4px;transition:all 0.2s">
                    <span class="icon">🔔</span> New Enrollments
                    <span id="notificationBadge" class="badge" style="background:var(--highlight);color:white;font-size:10px;padding:2px 6px;border-radius:10px;margin-left:auto;display:none">0</span>
                </button>
            </div>
        </aside>
        <main class="main-content">
            <div class="page-header">
                <h1>Admin Dashboard</h1>
                <p>Overview of enrollment system statistics</p>
            </div>

            <!-- ===== STAT CARDS ===== -->
            <div class="stats-grid" style="margin-bottom:24px">
                <div class="stat-card blue">
                    <div class="stat-number"><?= $totalStudents ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card gold">
                    <div class="stat-number"><?= $totalEnrollments ?></div>
                    <div class="stat-label">Total Enrollments</div>
                </div>
                <div class="stat-card gold">
                    <div class="stat-number"><?= $totalPending ?></div>
                    <div class="stat-label">Pending Reviews</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-number"><?= $totalApproved ?></div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-number"><?= $totalRejected ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>

            <!-- ===== ROW 1: Status Donut + Gender Donut + Semester Polar ===== -->
            <div class="charts-grid-3">
                <div class="chart-card">
                    <h3>Enrollment Status</h3>
                    <p class="chart-sub">Distribution of all enrollment requests</p>
                    <canvas id="statusChart" height="200"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Students by Gender</h3>
                    <p class="chart-sub">Male, Female, Other breakdown</p>
                    <canvas id="genderChart" height="200"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Enrollments by Semester</h3>
                    <p class="chart-sub">1st, 2nd and Summer distribution</p>
                    <canvas id="semesterChart" height="200"></canvas>
                </div>
            </div>

            <!-- ===== ROW 2: Enrollments by Course (Bar) + Students by Course (Horizontal Bar) ===== -->
            <div class="charts-grid-2">
                <div class="chart-card">
                    <h3>Enrollments by Course</h3>
                    <p class="chart-sub">Number of enrollment submissions per course</p>
                    <canvas id="courseChart" height="220"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Students by Course</h3>
                    <p class="chart-sub">Total registered students per course</p>
                    <canvas id="studentCourseChart" height="220"></canvas>
                </div>
            </div>

            <!-- ===== ROW 3: By Year Level (Line) + By Academic Year (Bar) ===== -->
            <div class="charts-grid-2">
                <div class="chart-card">
                    <h3>Enrollments by Year Level</h3>
                    <p class="chart-sub">Which year levels have the most submissions</p>
                    <canvas id="yearChart" height="220"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Enrollments by Academic Year</h3>
                    <p class="chart-sub">Comparison across academic years</p>
                    <canvas id="acadYearChart" height="220"></canvas>
                </div>
            </div>

            <!-- ===== ROW 4: Daily Activity (Line) + Recent Table ===== -->
            <div class="charts-grid-half">
                <div class="chart-card">
                    <h3>Daily Enrollment Activity</h3>
                    <p class="chart-sub">Last 7 days submissions</p>
                    <canvas id="dailyChart" height="260"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Recent Enrollments</h3>
                    <p class="chart-sub">Latest 8 submissions</p>
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentRows as $row): ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></div>
                                <div style="color:var(--text-muted);font-size:11px"><?= htmlspecialchars($row['student_no']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($row['course']) ?> <?= $row['year_level'] ?></td>
                            <td style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($row['semester']) ?></td>
                            <td><span class="badge badge-<?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
                            <td style="color:var(--text-muted);font-size:11px"><?= date('M d, Y', strtotime($row['submitted_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="text-align:center;margin-top:16px">
                        <a href="/enrollment_system/admin/enrollments.php" class="btn btn-primary btn-sm">View All Enrollments</a>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Notification Modal -->
<div id="notificationModal" class="modal-overlay" style="display:none">
    <div class="modal-content" style="max-width:600px;width:90%;max-height:70vh;overflow-y:auto">
        <div class="modal-header">
            <h3 style="margin:0;font-size:18px">New Student Enrollments</h3>
            <button class="modal-close" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--text-muted)">&times;</button>
        </div>
        <div class="modal-body" style="padding:20px">
            <div id="notificationList">
                <div style="text-align:center;padding:40px;color:var(--text-muted)">
                    <div style="font-size:48px;margin-bottom:16px">📭</div>
                    <p>Loading notifications...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/enrollment_system/assets/js/main.js"></script>
<script>
Chart.defaults.color = 'rgba(255,255,255,0.65)';
Chart.defaults.font  = { family: "'DM Sans', sans-serif", size: 12 };
const grid = 'rgba(255,255,255,0.06)';
const colors = ['rgba(233,69,96,0.8)','rgba(0,184,148,0.8)','rgba(245,166,35,0.8)','rgba(116,185,255,0.8)','rgba(162,155,254,0.8)','rgba(255,159,67,0.8)','rgba(52,211,153,0.8)'];

// 1. Status Doughnut
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Approved', 'Rejected'],
        datasets: [{ data: [<?= $totalPending ?>, <?= $totalApproved ?>, <?= $totalRejected ?>],
            backgroundColor: ['rgba(245,166,35,0.85)','rgba(0,184,148,0.85)','rgba(233,69,96,0.85)'],
            borderWidth: 0, hoverOffset: 8 }]
    },
    options: { responsive: true, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { padding: 16 } } } }
});

// 2. Gender Doughnut
new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
        labels: ['Male', 'Female', 'Other'],
        datasets: [{ data: [<?= $totalMale ?>, <?= $totalFemale ?>, <?= $totalOther ?>],
            backgroundColor: ['rgba(116,185,255,0.85)','rgba(233,69,96,0.85)','rgba(162,155,254,0.85)'],
            borderWidth: 0, hoverOffset: 8 }]
    },
    options: { responsive: true, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { padding: 16 } } } }
});

// 3. Semester Pie
new Chart(document.getElementById('semesterChart'), {
    type: 'pie',
    data: {
        labels: <?= $semJson ?>,
        datasets: [{ data: <?= $semCountsJson ?>,
            backgroundColor: ['rgba(116,185,255,0.85)','rgba(233,69,96,0.85)','rgba(245,166,35,0.85)'],
            borderWidth: 0, hoverOffset: 8 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { padding: 16 } } } }
});

// 4. Enrollments by Course (Bar)
new Chart(document.getElementById('courseChart'), {
    type: 'bar',
    data: {
        labels: <?= $coursesJson ?>,
        datasets: [{ label: 'Enrollments', data: <?= $courseCountsJson ?>,
            backgroundColor: colors, borderRadius: 8, borderSkipped: false }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { x: { grid: { color: grid } }, y: { grid: { color: grid }, ticks: { stepSize: 1 } } } }
});

// 5. Students by Course (Horizontal Bar)
new Chart(document.getElementById('studentCourseChart'), {
    type: 'bar',
    data: {
        labels: <?= $sCourseJson ?>,
        datasets: [{ label: 'Students', data: <?= $sCourseCountsJson ?>,
            backgroundColor: ['rgba(0,184,148,0.8)','rgba(116,185,255,0.8)','rgba(245,166,35,0.8)','rgba(162,155,254,0.8)','rgba(233,69,96,0.8)'],
            borderRadius: 8, borderSkipped: false }]
    },
    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } },
        scales: { x: { grid: { color: grid }, ticks: { stepSize: 1 } }, y: { grid: { color: grid } } } }
});

// 6. Enrollments by Year Level (Line)
new Chart(document.getElementById('yearChart'), {
    type: 'line',
    data: {
        labels: <?= $yearsJson ?>,
        datasets: [{ label: 'Enrollments', data: <?= $yearCountsJson ?>,
            borderColor: '#e94560', backgroundColor: 'rgba(233,69,96,0.1)',
            fill: true, tension: 0.4, pointBackgroundColor: '#e94560', pointRadius: 6, pointHoverRadius: 8 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { x: { grid: { color: grid } }, y: { grid: { color: grid }, ticks: { stepSize: 1 } } } }
});

// 7. Enrollments by Academic Year (Bar)
new Chart(document.getElementById('acadYearChart'), {
    type: 'bar',
    data: {
        labels: <?= $acadYearJson ?>,
        datasets: [{ label: 'Enrollments', data: <?= $acadYearCountsJson ?>,
            backgroundColor: ['rgba(116,185,255,0.8)','rgba(162,155,254,0.8)'],
            borderRadius: 10, borderSkipped: false }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { x: { grid: { color: grid } }, y: { grid: { color: grid }, ticks: { stepSize: 1 } } } }
});

// 8. Daily Activity (Line)
new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: {
        labels: <?= $dayJson ?: '[]' ?>,
        datasets: [{ label: 'Submissions', data: <?= $dayCountsJson ?: '[]' ?>,
            borderColor: '#00b894', backgroundColor: 'rgba(0,184,148,0.1)',
            fill: true, tension: 0.4, pointBackgroundColor: '#00b894', pointRadius: 5 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { x: { grid: { color: grid } }, y: { grid: { color: grid }, ticks: { stepSize: 1 } } } }
});
</script>

<script>
// Notification functionality
$(document).ready(function() {
    // Load notification count on page load
    loadNotificationCount();
    
    // Notification button click
    $('#notificationBtn').on('click', function() {
        loadNotifications();
        $('#notificationModal').show();
    });
    
    // Close modal
    $('.modal-close, .modal-overlay').on('click', function(e) {
        if (e.target === this || $(e.target).hasClass('modal-close')) {
            $('#notificationModal').hide();
        }
    });
    
    // Load notification count
    function loadNotificationCount() {
        $.ajax({
            url: '/enrollment_system/admin/ajax/get_notifications.php',
            method: 'GET',
            data: { action: 'count' },
            dataType: 'json',
            success: function(res) {
                if (res.success && res.count > 0) {
                    $('#notificationBadge').text(res.count).show();
                } else {
                    $('#notificationBadge').hide();
                }
            }
        });
    }
    
    // Load notifications
    function loadNotifications() {
        $('#notificationList').html('<div style="text-align:center;padding:40px;color:var(--text-muted)"><div style="font-size:24px;margin-bottom:16px">⏳</div><p>Loading...</p></div>');
        
        $.ajax({
            url: '/enrollment_system/admin/ajax/get_notifications.php',
            method: 'GET',
            data: { action: 'list', limit: 20 },
            dataType: 'json',
            success: function(res) {
                if (res.success && res.notifications.length > 0) {
                    let html = '';
                    res.notifications.forEach(function(notif) {
                        html += `
                            <div class="notification-item">
                                <div class="avatar">${notif.student_name.charAt(0).toUpperCase()}</div>
                                <div class="details">
                                    <div class="name">${notif.student_name}</div>
                                    <div class="info">ID: ${notif.student_no} • Course: ${notif.course}</div>
                                    <div class="time">${formatTimeAgo(notif.enrollment_date)}</div>
                                </div>
                            </div>
                        `;
                    });
                    $('#notificationList').html(html);
                    
                    // Mark as read
                    $.ajax({
                        url: '/enrollment_system/admin/ajax/get_notifications.php',
                        method: 'POST',
                        data: { action: 'mark_read' },
                        dataType: 'json'
                    });
                    
                    // Update badge
                    loadNotificationCount();
                } else {
                    $('#notificationList').html('<div style="text-align:center;padding:40px;color:var(--text-muted)"><div style="font-size:48px;margin-bottom:16px">📭</div><p>No new enrollments</p></div>');
                }
            },
            error: function() {
                $('#notificationList').html('<div style="text-align:center;padding:40px;color:var(--text-muted)"><div style="font-size:48px;margin-bottom:16px">❌</div><p>Failed to load notifications</p></div>');
            }
        });
    }
    
    // Format time ago
    function formatTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);
        
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
        if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
        return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    }
});
</script>
</body>
</html>