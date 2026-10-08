<?php
require_once '../includes/session.php';
requireStudent();
require_once '../config/database.php';
$conn = getConnection();

$studentId = $_SESSION['student_id'];
$student = $conn->query("SELECT * FROM students WHERE id = $studentId")->fetch_assoc();
$conn->close();

$updated = isset($_GET['updated']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile – BulSU Enrollment</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
.password-box { position: relative; }
.password-box input { padding-right: 44px; }
.password-box .password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: var(--text);
    cursor: pointer;
    font-size: 18px;
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.password-box .password-toggle svg { display: block; }
.password-box .password-toggle:focus {
    outline: none;
}
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
            <span style="color:var(--text-muted);font-size:14px">Hi, <?= htmlspecialchars($student['first_name']) ?></span>
            <a href="/enrollment_system/student/logout.php" class="nav-link btn-danger">Logout</a>
        </div>
    </nav>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Student</div>
                <a href="/enrollment_system/student/dashboard.php" class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
                <a href="/enrollment_system/student/enroll.php" class="sidebar-link"><span class="icon">📝</span> Enroll Now</a>
                <a href="/enrollment_system/student/status.php" class="sidebar-link"><span class="icon">🔍</span> My Status</a>
                <a href="/enrollment_system/student/profile.php" class="sidebar-link active"><span class="icon">👤</span> My Profile</a>                <a href="/enrollment_system/student/calendar.php" class="sidebar-link"><span class="icon">📅</span> Academic Calendar</a>
                <a href="/enrollment_system/student/schedule.php" class="sidebar-link"><span class="icon">🕒</span> Class Schedule</a>            </div>
        </aside>
        <main class="main-content">
            <div class="page-header">
                <h1>My Profile</h1>
                <p>View and update your personal information</p>
            </div>

            <?php if ($updated): ?>
            <div class="alert alert-success">Profile updated successfully!</div>
            <?php endif; ?>

            <div id="profileMsg"></div>

            <div class="card" style="max-width:700px">
                <div class="card-header">
                    <h2>Personal Information</h2>
                </div>
                <div class="card-body">
                    <form id="profileForm">
                        <div class="form-row three">
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-control">
                                    <option <?= $student['gender']==='Male' ? 'selected' : '' ?>>Male</option>
                                    <option <?= $student['gender']==='Female' ? 'selected' : '' ?>>Female</option>
                                    <option <?= $student['gender']==='Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Birthdate</label>
                                <input type="date" name="birthdate" class="form-control" id="birthdate" value="<?= htmlspecialchars($student['birthdate'] ?? '') ?>">
                                <span id="ageDisplay" style="font-size:12px;color:var(--text-muted);margin-top:4px;display:block"></span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" pattern="[0-9]*" title="Please enter only numbers">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($student['address'] ?? '') ?>">
                        </div>

                        <p style="font-family:'Syne',sans-serif;font-weight:700;font-size:13px;letter-spacing:1px;text-transform:uppercase;color:var(--highlight);margin:24px 0 16px">Academic Information</p>
                        <div class="form-row three">
                            <div class="form-group">
                                <label class="form-label">Student No.</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($student['student_no']) ?>" readonly style="opacity:0.5;cursor:not-allowed">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Course</label>
                                <select name="course" class="form-control">
                                    <?php foreach(['BSIT','BSCS','BSIS','BSCE','BSEd','BSBA'] as $c): ?>
                                    <option <?= $student['course']===$c ? 'selected' : '' ?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Year Level</label>
                                <select name="year_level" class="form-control">
                                    <?php foreach(['1'=>'1st','2'=>'2nd','3'=>'3rd','4'=>'4th'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= $student['year_level']===$v ? 'selected' : '' ?>><?= $l ?> Year</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <p style="font-family:'Syne',sans-serif;font-weight:700;font-size:13px;letter-spacing:1px;text-transform:uppercase;color:var(--highlight);margin:24px 0 16px">Change Password <span style="font-weight:400;color:var(--text-muted);text-transform:none;letter-spacing:0">(leave blank to keep current)</span></p>
                        <div class="form-row">
                            <div class="form-group password-field">
                                <label class="form-label">New Password</label>
                                <div class="password-box">
                                    <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters" minlength="6">
                                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                        <svg class="icon-show" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="icon-hide" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94C16.1 19.23 14.07 20 12 20c-7 0-11-8-11-8a21.12 21.12 0 0 1 5.06-5.94M6.1 6.1C7.9 4.77 9.92 4 12 4c7 0 11 8 11 8a21.12 21.12 0 0 1-5.06 5.94"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group password-field">
                                <label class="form-label">Confirm New Password</label>
                                <div class="password-box">
                                    <input type="password" name="confirm_password" class="form-control">
                                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                        <svg class="icon-show" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="icon-hide" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94C16.1 19.23 14.07 20 12 20c-7 0-11-8-11-8a21.12 21.12 0 0 1 5.06-5.94M6.1 6.1C7.9 4.77 9.92 4 12 4c7 0 11 8 11 8a21.12 21.12 0 0 1-5.06 5.94"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="/enrollment_system/student/dashboard.php" class="btn btn-ghost">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="/enrollment_system/assets/js/main.js"></script>
<script>
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

// Initialize age on load
$(document).ready(function() {
    var initialAge = calculateAge($('#birthdate').val());
    $('#ageDisplay').text(initialAge ? 'Age: ' + initialAge + ' years' : '');
});

$('#profileForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $(this).find('[type=submit]');
    btn.prop('disabled', true).html('<span class="spinner"></span> Saving...');
    $.ajax({
        url: '/enrollment_system/student/ajax/update_profile.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            const type = res.success ? 'success' : 'error';
            $('#profileMsg').html(`<div class="alert alert-${type}">${res.message}</div>`);
            window.scrollTo(0, 0);
        },
        error: function() {
            $('#profileMsg').html('<div class="alert alert-error">Server error. Please try again.</div>');
        },
        complete: function() { btn.prop('disabled', false).html('Save Changes'); }
    });
});
</script>
</body>
</html>