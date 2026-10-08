<?php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
$conn = getConnection();

$schedules = $conn->query("
    SELECT sch.*, p.program_name, p.program_code, sub.subject_name, sub.subject_code as subj_code
    FROM schedules sch
    LEFT JOIN programs p ON sch.program_id = p.id
    LEFT JOIN subjects sub ON sch.subject_id = sub.id
    ORDER BY sch.program_id, sch.course_code, sch.section, FIELD(sch.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), sch.start_time ASC
");
$rows = [];
while ($r = $schedules->fetch_assoc()) $rows[] = $r;

$programs = $conn->query("SELECT * FROM programs ORDER BY program_code ASC");
$programList = [];
while ($r = $programs->fetch_assoc()) $programList[] = $r;

$subjects = $conn->query("SELECT s.*, p.program_code FROM subjects s LEFT JOIN programs p ON s.program_id = p.id ORDER BY s.subject_code ASC");
$subjectList = [];
while ($r = $subjects->fetch_assoc()) $subjectList[] = $r;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Schedules – BulSU Admin</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
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
                <a href="/enrollment_system/admin/schedules.php" class="sidebar-link active"><span class="icon">📅</span> Schedule</a>
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
        </aside>
        <main class="main-content">
            <div class="page-header" style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h1>Class Schedules</h1>
                    <p>Manage class schedules and assign subjects to courses</p>
                </div>
                <button class="btn btn-primary" onclick="openAdd()">Add Schedule</button>
            </div>
            <div id="pageMsg"></div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Professor</th>
                            <th>Room</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="scheduleTable">
                    <?php foreach ($rows as $s): ?>
                    <tr id="sched-row-<?= $s['id'] ?>">
                        <td>
                            <span class="badge" style="background:rgba(0,184,148,0.12);color:var(--green);border:1px solid rgba(0,184,148,0.25)">
                                <?= htmlspecialchars($s['program_code']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background:rgba(255,193,7,0.12);color:#d4a574;border:1px solid rgba(255,193,7,0.25)">
                                <?= htmlspecialchars($s['course_code']) ?>
                            </span>
                        </td>
                        <td style="font-weight:500"><?= htmlspecialchars($s['section']) ?></td>
                        <td>
                            <div style="font-weight:500"><?= htmlspecialchars($s['subject_name'] ?? 'N/A') ?></div>
                            <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($s['subj_code'] ?? '') ?></div>
                        </td>
                        <td>
                            <span class="badge" style="background:rgba(108,92,231,0.12);color:#6c5ce7;border:1px solid rgba(108,92,231,0.25)">
                                <?= htmlspecialchars($s['day_of_week']) ?>
                            </span>
                        </td>
                        <td>
                            <?= date('g:i A', strtotime($s['start_time'])) ?> - <?= date('g:i A', strtotime($s['end_time'])) ?>
                        </td>
                        <td style="font-size:13px"><?= htmlspecialchars($s['professor_name']) ?></td>
                        <td>
                            <span class="badge" style="background:rgba(116,185,255,0.12);color:#74b9ff;border:1px solid rgba(116,185,255,0.25)">
                                <?= htmlspecialchars($s['room_no']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-ghost btn-sm" onclick="openEdit(<?= $s['id'] ?>, <?= $s['program_id'] ?>, '<?= addslashes($s['course_code']) ?>', '<?= addslashes($s['section']) ?>', <?= $s['subject_id'] ?? 0 ?>, '<?= addslashes($s['day_of_week']) ?>', '<?= $s['start_time'] ?>', '<?= $s['end_time'] ?>', '<?= addslashes($s['professor_name']) ?>', '<?= addslashes($s['room_no']) ?>')">Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteSchedule(<?= $s['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?>
                    <tr><td colspan="9" style="text-align:center;padding:32px;color:var(--text-muted)">No schedules yet. Click "Add Schedule" to get started.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header"><h2>Add Schedule</h2><button class="modal-close" onclick="closeModals()">✕</button></div>
        <div id="addMsg"></div>
        <form id="addScheduleForm">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Program *</label>
                    <select name="program_id" id="addProgramSelect" class="form-control" required>
                        <option value="">Select Program</option>
                        <?php foreach ($programList as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['program_code']) ?> - <?= htmlspecialchars($p['program_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Course Code *</label>
                    <input type="text" name="course_code" class="form-control" placeholder="e.g. BSIT" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Section *</label>
                    <input type="text" name="section" class="form-control" placeholder="e.g. A" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" id="addSubjectSelect" class="form-control">
                        <option value="">Select Subject (Optional)</option>
                        <?php foreach ($subjectList as $sub): ?>
                        <option value="<?= $sub['id'] ?>" data-program="<?= $sub['program_id'] ?>"><?= htmlspecialchars($sub['subject_code']) ?> - <?= htmlspecialchars($sub['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Day of Week *</label>
                    <select name="day_of_week" class="form-control" required>
                        <option value="">Select Day</option>
                        <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
                        <option>Thursday</option><option>Friday</option><option>Saturday</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Time *</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Professor Name *</label>
                    <input type="text" name="professor_name" class="form-control" placeholder="e.g. Dr. Juan Dela Cruz" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Room No. *</label>
                    <input type="text" name="room_no" class="form-control" placeholder="e.g. Room 101" required>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px">
                <button type="submit" class="btn btn-primary">Save Schedule</button>
                <button type="button" class="btn btn-ghost" onclick="closeModals()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header"><h2>Edit Schedule</h2><button class="modal-close" onclick="closeModals()">✕</button></div>
        <div id="editMsg"></div>
        <form id="editScheduleForm">
            <input type="hidden" name="schedule_id" id="editScheduleId">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Program *</label>
                    <select name="program_id" id="editProgramSelect" class="form-control" required>
                        <option value="">Select Program</option>
                        <?php foreach ($programList as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['program_code']) ?> - <?= htmlspecialchars($p['program_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Course Code *</label>
                    <input type="text" name="course_code" id="editCourseCode" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Section *</label>
                    <input type="text" name="section" id="editSection" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" id="editSubjectSelect" class="form-control">
                        <option value="">Select Subject (Optional)</option>
                        <?php foreach ($subjectList as $sub): ?>
                        <option value="<?= $sub['id'] ?>" data-program="<?= $sub['program_id'] ?>"><?= htmlspecialchars($sub['subject_code']) ?> - <?= htmlspecialchars($sub['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Day of Week *</label>
                    <select name="day_of_week" id="editDayOfWeek" class="form-control" required>
                        <option value="">Select Day</option>
                        <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
                        <option>Thursday</option><option>Friday</option><option>Saturday</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" id="editStartTime" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Time *</label>
                    <input type="time" name="end_time" id="editEndTime" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Professor Name *</label>
                    <input type="text" name="professor_name" id="editProfessorName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Room No. *</label>
                    <input type="text" name="room_no" id="editRoomNo" class="form-control" required>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px">
                <button type="submit" class="btn btn-primary">Update Schedule</button>
                <button type="button" class="btn btn-ghost" onclick="closeModals()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdd() { document.getElementById('addModal').classList.add('active'); }
function openEdit(id, programId, courseCode, section, subjectId, dayOfWeek, startTime, endTime, professorName, roomNo) {
    document.getElementById('editScheduleId').value = id;
    document.getElementById('editProgramSelect').value = programId;
    document.getElementById('editCourseCode').value = courseCode;
    document.getElementById('editSection').value = section;
    document.getElementById('editSubjectSelect').value = subjectId;
    document.getElementById('editDayOfWeek').value = dayOfWeek;
    document.getElementById('editStartTime').value = startTime;
    document.getElementById('editEndTime').value = endTime;
    document.getElementById('editProfessorName').value = professorName;
    document.getElementById('editRoomNo').value = roomNo;
    document.getElementById('editModal').classList.add('active');
}
function closeModals() { document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active')); }

// Filter subjects by program
function filterSubjects(programId, subjectSelect) {
    $(subjectSelect + ' option').each(function() {
        var subjectProgram = $(this).data('program');
        if (!subjectProgram || subjectProgram == programId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    if ($(subjectSelect).val()) {
        var selectedProgram = $(subjectSelect + ' option:selected').data('program');
        if (selectedProgram != programId) {
            $(subjectSelect).val('');
        }
    }
}

$('#addProgramSelect').on('change', function() {
    filterSubjects($(this).val(), '#addSubjectSelect');
});

$('#editProgramSelect').on('change', function() {
    filterSubjects($(this).val(), '#editSubjectSelect');
});

$('#addScheduleForm').on('submit', function(e) {
    e.preventDefault();
    var btn = $(this).find('[type=submit]');
    btn.prop('disabled', true).text('Saving...');
    $.ajax({
        url: '/enrollment_system/admin/ajax/schedules.php',
        method: 'POST',
        data: $(this).serialize() + '&action=add',
        dataType: 'json',
        success: function(res) {
            if (res.success) { closeModals(); location.reload(); }
            else $('#addMsg').html('<div class="alert alert-error">' + res.message + '</div>');
        },
        error: function(xhr) {
            $('#addMsg').html('<div class="alert alert-error">Server error ' + xhr.status + ': ' + xhr.responseText + '</div>');
        },
        complete: function() { btn.prop('disabled', false).text('Save Schedule'); }
    });
});

$('#editScheduleForm').on('submit', function(e) {
    e.preventDefault();
    var btn = $(this).find('[type=submit]');
    btn.prop('disabled', true).text('Saving...');
    $.ajax({
        url: '/enrollment_system/admin/ajax/schedules.php',
        method: 'POST',
        data: $(this).serialize() + '&action=edit',
        dataType: 'json',
        success: function(res) {
            if (res.success) { closeModals(); location.reload(); }
            else $('#editMsg').html('<div class="alert alert-error">' + res.message + '</div>');
        },
        error: function(xhr) {
            $('#editMsg').html('<div class="alert alert-error">Server error ' + xhr.status + ': ' + xhr.responseText + '</div>');
        },
        complete: function() { btn.prop('disabled', false).text('Update Schedule'); }
    });
});

function deleteSchedule(id) {
    if (!confirm('Delete this schedule?')) return;
    $.ajax({
        url: '/enrollment_system/admin/ajax/schedules.php',
        method: 'POST',
        data: { action: 'delete', schedule_id: id },
        dataType: 'json',
        success: function(res) {
            if (res.success) $('#sched-row-' + id).fadeOut(300, function(){ $(this).remove(); });
            else alert(res.message);
        }
    });
}

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) { if (e.target === this) closeModals(); });
});
</script>
</body>
</html>