<?php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
$conn = getConnection();

function calculateAge($birthdate) {
    if (!$birthdate) return '';
    $birth = new DateTime($birthdate);
    $now = new DateTime();
    return $now->diff($birth)->y;
}

$enrollments = $conn->query("
    SELECT e.*, s.student_no, s.first_name, s.last_name, s.course, s.year_level, s.section, s.birthdate
    FROM enrollments e
    JOIN students s ON e.student_id = s.id
    ORDER BY s.student_no ASC, e.submitted_at DESC
");

// Group by student — collect all their enrollments
$grouped = [];
while ($e = $enrollments->fetch_assoc()) {
    $key = $e['student_no'];
    if (!isset($grouped[$key])) {
        $grouped[$key] = [
            'student_no' => $e['student_no'],
            'full_name'  => $e['last_name'] . ', ' . $e['first_name'],
            'course'     => $e['course'],
            'year_level' => $e['year_level'],
            'section'    => $e['section'],
            'birthdate'  => $e['birthdate'],
            'batches'    => [],
        ];
    }
    // Each enrollment batch = one row of subjects
    $subjects = !empty($e['subjects']) ? array_map('trim', explode(',', $e['subjects'])) : [];
    $grouped[$key]['batches'][] = [
        'enrollment_id' => $e['id'],
        'academic_year' => $e['academic_year'],
        'semester'      => $e['semester'],
        'subjects'      => array_filter($subjects),
        'status'        => $e['status'],
        'submitted_at'  => $e['submitted_at'],
    ];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enrollments – BulSU Admin</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
.enroll-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: var(--radius);
    margin-bottom: 16px;
    overflow: hidden;
    transition: border-color 0.2s;
}
.enroll-card:hover { border-color: rgba(255,255,255,0.12); }
.enroll-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--card-border);
    background: rgba(255,255,255,0.02);
}
.student-info { display: flex; align-items: center; gap: 16px; }
.student-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--highlight), var(--gold));
    display: flex; align-items: center; justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 700; font-size: 14px; color: white;
    flex-shrink: 0;
}
.student-name { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 15px; }
.student-meta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.batch-table { width: 100%; border-collapse: collapse; }
.batch-table th {
    padding: 10px 20px;
    text-align: left;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--text-muted);
    border-bottom: 1px solid var(--card-border);
    background: rgba(255,255,255,0.01);
}
.batch-table td {
    padding: 12px 20px;
    font-size: 13px;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    vertical-align: top;
}
.batch-table tr:last-child td { border-bottom: none; }
.subject-pills { display: flex; flex-wrap: wrap; gap: 6px; }
.subject-pill {
    background: rgba(116,185,255,0.1);
    border: 1px solid rgba(116,185,255,0.25);
    color: #74b9ff;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
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
            <a href="/enrollment_system/admin/logout.php" class="nav-link btn-danger">Logout</a>
        </div>
    </nav>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Main</div>
                <a href="/enrollment_system/admin/dashboard.php" class="sidebar-link"><span class="icon">📊</span> Dashboard</a>
                <a href="/enrollment_system/admin/enrollments.php" class="sidebar-link active"><span class="icon">📋</span> Enrollments</a>
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
                <h1>Enrollment Requests</h1>
                <p>Approve or reject student enrollment submissions</p>
            </div>

            <!-- Search & Filter -->
            <div class="search-bar" style="margin-bottom:24px">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name or student no...">
                <select id="filterCourse" class="form-control" style="max-width:160px">
                    <option value="">All Courses</option>
                    <option>BSIT</option><option>BSCS</option><option>BSIS</option>
                    <option>BSCE</option><option>BSEd</option><option>BSBA</option>
                </select>
                <select id="filterYear" class="form-control" style="max-width:140px">
                    <option value="">All Years</option>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                </select>
                <select id="filterStatus" class="form-control" style="max-width:150px">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <!-- Enrollment Cards -->
            <div id="enrollmentList">
            <?php foreach ($grouped as $student): ?>
            <?php
                // Determine overall status for filtering (use latest batch status)
                $latestStatus = strtolower($student['batches'][0]['status']);
                $initials = strtoupper(substr($student['full_name'], 0, 1));
            ?>
            <div class="enroll-card"
                 data-name="<?= strtolower(htmlspecialchars($student['full_name'])) ?>"
                 data-studentno="<?= strtolower(htmlspecialchars($student['student_no'])) ?>"
                 data-course="<?= htmlspecialchars($student['course']) ?>"
                 data-year="<?= $student['year_level'] ?>"
                 data-status="<?= $latestStatus ?>">

                <!-- Student Header -->
                <div class="enroll-card-header">
                    <div class="student-info">
                        <div class="student-avatar"><?= $initials ?></div>
                        <div>
                            <div class="student-name"><?= htmlspecialchars($student['full_name']) ?></div>
                            <div class="student-meta">
                                <?= htmlspecialchars($student['student_no']) ?> &nbsp;|&nbsp;
                                Age: <?= calculateAge($student['birthdate']) ? calculateAge($student['birthdate']) . ' years' : '—' ?> &nbsp;|&nbsp;
                                <span class="badge" style="background:rgba(116,185,255,0.12);color:#74b9ff;border:1px solid rgba(116,185,255,0.25);font-size:10px"><?= htmlspecialchars($student['course']) ?></span>
                                &nbsp;Year <?= $student['year_level'] ?> &nbsp;<?= htmlspecialchars($student['section']) ?>
                            </div>
                        </div>
                    </div>
                    <div style="font-size:12px;color:var(--text-muted)"><?= count($student['batches']) ?> enrollment<?= count($student['batches']) > 1 ? 's' : '' ?> submitted</div>
                </div>

                <!-- Enrollment Batches Table -->
                <table class="batch-table">
                    <thead>
                        <tr>
                            <th style="width:160px">Academic Year</th>
                            <th style="width:120px">Semester</th>
                            <th>Subjects</th>
                            <th style="width:120px">Status</th>
                            <th style="width:110px">Date</th>
                            <th style="width:180px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($student['batches'] as $batch): ?>
                    <tr id="batch-row-<?= $batch['enrollment_id'] ?>" data-rowstatus="<?= strtolower($batch['status']) ?>">
                        <td><?= htmlspecialchars($batch['academic_year']) ?></td>
                        <td style="color:var(--text-muted)"><?= htmlspecialchars($batch['semester']) ?> Sem</td>
                        <td>
                            <div class="subject-pills">
                                <?php foreach ($batch['subjects'] as $sub): ?>
                                <span class="subject-pill"><?= htmlspecialchars($sub) ?></span>
                                <?php endforeach; ?>
                                <?php if (empty($batch['subjects'])): ?>
                                <span style="color:var(--text-muted);font-size:12px">—</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="batch-status-<?= $batch['enrollment_id'] ?>">
                            <span class="badge badge-<?= strtolower($batch['status']) ?>"><?= $batch['status'] ?></span>
                        </td>
                        <td style="color:var(--text-muted);font-size:12px"><?= date('M d, Y', strtotime($batch['submitted_at'])) ?></td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap">
                                <button class="btn btn-ghost btn-sm btn-view-enrollment" data-id="<?= $batch['enrollment_id'] ?>">View</button>
                                <?php if ($batch['status'] === 'Pending'): ?>
                                <button class="btn btn-success btn-sm btn-approve" data-id="<?= $batch['enrollment_id'] ?>">✓ Approve</button>
                                <button class="btn btn-danger btn-sm btn-reject" data-id="<?= $batch['enrollment_id'] ?>">✕ Reject</button>
                                <?php elseif ($batch['status'] === 'Approved'): ?>
                                <button class="btn btn-danger btn-sm btn-reject" data-id="<?= $batch['enrollment_id'] ?>">✕ Reject</button>
                                <button class="btn btn-ghost btn-sm btn-pend" data-id="<?= $batch['enrollment_id'] ?>" style="font-size:11px">Pend</button>
                                <?php elseif ($batch['status'] === 'Rejected'): ?>
                                <button class="btn btn-success btn-sm btn-approve" data-id="<?= $batch['enrollment_id'] ?>">✓ Approve</button>
                                <button class="btn btn-ghost btn-sm btn-pend" data-id="<?= $batch['enrollment_id'] ?>" style="font-size:11px">Pend</button>
                                <?php endif; ?>
                                <button class="btn btn-danger btn-sm btn-delete-enrollment" data-id="<?= $batch['enrollment_id'] ?>">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>

            <?php if (empty($grouped)): ?>
            <div class="card">
                <div class="card-body" style="text-align:center;padding:48px">
                    <div style="font-size:48px;margin-bottom:16px">📋</div>
                    <h2>No Enrollments Yet</h2>
                    <p style="color:var(--text-muted);margin-top:8px">No enrollment submissions have been made.</p>
                </div>
            </div>
            <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<!-- View Enrollment Modal -->
<div class="modal-overlay" id="viewEnrollmentModal" style="display:none">
    <div class="modal" style="max-width:680px">
        <div class="modal-header"><h2>Enrollment Details</h2><button class="modal-close" onclick="document.getElementById('viewEnrollmentModal').style.display='none'">✕</button></div>
        <div id="viewEnrollmentContent" style="color:var(--text)"></div>
    </div>
</div>
<script src="/enrollment_system/assets/js/main.js"></script>
<script>
// Search
$('#searchInput').on('keyup', applyFilters);

// Filters
$('#filterCourse, #filterYear, #filterStatus').on('change', applyFilters);

function applyFilters() {
    var search = $('#searchInput').val().toLowerCase();
    var course = String($('#filterCourse').val()).toLowerCase();
    var year   = String($('#filterYear').val());
    var status = String($('#filterStatus').val()).toLowerCase();

    $('.enroll-card').each(function () {
        var card   = $(this);
        var c      = String(card.data('course') || '').toLowerCase();
        var y      = String(card.data('year') || '');
        var name   = String(card.data('name') || '');
        var sno    = String(card.data('studentno') || '');

        // For status: check if ANY batch row inside this card matches
        var statusMatch = true;
        if (status) {
            statusMatch = false;
            card.find('tbody tr').each(function () {
                var rowStatus = String($(this).data('rowstatus') || '').toLowerCase();
                if (rowStatus === status) { statusMatch = true; return false; }
            });
        }

        var show = (!search || name.includes(search) || sno.includes(search))
                && (!course || c.includes(course))
                && (!year   || y === year)
                && statusMatch;

        card.toggle(show);

        // If filtering by status, hide batch rows that don't match
        if (show) {
            card.find('tbody tr').each(function () {
                if (status) {
                    // Use data-status on the row instead of badge text
                    var rowStatus = String($(this).data('rowstatus') || '').toLowerCase();
                    $(this).toggle(rowStatus === status);
                } else {
                    $(this).show();
                }
            });
        }
    });
}

// Approve / Reject / Pend — update the batch row actions
$(document).on('click', '.btn-approve, .btn-reject, .btn-pend', function () {
    var id     = $(this).data('id');
    var action = $(this).hasClass('btn-approve') ? 'Approved' : $(this).hasClass('btn-reject') ? 'Rejected' : 'Pending';
    var row    = $(this).closest('tr');

    $.ajax({
        url: '/enrollment_system/admin/ajax/update_status.php',
        method: 'POST',
        data: { enrollment_id: id, status: action },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                var badgeClass = action === 'Approved' ? 'badge-approved' : action === 'Rejected' ? 'badge-rejected' : 'badge-pending';
                row.find('.badge').attr('class', 'badge ' + badgeClass).text(action);
                row.attr('data-rowstatus', action.toLowerCase());
                row.closest('.enroll-card').attr('data-status', action.toLowerCase());
                updateEnrollmentActions(row, action, id);
                var toast = $('<div class="alert alert-success" style="position:fixed;bottom:24px;right:24px;z-index:9999;min-width:240px">' + res.message + '</div>');
                $('body').append(toast);
                setTimeout(function () { toast.fadeOut(400, function () { toast.remove(); }); }, 3000);
            }
        }
    });
});

function updateEnrollmentActions(row, status, id) {
    var actions = '<div style="display:flex;gap:6px;flex-wrap:wrap">';
    actions += '<button class="btn btn-ghost btn-sm btn-view-enrollment" data-id="' + id + '">View</button>';
    if (status === 'Pending') {
        actions += '<button class="btn btn-success btn-sm btn-approve" data-id="' + id + '">✓ Approve</button>';
        actions += '<button class="btn btn-danger btn-sm btn-reject" data-id="' + id + '">✕ Reject</button>';
    } else if (status === 'Approved') {
        actions += '<button class="btn btn-danger btn-sm btn-reject" data-id="' + id + '">✕ Reject</button>';
        actions += '<button class="btn btn-ghost btn-sm btn-pend" data-id="' + id + '" style="font-size:11px">Pend</button>';
    } else if (status === 'Rejected') {
        actions += '<button class="btn btn-success btn-sm btn-approve" data-id="' + id + '">✓ Approve</button>';
        actions += '<button class="btn btn-ghost btn-sm btn-pend" data-id="' + id + '" style="font-size:11px">Pend</button>';
    }
    actions += '<button class="btn btn-danger btn-sm btn-delete-enrollment" data-id="' + id + '">Delete</button>';
    actions += '</div>';
    row.find('td:last-child').html(actions);
}

$(document).on('click', '.btn-view-enrollment', function () {
    var row = $(this).closest('tr');
    var card = row.closest('.enroll-card');
    var studentName = card.find('.student-name').text();
    var studentInfo = card.find('.student-meta').text();
    var academicYear = row.find('td').eq(0).text();
    var semester = row.find('td').eq(1).text();
    var subjects = [];
    row.find('.subject-pill').each(function () { subjects.push($(this).text()); });
    if (subjects.length === 0) subjects.push('—');
    var status = row.find('.badge').text();
    var date = row.find('td').eq(4).text();
    var html = '<div style="padding:12px 0;line-height:1.6">';
    html += '<p style="font-size:14px;font-weight:700;margin-bottom:4px">' + studentName + '</p>';
    html += '<p style="font-size:13px;color:var(--text-muted);margin-bottom:12px">' + studentInfo + '</p>';
    html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">';
    html += '<div><strong>Academic Year</strong><p>' + academicYear + '</p></div>';
    html += '<div><strong>Semester</strong><p>' + semester + '</p></div>';
    html += '<div><strong>Status</strong><p>' + status + '</p></div>';
    html += '<div><strong>Submitted</strong><p>' + date + '</p></div>';
    html += '</div>';
    html += '<div><strong>Subjects</strong><div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">';
    subjects.forEach(function (sub) {
        html += '<span class="subject-pill" style="background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.1);">'+sub+'</span>';
    });
    html += '</div></div>';
    html += '</div>';
    $('#viewEnrollmentContent').html(html);
    $('#viewEnrollmentModal').css('display', 'flex');
});

$(document).on('click', '#viewEnrollmentModal, #viewEnrollmentModal .modal-close', function (e) {
    if (e.target === this || $(e.target).hasClass('modal-close')) {
        $('#viewEnrollmentModal').hide();
    }
});

$(document).on('click', '.btn-delete-enrollment', function () {
    if (!confirm('Delete this enrollment record?')) return;
    var id = $(this).data('id');
    var row = $(this).closest('tr');
    $.ajax({
        url: '/enrollment_system/admin/ajax/delete_enrollment.php',
        method: 'POST',
        data: { enrollment_id: id },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                row.fadeOut(200, function () { $(this).remove(); });
                showToast(res.message, 'success');
            } else {
                showToast(res.message, 'error');
            }
        },
        error: function () {
            showToast('Server error. Please try again.', 'error');
        }
    });
});
</script>

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

<style>
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