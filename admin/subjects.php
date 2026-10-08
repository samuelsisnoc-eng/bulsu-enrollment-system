<?php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
$conn = getConnection();

$conn->query("CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL,
    subject_name VARCHAR(150) NOT NULL,
    units INT NOT NULL DEFAULT 3,
    program_id INT,
    college_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE,
    UNIQUE KEY unique_subject_program (subject_code, program_id)
)");

$subjects = $conn->query("
    SELECT s.*, p.program_name, p.program_code, c.college_name, c.college_code
    FROM subjects s
    LEFT JOIN programs p ON s.program_id = p.id
    LEFT JOIN colleges c ON s.college_id = c.id
    ORDER BY s.subject_code ASC
");
$rows = [];
while ($r = $subjects->fetch_assoc()) $rows[] = $r;

$colleges = $conn->query("SELECT * FROM colleges ORDER BY college_code ASC");
$collegeList = [];
while ($r = $colleges->fetch_assoc()) $collegeList[] = $r;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subjects – BulSU Admin</title>
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
        </aside>
        <main class="main-content">
            <div class="page-header" style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h1>Subjects</h1>
                    <p>Manage academic subjects and their units</p>
                </div>
                <button class="btn btn-primary" onclick="openAdd()">Add Subject</button>
            </div>
            <div id="pageMsg"></div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Units</th>
                            <th>College</th>
                            <th>Program</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="subjectTable">
                    <?php foreach ($rows as $i => $s): ?>
                    <tr id="subj-row-<?= $s['id'] ?>">
                        <td style="color:var(--text-muted);font-size:12px"><?= $i+1 ?></td>
                        <td><span class="badge" style="background:rgba(255,107,107,0.12);color:#ff6b6b;border:1px solid rgba(255,107,107,0.25)"><?= htmlspecialchars($s['subject_code']) ?></span></td>
                        <td style="font-weight:500"><?= htmlspecialchars($s['subject_name']) ?></td>
                        <td style="font-size:13px"><?= $s['units'] ?> units</td>
                        <td>
                            <?php if ($s['college_code']): ?>
                            <span class="badge" style="background:rgba(116,185,255,0.12);color:#74b9ff;border:1px solid rgba(116,185,255,0.25)"><?= htmlspecialchars($s['college_code']) ?></span>
                            <?php else: ?><span style="color:var(--text-muted)">—</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($s['program_code']): ?>
                            <span class="badge" style="background:rgba(0,184,148,0.12);color:var(--green);border:1px solid rgba(0,184,148,0.25)"><?= htmlspecialchars($s['program_code']) ?></span>
                            <?php else: ?><span style="color:var(--text-muted)">—</span><?php endif; ?>
                        </td>
                        <td style="color:var(--text-muted);font-size:12px"><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-ghost btn-sm" onclick="openEdit(<?= $s['id'] ?>, '<?= addslashes($s['subject_code']) ?>', '<?= addslashes($s['subject_name']) ?>', <?= $s['units'] ?>, <?= $s['college_id'] ?? 0 ?>, <?= $s['program_id'] ?? 0 ?>)">Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteSubject(<?= $s['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--text-muted)">No subjects yet. Click "Add Subject" to get started.</td></tr>
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
        <div class="modal-header"><h2>Add Subject</h2><button class="modal-close" onclick="closeModals()">✕</button></div>
        <div id="addMsg"></div>
        <form id="addSubjectForm">
            <div class="form-group">
                <label class="form-label">College *</label>
                <select name="college_id" id="addCollegeSelect" class="form-control" required>
                    <option value="">Select College</option>
                    <?php foreach ($collegeList as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['college_code']) ?> - <?= htmlspecialchars($c['college_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Program *</label>
                <select name="program_id" id="addProgramSelect" class="form-control" required disabled>
                    <option value="">Select Program</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Subject Code *</label><input type="text" name="subject_code" class="form-control" placeholder="e.g. CS101" required></div>
            <div class="form-group"><label class="form-label">Subject Name *</label><input type="text" name="subject_name" class="form-control" placeholder="e.g. Introduction to Computer Science" required></div>
            <div class="form-group"><label class="form-label">Units *</label><input type="number" name="units" class="form-control" min="1" max="6" value="3" required></div>
            <div style="display:flex;gap:12px;margin-top:8px">
                <button type="submit" class="btn btn-primary">Save Subject</button>
                <button type="button" class="btn btn-ghost" onclick="closeModals()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header"><h2>Edit Subject</h2><button class="modal-close" onclick="closeModals()">✕</button></div>
        <div id="editMsg"></div>
        <form id="editSubjectForm">
            <input type="hidden" name="subject_id" id="editSubjectId">
            <div class="form-group">
                <label class="form-label">College *</label>
                <select name="college_id" id="editCollegeSelect" class="form-control" required>
                    <option value="">Select College</option>
                    <?php foreach ($collegeList as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['college_code']) ?> - <?= htmlspecialchars($c['college_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Program *</label>
                <select name="program_id" id="editProgramSelect" class="form-control" required disabled>
                    <option value="">Select Program</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Subject Code *</label><input type="text" name="subject_code" id="editCode" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Subject Name *</label><input type="text" name="subject_name" id="editName" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Units *</label><input type="number" name="units" id="editUnits" class="form-control" min="1" max="6" required></div>
            <div style="display:flex;gap:12px;margin-top:8px">
                <button type="submit" class="btn btn-primary">Update Subject</button>
                <button type="button" class="btn btn-ghost" onclick="closeModals()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdd() { document.getElementById('addModal').classList.add('active'); }
function openEdit(id, code, name, units, collegeId, programId) {
    document.getElementById('editSubjectId').value = id;
    document.getElementById('editCode').value = code;
    document.getElementById('editName').value = name;
    document.getElementById('editUnits').value = units;
    document.getElementById('editCollegeSelect').value = collegeId;
    loadProgramsForEdit(collegeId, programId);
    document.getElementById('editModal').classList.add('active');
}
function closeModals() { document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active')); }

// Load programs when college is selected
function loadPrograms(collegeId, targetSelect) {
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
                        options += '<option value="' + program.id + '">' + program.program_code + ' - ' + program.program_name + '</option>';
                    });
                    $(targetSelect).html(options).prop('disabled', false);
                }
            }
        });
    } else {
        $(targetSelect).html('<option value="">Select Program</option>').prop('disabled', true);
    }
}

function loadProgramsForEdit(collegeId, selectedProgramId) {
    loadPrograms(collegeId, '#editProgramSelect');
    setTimeout(function() {
        $('#editProgramSelect').val(selectedProgramId);
    }, 500);
}

$('#addCollegeSelect').on('change', function() {
    loadPrograms($(this).val(), '#addProgramSelect');
});

$('#editCollegeSelect').on('change', function() {
    loadPrograms($(this).val(), '#editProgramSelect');
});

$('#addSubjectForm').on('submit', function(e) {
    e.preventDefault();
    var btn = $(this).find('[type=submit]');
    btn.prop('disabled', true).text('Saving...');
    $.ajax({
        url: '/enrollment_system/admin/ajax/subjects.php',
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
        complete: function() { btn.prop('disabled', false).text('Save Subject'); }
    });
});

$('#editSubjectForm').on('submit', function(e) {
    e.preventDefault();
    var btn = $(this).find('[type=submit]');
    btn.prop('disabled', true).text('Saving...');
    $.ajax({
        url: '/enrollment_system/admin/ajax/subjects.php',
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
        complete: function() { btn.prop('disabled', false).text('Update Subject'); }
    });
});

function deleteSubject(id) {
    if (!confirm('Delete this subject?')) return;
    $.ajax({
        url: '/enrollment_system/admin/ajax/subjects.php',
        method: 'POST',
        data: { action: 'delete', subject_id: id },
        dataType: 'json',
        success: function(res) {
            if (res.success) $('#subj-row-' + id).fadeOut(300, function(){ $(this).remove(); });
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