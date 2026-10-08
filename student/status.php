<?php
require_once '../includes/session.php';
requireStudent();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Enrollment Status – BulSU</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>
<body>
<div class="wrapper">
    <nav class="navbar">
        <a href="/enrollment_system/index.php" class="navbar-brand"><div class="logo-icon">🎓</div><span>BulSU Enrollment</span></a>
        <div class="navbar-nav">
            <a href="/enrollment_system/student/dashboard.php" class="nav-link">Dashboard</a>
            <a href="/enrollment_system/student/logout.php" class="nav-link btn-danger">Logout</a>
        </div>
    </nav>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Student</div>
                <a href="/enrollment_system/student/dashboard.php" class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
                <a href="/enrollment_system/student/enroll.php" class="sidebar-link"><span class="icon">📝</span> Enroll Now</a>
                <a href="/enrollment_system/student/status.php" class="sidebar-link active"><span class="icon">🔍</span> My Status</a>
                <a href="/enrollment_system/student/profile.php" class="sidebar-link"><span class="icon">👤</span> My Profile</a>                <a href="/enrollment_system/student/calendar.php" class="sidebar-link"><span class="icon">📅</span> Academic Calendar</a>
                <a href="/enrollment_system/student/schedule.php" class="sidebar-link"><span class="icon">🕒</span> Class Schedule</a>            </div>
        </aside>
        <main class="main-content">
            <div class="page-header">
                <h1>Enrollment Status</h1>
                <p>Auto-refreshes every 15 seconds</p>
            </div>
            <div class="card" style="max-width:500px">
                <div class="card-body status-card" id="statusBox">
                    <div class="spinner"></div>
                    <p style="margin-top:16px;color:var(--text-muted)">Loading status...</p>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
function checkEnrollmentStatus() {
    $.ajax({
        url: '/enrollment_system/student/ajax/check_status.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (!res || !res.success) {
                $('#statusBox').html('<p style="color:var(--highlight)">Could not load status. Please refresh.</p>');
                return;
            }

            var statusMap = {
                'Pending':       { icon: '⏳', cls: 'badge-pending',  label: 'Pending Review' },
                'Approved':      { icon: '✅', cls: 'badge-approved', label: 'Approved' },
                'Rejected':      { icon: '❌', cls: 'badge-rejected', label: 'Rejected' },
                'Not Submitted': { icon: '📋', cls: 'badge-pending',  label: 'Not Yet Submitted' }
            };

            var s = statusMap[res.status] || { icon: '📋', cls: 'badge-pending', label: res.status };

            var html = '<span style="font-size:56px;display:block;margin-bottom:16px">' + s.icon + '</span>';
            html += '<h2 style="margin-bottom:12px">Enrollment Status</h2>';
            html += '<span class="badge ' + s.cls + '" style="font-size:15px;padding:8px 20px">' + s.label + '</span>';

            if (res.academic_year) {
                html += '<p style="margin-top:16px;font-size:14px;color:var(--text-muted)">';
                html += 'AY ' + res.academic_year + ' &bull; ' + res.semester + ' Semester';
                html += '</p>';
            }

            if (res.remarks) {
                html += '<div style="margin-top:16px;padding:12px 16px;background:rgba(255,255,255,0.04);border-radius:10px;text-align:left">';
                html += '<p style="font-size:13px;color:var(--text-muted)"><strong>Remarks:</strong> ' + res.remarks + '</p>';
                html += '</div>';
            }

            if (res.status === 'Not Submitted') {
                html += '<a href="/enrollment_system/student/enroll.php" class="btn btn-primary" style="margin-top:20px">Enroll Now</a>';
            }

            html += '<p style="margin-top:16px;font-size:11px;color:var(--text-muted)">Last checked: ' + new Date().toLocaleTimeString() + '</p>';

            $('#statusBox').html(html);
        },
        error: function() {
            $('#statusBox').html('<p style="color:var(--highlight)">Error loading status. Please check your connection.</p>');
        }
    });
}

$(document).ready(function() {
    checkEnrollmentStatus();
    setInterval(checkEnrollmentStatus, 15000);
});
</script>
</body>
</html>