<?php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
$conn = getConnection();
$programs = $conn->query("SELECT id, program_code, program_name FROM programs ORDER BY program_code ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Student – BulSU Admin</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
.password-field { position: relative; }
.password-field input { padding-right: 44px; }
.password-field .password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: var(--text);
    cursor: pointer;
    font-size: 18px;
    width: 32px;
    height: 32px;
    line-height: 32px;
    text-align: center;
    padding: 0;
}
.password-field .password-toggle:focus {
    outline: none;
}
</style>
</head>
<body>
<div class="wrapper">
    <nav class="navbar">
        <a href="/enrollment_system/index.php" class="navbar-brand"><div class="logo-icon">🎓</div><span>BulSU Admin</span></a>
        <div class="navbar-nav">
            <a href="/enrollment_system/admin/students.php" class="nav-link">← Back to Students</a>
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
                <a href="/enrollment_system/admin/students.php" class="sidebar-link"><span class="icon">👥</span> Students</a>
                <a href="/enrollment_system/admin/colleges.php" class="sidebar-link"><span class="icon">🏛️</span> Colleges</a>
                <a href="/enrollment_system/admin/programs.php" class="sidebar-link"><span class="icon">📚</span> Programs</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">Actions</div>
                <a href="/enrollment_system/admin/add_student.php" class="sidebar-link active"><span class="icon">➕</span> Add Student</a>
            </div>
        </aside>
        <main class="main-content">
            <div class="page-header">
                <h1>Add New Student</h1>
                <p>Create a new student record manually</p>
            </div>
            <div class="card" style="max-width:700px">
                <div class="card-body">
                    <div id="formMsg"></div>
                    <form id="adminAddStudentForm">
                        <p style="font-family:'Syne',sans-serif;font-weight:700;font-size:13px;letter-spacing:1px;text-transform:uppercase;color:var(--highlight);margin-bottom:16px">Personal Information</p>
                        <div class="form-row three">
                            <div class="form-group">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Gender *</label>
                                <select name="gender" class="form-control" required>
                                    <option value="">Select Gender</option>
                                    <option>Male</option><option>Female</option><option>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Birthdate</label>
                                <input type="date" name="birthdate" class="form-control" id="birthdate">
                                <span id="ageDisplay" style="font-size:12px;color:var(--text-muted);margin-top:4px;display:block"></span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" pattern="[0-9]*" title="Please enter only numbers">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control">
                        </div>
                        <p style="font-family:'Syne',sans-serif;font-weight:700;font-size:13px;letter-spacing:1px;text-transform:uppercase;color:var(--highlight);margin:24px 0 16px">Academic Information</p>
                        <div class="form-row three">
                                <div class="form-group">
                                <label class="form-label">Program *</label>
                                <select name="program_id" class="form-control" required>
                                    <option value="">Select Program</option>
                                    <?php while ($program = $programs->fetch_assoc()): ?>
                                        <option value="<?= $program['id'] ?>"><?= htmlspecialchars($program['program_code'] . ' - ' . $program['program_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Course *</label>
                                <select name="course" class="form-control" required>
                                    <option value="">Select</option>
                                    <option>BSIT</option><option>BSCS</option><option>BSIS</option>
                                    <option>BSCE</option><option>BSEd</option><option>BSBA</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Year Level *</label>
                                <select name="year_level" class="form-control" required>
                                    <option value="">Year</option>
                                    <option value="1">1st</option><option value="2">2nd</option>
                                    <option value="3">3rd</option><option value="4">4th</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Section</label>
                                <input type="text" name="section" class="form-control">
                            </div>
                        </div>
                        <p style="font-family:'Syne',sans-serif;font-weight:700;font-size:13px;letter-spacing:1px;text-transform:uppercase;color:var(--highlight);margin:24px 0 16px">Account Credentials</p>
                        <div class="form-group">
                            <label class="form-label">Student Number *</label>
                            <input type="text" name="student_no" class="form-control" placeholder="e.g. 2024-00001" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group password-field">
                                <label class="form-label">Password *</label>
                                <div class="password-box">
                                    <input type="password" name="password" class="form-control" minlength="6" required>
                                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                        <svg class="icon-show" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="icon-hide" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94C16.1 19.23 14.07 20 12 20c-7 0-11-8-11-8a21.12 21.12 0 0 1 5.06-5.94M6.1 6.1C7.9 4.77 9.92 4 12 4c7 0 11 8 11 8a21.12 21.12 0 0 1-5.06 5.94"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group password-field">
                                <label class="form-label">Confirm Password *</label>
                                <div class="password-box">
                                    <input type="password" name="confirm_password" class="form-control" required>
                                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                        <svg class="icon-show" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="icon-hide" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94C16.1 19.23 14.07 20 12 20c-7 0-11-8-11-8a21.12 21.12 0 0 1 5.06-5.94M6.1 6.1C7.9 4.77 9.92 4 12 4c7 0 11 8 11 8a21.12 21.12 0 0 1-5.06 5.94"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;gap:12px;margin-top:8px">
                            <button type="submit" class="btn btn-primary" id="addBtn">➕ Add Student</button>
                            <a href="/enrollment_system/admin/students.php" class="btn btn-ghost">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
$(document).ready(function () {
    function calculateAge(birthdate) {
        if (!birthdate) return '';
        var birth = new Date(birthdate);
        var now = new Date();
        var age = now.getFullYear() - birth.getFullYear();
        var monthDiff = now.getMonth() - birth.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < birth.getDate())) {
            age--;
        }
        return age;
    }

    $('#birthdate').on('change', function() {
        var age = calculateAge($(this).val());
        $('#ageDisplay').text(age ? 'Age: ' + age + ' years' : '');
    });

    $(document).on('click', '.password-toggle', function() {
        var input = $(this).closest('.password-field').find('input');
        var shown = input.attr('type') === 'text';
        input.attr('type', shown ? 'password' : 'text');
        $(this).find('.icon-show').toggle(shown);
        $(this).find('.icon-hide').toggle(!shown);
    });

    $('#adminAddStudentForm').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#addBtn');
        btn.prop('disabled', true).html('<span class="spinner"></span> Adding...');
        $.ajax({
            url: '/enrollment_system/admin/ajax/add_student.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#formMsg').html('<div class="alert alert-success">' + res.message + '</div>');
                    setTimeout(function () {
                        window.location.href = '/enrollment_system/admin/students.php';
                    }, 1500);
                } else {
                    $('#formMsg').html('<div class="alert alert-error">' + res.message + '</div>');
                    btn.prop('disabled', false).html('➕ Add Student');
                }
            },
            error: function (xhr) {
                $('#formMsg').html('<div class="alert alert-error">Server error: ' + xhr.status + ' ' + xhr.statusText + '</div>');
                btn.prop('disabled', false).html('➕ Add Student');
            }
        });
    });
});
</script>
</body>
</html>
