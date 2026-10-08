<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Registration – BulSU Enrollment</title>
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
            <a href="/enrollment_system/student/login.php" class="nav-link">Already have an account? Sign In</a>
        </div>
    </nav>
    <div style="max-width:700px;margin:40px auto;padding:0 24px">
        <div class="page-header">
            <h1>Student Registration</h1>
            <p>Fill out the form to create your enrollment account</p>
        </div>
        <div class="card">
            <div class="card-body">
                <div id="formMsg"></div>
                <form id="registrationForm">
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
                                <option>Male</option>
                                <option>Female</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Birthdate *</label>
                            <input type="date" name="birthdate" class="form-control" id="birthdate" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Age</label>
                            <input type="text" id="ageDisplay" class="form-control" readonly placeholder="Age will appear here" style="background:rgba(255,255,255,0.05);color:var(--text);">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="09XXXXXXXXX" pattern="[0-9]*" title="Please enter only numbers">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    <p style="font-family:'Syne',sans-serif;font-weight:700;font-size:13px;letter-spacing:1px;text-transform:uppercase;color:var(--highlight);margin:24px 0 16px">Academic Information</p>
                    <div class="form-row three">
                        <div class="form-group">
                            <label class="form-label">College *</label>
                            <select name="college_id" id="collegeSelect" class="form-control" required>
                                <option value="">Select College</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Program *</label>
                            <select name="program_id" id="programSelect" class="form-control" required disabled>
                                <option value="">Select Program</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Course *</label>
                            <select name="course" id="courseSelect" class="form-control" required disabled>
                                <option value="">Select Course</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Year Level *</label>
                            <select name="year_level" class="form-control" required>
                                <option value="">Year</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Section</label>
                            <input type="text" name="section" class="form-control" placeholder="e.g. A">
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
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px">Register</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="/enrollment_system/assets/js/main.js"></script>
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
        $('#ageDisplay').val(age ? age + ' years' : '');
    });

    $(document).on('click', '.password-toggle', function() {
        var input = $(this).closest('.password-field').find('input');
        var shown = input.attr('type') === 'text';
        input.attr('type', shown ? 'password' : 'text');
        $(this).find('.icon-show').toggle(shown);
        $(this).find('.icon-hide').toggle(!shown);
    });

    // Initialize age box if a birthdate is already set
    var initialAge = calculateAge($('#birthdate').val());
    $('#ageDisplay').val(initialAge ? initialAge + ' years' : '');

    // Load colleges
    $.ajax({
        url: '/enrollment_system/admin/ajax/colleges.php',
        method: 'GET',
        data: { action: 'get_colleges' },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                var options = '<option value="">Select College</option>';
                res.colleges.forEach(function(college) {
                    options += '<option value="' + college.id + '">' + college.college_code + ' - ' + college.college_name + '</option>';
                });
                $('#collegeSelect').html(options);
            }
        }
    });

    // Load programs when college is selected
    $('#collegeSelect').on('change', function() {
        var collegeId = $(this).val();
        if (collegeId) {
            $.ajax({
                url: '/enrollment_system/admin/ajax/programs.php',
                method: 'GET',
                data: { action: 'get_programs', college_id: collegeId },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        var options = '<option value="">Select Program</option>';
                        res.programs.forEach(function(program) {
                            options += '<option value="' + program.id + '" data-code="' + program.program_code + '">' + program.program_code + ' - ' + program.program_name + '</option>';
                        });
                        $('#programSelect').html(options).prop('disabled', false);
                        $('#courseSelect').html('<option value="">Select Course</option>').prop('disabled', true);
                    }
                }
            });
        } else {
            $('#programSelect').html('<option value="">Select Program</option>').prop('disabled', true);
            $('#courseSelect').html('<option value="">Select Course</option>').prop('disabled', true);
        }
    });

    // Load course when program is selected
    $('#programSelect').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var programCode = selectedOption.data('code');
        if (programCode) {
            $('#courseSelect').html('<option value="' + programCode + '">' + programCode + '</option>').prop('disabled', false);
        } else {
            $('#courseSelect').html('<option value="">Select Course</option>').prop('disabled', true);
        }
    });
});
</script>
</body>
</html>
