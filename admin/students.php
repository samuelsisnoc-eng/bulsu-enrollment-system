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

// Get all students with their latest enrollment info
$result = $conn->query("
    SELECT s.*,
        e.id AS enrollment_id,
        e.status AS enrollment_status,
        e.semester,
        e.academic_year,
        e.submitted_at,
        p.program_name,
        c.college_name,
        c.college_code
    FROM students s
    LEFT JOIN enrollments e ON e.id = (
        SELECT id FROM enrollments
        WHERE student_id = s.id
        ORDER BY submitted_at DESC LIMIT 1
    )
    LEFT JOIN programs p ON s.program_id = p.id
    LEFT JOIN colleges c ON p.college_id = c.id
    ORDER BY s.last_name ASC
");
$students = [];
while ($r = $result->fetch_assoc()) $students[] = $r;
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Students – BulSU Admin</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
.action-group { display:flex; gap:5px; flex-wrap:wrap; }
</style>
</head>
<body>
<div class="wrapper">
    <nav class="navbar">
        <a href="/enrollment_system/index.php" class="navbar-brand"><div class="logo-icon">🎓</div><span>BulSU Admin</span></a>
        <div class="navbar-nav">
            <span style="color:var(--text-muted);font-size:14px">Hi, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
            <a href="/enrollment_system/admin/logout.php" class="nav-link btn-danger">Logout</a>
        </div>
    </nav>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Main</div>
                <a href="/enrollment_system/admin/dashboard.php" class="sidebar-link"><span class="icon">📊</span> Dashboard</a>
                <a href="/enrollment_system/admin/enrollments.php" class="sidebar-link"><span class="icon">📋</span> Enrollments</a>
                <a href="/enrollment_system/admin/schedules.php" class="sidebar-link"><span class="icon">📅</span> Schedule</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">Records</div>
                <a href="/enrollment_system/admin/students.php" class="sidebar-link active"><span class="icon">👥</span> Students</a>
                <a href="/enrollment_system/admin/colleges.php" class="sidebar-link"><span class="icon">🏛️</span> Colleges</a>
                <a href="/enrollment_system/admin/programs.php" class="sidebar-link"><span class="icon">📚</span> Programs</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">Actions</div>
                <a href="/enrollment_system/admin/add_student.php" class="sidebar-link"><span class="icon">➕</span> Add Student</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">Notifications</div>
                <button class="sidebar-link" id="notificationBtn" style="width:100%;text-align:left;border:none;background:none;color:inherit;padding:12px 16px;cursor:pointer;">
                    <span class="icon">🔔</span> New Enrollments
                    <span id="notificationBadge" class="badge" style="background:red;color:white;font-size:10px;padding:2px 6px;border-radius:10px;margin-left:auto;display:none;">0</span>
                </button>
            </div>
        </aside>
        <main class="main-content">
            <div class="page-header" style="display:flex;align-items:center;justify-content:space-between">
                <div><h1>Student Records</h1><p>Manage all registered students and their enrollment status</p></div>
                <a href="/enrollment_system/admin/add_student.php" class="btn btn-primary">Add Student</a>
            </div>

            <!-- Search & Filter -->
            <div class="search-bar">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name, email, student no...">
                <select id="filterCollege" class="form-control" style="max-width:160px">
                    <option value="">All Colleges</option>
                </select>
                <select id="filterProgram" class="form-control" style="max-width:160px" disabled>
                    <option value="">All Programs</option>
                </select>
                <select id="filterCourse" class="form-control" style="max-width:160px" disabled>
                    <option value="">All Courses</option>
                </select>
                <select id="filterYear" class="form-control" style="max-width:140px">
                    <option value="">All Years</option>
                    <option value="1">1st Year</option><option value="2">2nd Year</option>
                    <option value="3">3rd Year</option><option value="4">4th Year</option>
                </select>
                <select id="filterStatus" class="form-control" style="max-width:160px">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="not enrolled">Not Enrolled</option>
                </select>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Student No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>College</th>
                            <th>Program</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Semester</th>
                            <th>School Year</th>
                            <th>Enrollment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $s):
                        $status = $s['enrollment_status'] ?? 'Not Enrolled';
                        $statusLower = strtolower($status);
                    ?>
                    <tr data-college="<?= htmlspecialchars($s['college_code'] ?? '') ?>"
                        data-program="<?= htmlspecialchars($s['program_name'] ?? '') ?>"
                        data-course="<?= htmlspecialchars($s['course']) ?>"
                        data-year="<?= $s['year_level'] ?>"
                        data-status="<?= $statusLower ?>">
                        <td style="font-family:'Syne',sans-serif;font-weight:600;font-size:13px"><?= htmlspecialchars($s['student_no']) ?></td>
                        <td>
                            <div style="font-weight:500"><?= htmlspecialchars($s['last_name'].', '.$s['first_name'].' '.($s['middle_name']??'')) ?></div>
                        </td>
                        <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($s['email']) ?></td>
                        <td>
                            <span class="badge" style="background:rgba(116,185,255,0.12);color:#74b9ff;border:1px solid rgba(116,185,255,0.25)"><?= htmlspecialchars($s['college_code'] ?? '—') ?></span>
                        </td>
                        <td>
                            <span class="badge" style="background:rgba(0,184,148,0.12);color:var(--green);border:1px solid rgba(0,184,148,0.25)"><?= htmlspecialchars($s['program_name'] ?? '—') ?></span>
                        </td>
                        <td>
                            <span class="badge" style="background:rgba(255,193,7,0.12);color:#d4a574;border:1px solid rgba(255,193,7,0.25)"><?= htmlspecialchars($s['course']) ?></span>
                        </td>
                        <td style="font-size:13px"><?= ['1'=>'1st','2'=>'2nd','3'=>'3rd','4'=>'4th'][$s['year_level']] ?? $s['year_level'] ?> Year</td>
                        <td style="color:var(--text-muted);font-size:13px">
                            <?= $s['semester'] ? htmlspecialchars($s['semester']).' Sem' : '—' ?>
                        </td>
                        <td style="color:var(--text-muted);font-size:13px">
                            <?= $s['academic_year'] ? htmlspecialchars($s['academic_year']) : '—' ?>
                        </td>
                        <td>
                            <?php if ($s['enrollment_status']): ?>
                            <span class="badge badge-<?= $statusLower ?>"><?= $status ?></span>
                            <?php else: ?>
                            <span class="badge" style="background:rgba(160,160,176,0.1);color:var(--text-muted);border:1px solid rgba(160,160,176,0.2)">Not Enrolled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-group">
                                <button class="btn btn-ghost btn-sm btn-view-student" data-id="<?= $s['id'] ?>" title="View">View</button>
                                <button class="btn btn-danger btn-sm btn-delete-student" data-id="<?= $s['id'] ?>" data-name="<?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?>" title="Delete">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal-overlay" id="notificationModal">
    <div class="modal" style="max-width:700px">
        <div class="modal-header"><h2>New Student Enrollments</h2><button class="modal-close" onclick="closeNotificationModal()">✕</button></div>
        <div id="notificationContent" style="max-height:400px;overflow-y:auto;">
            <p style="color:var(--text-muted);padding:20px;text-align:center">Loading notifications...</p>
        </div>
    </div>
</div>

<script src="/enrollment_system/assets/js/main.js"></script>
<script>
// Search & Filter
function applyFilters() {
    var search = $('#searchInput').val().toLowerCase();
    var college = String($('#filterCollege').val()).toLowerCase();
    var program = String($('#filterProgram').val()).toLowerCase();
    var course = String($('#filterCourse').val()).toLowerCase();
    var year   = String($('#filterYear').val());
    var status = String($('#filterStatus').val()).toLowerCase();
    $('tbody tr').each(function () {
        var c = String($(this).data('college') || '').toLowerCase();
        var p = String($(this).data('program') || '').toLowerCase();
        var co = String($(this).data('course') || '').toLowerCase();
        var y = String($(this).data('year') || '');
        var s = String($(this).data('status') || '').toLowerCase();
        var text = $(this).text().toLowerCase();
        var show = (!search || text.includes(search))
                && (!college || c.includes(college))
                && (!program || p.includes(program))
                && (!course || co.includes(course))
                && (!year   || y === year)
                && (!status || s === status);
        $(this).toggle(show);
    });
}
$('#searchInput').on('keyup', applyFilters);
$('#filterCollege, #filterProgram, #filterCourse, #filterYear, #filterStatus').on('change', applyFilters);

// Load colleges for filter
$.ajax({
    url: '/enrollment_system/admin/ajax/colleges.php',
    method: 'GET',
    data: { action: 'get_colleges' },
    dataType: 'json',
    success: function(res) {
        if (res.success) {
            var options = '<option value="">All Colleges</option>';
            res.colleges.forEach(function(college) {
                options += '<option value="' + college.college_code + '">' + college.college_code + ' - ' + college.college_name + '</option>';
            });
            $('#filterCollege').html(options);
        }
    }
});

// Load programs when college filter is selected
$('#filterCollege').on('change', function() {
    var collegeCode = $(this).val();
    if (collegeCode) {
        $.ajax({
            url: '/enrollment_system/admin/ajax/programs.php',
            method: 'GET',
            data: { action: 'get_programs_by_college_code', college_code: collegeCode },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    var options = '<option value="">All Programs</option>';
                    res.programs.forEach(function(program) {
                        options += '<option value="' + program.program_name + '">' + program.program_name + '</option>';
                    });
                    $('#filterProgram').html(options).prop('disabled', false);
                    $('#filterCourse').html('<option value="">All Courses</option>').prop('disabled', false);
                }
            }
        });
    } else {
        $('#filterProgram').html('<option value="">All Programs</option>').prop('disabled', true);
        $('#filterCourse').html('<option value="">All Courses</option>').prop('disabled', true);
    }
});

// View student
$(document).on('click', '.btn-view-student', function () {
    var id = $(this).data('id');
    document.getElementById('viewModal').classList.add('active');
    $('#viewContent').html('<p style="color:var(--text-muted);padding:20px">Loading...</p>');
    $.ajax({
        url: '/enrollment_system/admin/ajax/get_student.php',
        method: 'GET', data: { id: id }, dataType: 'json',
        success: function (res) {
            if (res.success) {
                var s = res.student;
                var html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:4px 0">';
                var fields = [
                    ['Student No.', s.student_no], ['Full Name', s.last_name+', '+s.first_name+' '+(s.middle_name||'')],
                    ['Email', s.email], ['Phone', s.phone||'—'],
                    ['Gender', s.gender], ['Birthdate', s.birthdate||'—'], ['Age', s.birthdate ? (new Date().getFullYear() - new Date(s.birthdate).getFullYear() - ((new Date().getMonth() < new Date(s.birthdate).getMonth() || (new Date().getMonth() === new Date(s.birthdate).getMonth() && new Date().getDate() < new Date(s.birthdate).getDate())) ? 1 : 0)) + ' years' : '—'],
                    ['Course', s.course], ['Year Level', s.year_level],
                    ['Section', s.section||'—'], ['Address', s.address||'—']
                ];
                fields.forEach(function(f) {
                    html += '<div><p style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">'+f[0]+'</p>';
                    html += '<p style="font-size:14px">'+f[1]+'</p></div>';
                });
                html += '</div>';
                $('#viewContent').html(html);
            }
        }
    });
});

function showToast(msg, type) {
    var t = $('<div class="alert alert-'+type+'" style="position:fixed;bottom:24px;right:24px;z-index:9999;min-width:220px">'+msg+'</div>');
    $('body').append(t);
    setTimeout(function(){ t.fadeOut(400, function(){ t.remove(); }); }, 2500);
}

// Delete student
$(document).on('click', '.btn-delete-student', function () {
    var id = $(this).data('id');
    var name = $(this).data('name');
    if (confirm('Are you sure you want to delete student "' + name + '"? This action cannot be undone.')) {
        $.ajax({
            url: '/enrollment_system/admin/ajax/delete_student.php',
            method: 'POST',
            data: { student_id: id },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('[data-id="' + id + '"]').closest('tr').fadeOut(300, function(){ $(this).remove(); });
                    showToast('Student deleted successfully.', 'success');
                } else {
                    showToast(res.message, 'error');
                }
            },
            error: function() {
                showToast('Error deleting student.', 'error');
            }
        });
    }
});

// Notification functionality
function loadNotifications() {
    $.ajax({
        url: '/enrollment_system/admin/ajax/notifications.php',
        method: 'GET',
        data: { action: 'get_unread' },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                var count = res.notifications.length;
                if (count > 0) {
                    $('#notificationBadge').text(count).show();
                } else {
                    $('#notificationBadge').hide();
                }
            }
        }
    });
}

$('#notificationBtn').on('click', function() {
    document.getElementById('notificationModal').classList.add('active');
    loadNotificationDetails();
});

function loadNotificationDetails() {
    $('#notificationContent').html('<p style="color:var(--text-muted);padding:20px;text-align:center">Loading...</p>');
    $.ajax({
        url: '/enrollment_system/admin/ajax/notifications.php',
        method: 'GET',
        data: { action: 'get_unread_details' },
        dataType: 'json',
        success: function(res) {
            if (res.success && res.notifications.length > 0) {
                var html = '<div style="padding:16px 0">';
                res.notifications.forEach(function(notif) {
                    html += '<div class="card" style="margin-bottom:12px;padding:16px;">';
                    html += '<div style="display:flex;justify-content:space-between;align-items:start;">';
                    html += '<div>';
                    html += '<h4 style="margin:0 0 8px 0;font-size:16px;">' + notif.student_name + '</h4>';
                    html += '<p style="margin:0;color:var(--text-muted);font-size:14px;">Student No: ' + notif.student_no + '</p>';
                    html += '<p style="margin:0;color:var(--text-muted);font-size:14px;">Course: ' + notif.course + '</p>';
                    html += '<p style="margin:0;color:var(--text-muted);font-size:12px;">Enrolled: ' + new Date(notif.enrollment_date).toLocaleDateString() + '</p>';
                    html += '</div>';
                    html += '<button class="btn btn-ghost btn-sm" onclick="markAsRead(' + notif.id + ')">Mark Read</button>';
                    html += '</div>';
                    html += '</div>';
                });
                html += '</div>';
                $('#notificationContent').html(html);
            } else {
                $('#notificationContent').html('<p style="color:var(--text-muted);padding:40px;text-align:center">No new enrollment notifications.</p>');
            }
        }
    });
}

function markAsRead(notificationId) {
    $.ajax({
        url: '/enrollment_system/admin/ajax/notifications.php',
        method: 'POST',
        data: { action: 'mark_read', notification_id: notificationId },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                loadNotificationDetails();
                loadNotifications();
            }
        }
    });
}

function closeNotificationModal() {
    document.getElementById('notificationModal').classList.remove('active');
}

// Load notifications on page load
loadNotifications();

// Close modal on overlay click
document.getElementById('notificationModal').addEventListener('click', function(e){ if(e.target===this) closeNotificationModal(); });
</script>
</body>
</html>