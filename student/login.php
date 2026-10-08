<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Login – BulSU Enrollment</title>
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
.password-box .password-toggle:focus { outline: none; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="login-page">
        <div class="login-card">
            <div class="login-logo">
                <div class="icon-wrap">🎒</div>
                <h1>Student Portal</h1>
                <p>Bulacan State University Enrollment System</p>
            </div>
            <div id="loginMsg"></div>
            <form id="studentLoginForm">
                <div class="form-group">
                    <label class="form-label">Student Number</label>
                    <input type="text" name="student_no" class="form-control" placeholder="e.g. 2021-00001" required>
                </div>
                <div class="form-group password-field">
                    <label class="form-label">Password</label>
                    <div class="password-box">
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <svg class="icon-show" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="icon-hide" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94C16.1 19.23 14.07 20 12 20c-7 0-11-8-11-8a21.12 21.12 0 0 1 5.06-5.94M6.1 6.1C7.9 4.77 9.92 4 12 4c7 0 11 8 11 8a21.12 21.12 0 0 1-5.06 5.94"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;margin-top:8px">Sign In</button>
            </form>
            <p style="text-align:center;margin-top:20px;font-size:13px;color:var(--text-muted)">
                No account? <a href="/enrollment_system/student/register.php" style="color:var(--green)">Register here</a>
            </p>
            <p style="text-align:center;margin-top:8px;font-size:13px;color:var(--text-muted)">
                Admin? <a href="/enrollment_system/admin/login.php" style="color:var(--green)">Go to admin portal</a>
            </p>
            <p style="text-align:center;margin-top:10px;font-size:13px;color:var(--text-muted)">
                <a href="/enrollment_system/index.php" style="color:var(--text-muted)">← Back to Portal</a>
            </p>
        </div>
    </div>
</div>
<script src="/enrollment_system/assets/js/main.js"></script>
<script>
$(document).on('click', '.password-toggle', function() {
    var input = $(this).closest('.password-field').find('input');
    var shown = input.attr('type') === 'text';
    input.attr('type', shown ? 'password' : 'text');
    $(this).toggleClass('active', !shown);
    $(this).find('.icon-show').toggle(shown);
    $(this).find('.icon-hide').toggle(!shown);
});
</script>
</body>
</html>
