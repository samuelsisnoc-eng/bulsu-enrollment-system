<?php
require_once '../includes/session.php';
requireStudent();
require_once '../config/database.php';
$conn = getConnection();
$studentId = $_SESSION['student_id'];

$student  = $conn->query("SELECT course, year_level FROM students WHERE id = $studentId")->fetch_assoc();

// Get all already-enrolled subjects (from any enrollment)
$enrolledResult = $conn->query("SELECT subjects FROM enrollments WHERE student_id = $studentId");
$enrolledSubjects = [];
while ($row = $enrolledResult->fetch_assoc()) {
    if (!empty($row['subjects'])) {
        $subs = array_map('trim', explode(',', $row['subjects']));
        foreach ($subs as $s) {
            if (!empty($s)) $enrolledSubjects[] = $s;
        }
    }
}
$enrolledSubjects = array_unique($enrolledSubjects);

// Check if there is a currently PENDING enrollment (block only pending)
$pending = $conn->query("SELECT id FROM enrollments WHERE student_id = $studentId AND status = 'Pending' LIMIT 1")->fetch_assoc();

$conn->close();

$course = $student['course'] ?? 'BSIT';
$year   = $student['year_level'] ?? '1';

$subjectMap = [
    '2025-2026' => [
        '1st' => [
            'IT 205 - Quantitative Methods',
            'IT 203 - Object-Oriented Programming',
            'IT 204 - Networking 1',
            'CC 106 - Information Management',
            'CC 105 - Data Structures and Algorithms',
            'STS 101 - Science, Technology and Society',
            'ETH 101 - Ethics',
            'PE 12 - PATHFit 3',
            'RLW - Life and Works of Rizal',
        ],
        '2nd' => [
            'AAP 101 - Art Appreciation',
            'PE 13 - Physical Education 4',
            'FL 101 - Foreign Language',
            'IT 206 - Event-Driven Programming',
            'IT 207 - Integrative Programming and Technologies',
            'IT 208 - Advanced Database Systems',
            'IT 209 - Introduction to Data Analytics',
            'IT 210 - Networking 2',
            'IT 211 - Web Systems and Technologies',
        ],
        'Summer' => [
            'IT 205 - Quantitative Methods',
            'IT 203 - Object-Oriented Programming',
            'IT 204 - Networking 1',
            'CC 106 - Information Management',
            'CC 105 - Data Structures and Algorithms',
            'STS 101 - Science, Technology and Society',
            'ETH 101 - Ethics',
            'PE 12 - PATHFit 3',
            'RLW - Life and Works of Rizal',
            'AAP 101 - Art Appreciation',
            'PE 13 - Physical Education 4',
            'FL 101 - Foreign Language',
            'IT 206 - Event-Driven Programming',
            'IT 207 - Integrative Programming and Technologies',
            'IT 208 - Advanced Database Systems',
            'IT 209 - Introduction to Data Analytics',
            'IT 210 - Networking 2',
            'IT 211 - Web Systems and Technologies',
        ],
    ],
    '2026-2027' => [
        '1st' => [
            'IT 301 - Platform Technologies',
            'IT 302 - Systems Integration and Architecture',
            'IT 303 - Information Assurance and Security 1',
            'IT 304 - Social and Professional Issues in IT',
            'IT 305 - Application Development and Emerging Technologies',
            'CC 201 - Quantitative Methods in Computing',
            'GE 101 - Understanding the Self',
            'PE 14 - PATHFit 4',
        ],
        '2nd' => [
            'IT 306 - Human Computer Interaction',
            'IT 307 - Information Assurance and Security 2',
            'IT 308 - Systems Administration and Maintenance',
            'IT 309 - Capstone Project and Research 1',
            'IT 310 - IT Elective 1 - Cybersecurity Fundamentals',
            'IT 311 - IT Elective 2 - Cloud Computing',
            'GE 102 - Readings in Philippine History',
            'GE 103 - Mathematics in the Modern World',
        ],
        'Summer' => [
            'IT 301 - Platform Technologies',
            'IT 302 - Systems Integration and Architecture',
            'IT 303 - Information Assurance and Security 1',
            'IT 304 - Social and Professional Issues in IT',
            'IT 305 - Application Development and Emerging Technologies',
            'IT 306 - Human Computer Interaction',
            'IT 307 - Information Assurance and Security 2',
            'IT 308 - Systems Administration and Maintenance',
            'IT 309 - Capstone Project and Research 1',
            'IT 310 - IT Elective 1 - Cybersecurity Fundamentals',
            'IT 311 - IT Elective 2 - Cloud Computing',
            'GE 101 - Understanding the Self',
            'GE 102 - Readings in Philippine History',
            'GE 103 - Mathematics in the Modern World',
        ],
    ],
];

$subjectMapJson = json_encode($subjectMap);
$enrolledSubjectsJson = json_encode(array_values($enrolledSubjects));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enroll - BulSU Enrollment</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
.subject-dropdown { position: relative; }
.subject-trigger {
    width: 100%;
    background: var(--input-bg);
    border: 1px solid var(--card-border);
    border-radius: var(--radius-sm);
    color: var(--text);
    padding: 12px 16px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: border-color 0.2s;
    user-select: none;
}
.subject-trigger:hover { border-color: var(--highlight); }
.subject-trigger.open { border-color: var(--highlight); border-radius: var(--radius-sm) var(--radius-sm) 0 0; }
.subject-trigger .arrow { transition: transform 0.2s; font-size: 12px; }
.subject-trigger.open .arrow { transform: rotate(180deg); }
.subject-options {
    display: none;
    position: static;
    background: var(--secondary);
    border: 1px solid var(--card-border);
    border-top: none;
    border-radius: 0 0 var(--radius-sm) var(--radius-sm);
    z-index: 10;
    max-height: none;
    overflow-y: visible;
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
}
.subject-options.open { display: block; }
.subject-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    font-size: 14px;
    color: var(--text);
    transition: background 0.15s;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.subject-option:last-child { border-bottom: none; }
.subject-option:hover { background: rgba(255,255,255,0.05); }
.subject-option.checked { background: rgba(233,69,96,0.06); }
.subject-option input[type=checkbox] {
    width: 16px; height: 16px;
    accent-color: var(--highlight);
    cursor: pointer;
    flex-shrink: 0;
}
.select-actions {
    display: flex;
    gap: 8px;
    padding: 10px 16px;
    border-bottom: 1px solid var(--card-border);
    background: rgba(255,255,255,0.02);
}
.select-actions button {
    background: none;
    border: 1px solid var(--card-border);
    color: var(--text-muted);
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
}
.select-actions button:hover { color: var(--text); border-color: var(--text-muted); }
.selected-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.selected-tag {
    background: rgba(233,69,96,0.12);
    border: 1px solid rgba(233,69,96,0.3);
    color: var(--highlight);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.selected-tag .remove { cursor: pointer; font-size: 14px; opacity: 0.7; line-height: 1; }
.selected-tag .remove:hover { opacity: 1; }
.no-subjects {
    padding: 20px 16px;
    text-align: center;
    color: var(--text-muted);
    font-size: 13px;
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
            <a href="/enrollment_system/student/dashboard.php" class="nav-link">Dashboard</a>
            <a href="/enrollment_system/student/logout.php" class="nav-link btn-danger">Logout</a>
        </div>
    </nav>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Student</div>
                <a href="/enrollment_system/student/dashboard.php" class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
                <a href="/enrollment_system/student/enroll.php" class="sidebar-link active"><span class="icon">📝</span> Enroll Now</a>
                <a href="/enrollment_system/student/status.php" class="sidebar-link"><span class="icon">🔍</span> My Status</a>
                <a href="/enrollment_system/student/profile.php" class="sidebar-link"><span class="icon">👤</span> My Profile</a>                <a href="/enrollment_system/student/calendar.php" class="sidebar-link"><span class="icon">📅</span> Academic Calendar</a>
                <a href="/enrollment_system/student/schedule.php" class="sidebar-link"><span class="icon">🕒</span> Class Schedule</a>            </div>
        </aside>
        <main class="main-content">
            <div class="page-header">
                <h1>Submit Enrollment</h1>
                <p><?= htmlspecialchars($course) ?> – Year <?= $year ?></p>
            </div>

            <?php if (!empty($enrolledSubjects)): ?>
            <div class="alert alert-info">
                Already enrolled subjects are hidden from the list below: <strong><?= implode(', ', $enrolledSubjects) ?></strong>
            </div>
            <?php endif; ?>
            <div class="card enrollment-form">
                <div class="card-header"><h2>Enrollment Form</h2></div>
                <div class="card-body">
                    <div id="enrollMsg"></div>
                    <form id="enrollmentForm">

                        <div class="form-row">
                            <!-- Academic Year Dropdown -->
                            <div class="form-group">
                                <label class="form-label">Academic Year *</label>
                                <select name="academic_year" id="academicYear" class="form-control" required>
                                    <option value="">Select Academic Year</option>
                                    <option value="2025-2026">2025-2026</option>
                                    <option value="2026-2027">2026-2027</option>
                                </select>
                            </div>

                            <!-- Semester Dropdown (updates based on academic year) -->
                            <div class="form-group">
                                <label class="form-label">Semester *</label>
                                <select name="semester" id="semesterSelect" class="form-control" required>
                                    <option value="">Select Academic Year first</option>
                                </select>
                            </div>
                        </div>

                        <!-- Subjects Dropdown (updates based on academic year + semester) -->
                        <div class="form-group">
                            <label class="form-label">Subjects to Enroll * <span style="color:var(--text-muted);font-weight:400">(select all that apply)</span></label>
                            <div class="subject-dropdown">
                                <div class="subject-trigger" id="subjectTrigger">
                                    <span id="triggerText">Select academic year and semester first...</span>
                                    <span class="arrow">&#9660;</span>
                                </div>
                                <div class="subject-options" id="subjectOptions">
                                    <div class="no-subjects">Please select academic year and semester first.</div>
                                </div>
                            </div>
                            <div class="selected-tags" id="selectedTags"></div>
                            <input type="hidden" name="subjects" id="subjectsHidden">
                        </div>

                        <div style="background:rgba(255,255,255,0.04);border:1px solid var(--card-border);border-radius:12px;padding:18px;margin-bottom:20px">
                            <p style="font-size:13px;color:var(--text-muted)">By submitting this form, you confirm that the information provided is accurate. Your enrollment is subject to admin approval.</p>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Enrollment</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="/enrollment_system/assets/js/main.js"></script>
<script>
var subjectMap = <?= $subjectMapJson ?>;
var enrolledSubjects = <?= $enrolledSubjectsJson ?>;

var semesterLabels = {
    '1st':    '1st Semester',
    '2nd':    '2nd Semester',
    'Summer': 'Summer Class'
};

// Academic year change — update semester options
$('#academicYear').on('change', function () {
    var ay = $(this).val();
    var semSel = $('#semesterSelect');
    semSel.empty().append('<option value="">Select Semester</option>');
    $('#subjectOptions').html('<div class="no-subjects">Please select a semester.</div>');
    $('#triggerText').text('Select a semester first...');
    clearSelected();

    if (ay && subjectMap[ay]) {
        $.each(subjectMap[ay], function (semKey) {
            semSel.append('<option value="' + semKey + '">' + semesterLabels[semKey] + ' (' + ay + ')</option>');
        });
    }
});

// Semester change — update subject list
$('#semesterSelect').on('change', function () {
    var ay  = $('#academicYear').val();
    var sem = $(this).val();
    clearSelected();

    if (ay && sem && subjectMap[ay] && subjectMap[ay][sem]) {
        var subjects = subjectMap[ay][sem];
        var html = '<div class="select-actions">';
        html += '<button type="button" id="selectAll">Select All</button>';
        html += '<button type="button" id="clearAll">Clear All</button>';
        html += '</div>';
        subjects.forEach(function (subject) {
            var code = subject.split(' - ')[0].trim();
            // Skip already enrolled subjects
            if (enrolledSubjects.indexOf(code) !== -1) return;
            html += '<label class="subject-option">';
            html += '<input type="checkbox" class="subject-check" value="' + code + '">';
            html += '<span>' + subject + '</span>';
            html += '</label>';
        });
        // If all subjects already enrolled
        var available = subjects.filter(function(s) {
            return enrolledSubjects.indexOf(s.split(' - ')[0].trim()) === -1;
        });
        if (available.length === 0) {
            html += '<div class="no-subjects">All subjects for this semester are already enrolled.</div>';
        }
        $('#subjectOptions').html(html);
        $('#triggerText').text('Click to select subjects...');
    } else {
        $('#subjectOptions').html('<div class="no-subjects">No subjects available.</div>');
        $('#triggerText').text('Select academic year and semester first...');
    }
});

// Toggle dropdown
$('#subjectTrigger').on('click', function () {
    if (!$('#academicYear').val() || !$('#semesterSelect').val()) return;
    $(this).toggleClass('open');
    $('#subjectOptions').toggleClass('open');
});

// Close when clicking outside
$(document).on('click', function (e) {
    if (!$(e.target).closest('.subject-dropdown').length) {
        $('#subjectTrigger').removeClass('open');
        $('#subjectOptions').removeClass('open');
    }
});

$(document).on('click', '#selectAll', function () {
    $('.subject-check').prop('checked', true);
    updateSelected();
});

$(document).on('click', '#clearAll', function () {
    $('.subject-check').prop('checked', false);
    updateSelected();
});

$(document).on('change', '.subject-check', function () {
    updateSelected();
});

$(document).on('click', '.remove', function () {
    var val = $(this).data('val');
    $('.subject-check[value="' + val + '"]').prop('checked', false);
    updateSelected();
});

function clearSelected() {
    $('#subjectsHidden').val('');
    $('#selectedTags').html('');
    $('#triggerText').text('Click to select subjects...');
}

function updateSelected() {
    var selected = [];
    $('.subject-check:checked').each(function () {
        selected.push($(this).val());
    });
    $('.subject-option').removeClass('checked');
    $('.subject-check:checked').each(function () {
        $(this).closest('.subject-option').addClass('checked');
    });
    $('#subjectsHidden').val(selected.join(','));
    $('#triggerText').text(selected.length === 0
        ? 'Click to select subjects...'
        : selected.length + ' subject' + (selected.length > 1 ? 's' : '') + ' selected');
    var tagsHtml = '';
    selected.forEach(function (s) {
        tagsHtml += '<span class="selected-tag">' + s + '<span class="remove" data-val="' + s + '">&times;</span></span>';
    });
    $('#selectedTags').html(tagsHtml);
}
</script>
</body>
</html>