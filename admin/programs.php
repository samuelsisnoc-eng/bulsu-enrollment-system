<?php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
$conn = getConnection();

$conn->query("CREATE TABLE IF NOT EXISTS colleges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    college_code VARCHAR(20) NOT NULL UNIQUE,
    college_name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_code VARCHAR(20) NOT NULL UNIQUE,
    program_name VARCHAR(150) NOT NULL,
    college_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE SET NULL
)");

$programs = $conn->query("SELECT p.*, c.college_name, c.college_code FROM programs p LEFT JOIN colleges c ON p.college_id = c.id ORDER BY p.program_code ASC");
$rows = [];
while ($r = $programs->fetch_assoc()) $rows[] = $r;

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
<title>Programs – BulSU Admin</title>
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
                <a href="/enrollment_system/admin/programs.php" class="sidebar-link active"><span class="icon">📚</span> Programs</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">Actions</div>
                <a href="/enrollment_system/admin/add_student.php" class="sidebar-link"><span class="icon">➕</span> Add Student</a>
            </div>
        </aside>
        <main class="main-content">
            <div class="page-header" style="display:flex;align-items:center;justify-content:space-between">
                <div><h1>Programs</h1><p>Manage academic programs</p></div>
                <button class="btn btn-primary" onclick="openAdd()">Add Program</button>
            </div>
            <div id="pageMsg"></div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Program Code</th>
                            <th>Program Name</th>
                            <th>College</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $i => $p): ?>
                    <tr id="prog-row-<?= $p['id'] ?>">
                        <td style="color:var(--text-muted);font-size:12px"><?= $i+1 ?></td>
                        <td><span class="badge" style="background:rgba(0,184,148,0.12);color:var(--green);border:1px solid rgba(0,184,148,0.25)"><?= htmlspecialchars($p['program_code']) ?></span></td>
                        <td style="font-weight:500"><?= htmlspecialchars($p['program_name']) ?></td>
                        <td>
                            <?php if ($p['college_code']): ?>
                            <span class="badge" style="background:rgba(116,185,255,0.12);color:#74b9ff;border:1px solid rgba(116,185,255,0.25)"><?= htmlspecialchars($p['college_code']) ?></span>
                            <span style="color:var(--text-muted);font-size:12px;margin-left:6px"><?= htmlspecialchars($p['college_name']) ?></span>
                            <?php else: ?><span style="color:var(--text-muted)">—</span><?php endif; ?>
                        </td>
                        <td style="color:var(--text-muted);font-size:12px"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-ghost btn-sm" onclick="openEdit(<?= $p['id'] ?>, '<?= addslashes($p['program_code']) ?>', '<?= addslashes($p['program_name']) ?>', <?= $p['college_id'] ?? 0 ?>)">Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteProgram(<?= $p['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?>
                    <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted)">No programs yet. Click "Add Program" to get started.</td></tr>
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
        <div class="modal-header"><h2>Add Program</h2><button class="modal-close" onclick="closeModals()">✕</button></div>
        <div id="addMsg"></div>
        <form id="addProgramForm">
            <div class="form-group"><label class="form-label">Program Code *</label><input type="text" name="program_code" class="form-control" placeholder="e.g. BSIT" required></div>
            <div class="form-group"><label class="form-label">Program Name *</label><input type="text" name="program_name" class="form-control" placeholder="e.g. Bachelor of Science in Information Technology" required></div>
            <div class="form-group">
                <label class="form-label">College</label>
                <select name="college_id" class="form-control">
                    <option value="">Select College</option>
                    <?php foreach ($collegeList as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['college_code']) ?> – <?= htmlspecialchars($c['college_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px">
                <button type="submit" class="btn btn-primary">Save Program</button>
                <button type="button" class="btn btn-ghost" onclick="closeModals()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header"><h2>Edit Program</h2><button class="modal-close" onclick="closeModals()">✕</button></div>
        <div id="editMsg"></div>
        <form id="editProgramForm">
            <input type="hidden" name="program_id" id="editProgId">
            <div class="form-group"><label class="form-label">Program Code *</label><input type="text" name="program_code" id="editCode" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Program Name *</label><input type="text" name="program_name" id="editName" class="form-control" required></div>
            <div class="form-group">
                <label class="form-label">College</label>
                <select name="college_id" id="editCollege" class="form-control">
                    <option value="">Select College</option>
                    <?php foreach ($collegeList as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['college_code']) ?> – <?= htmlspecialchars($c['college_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px">
                <button type="submit" class="btn btn-primary">Update Program</button>
                <button type="button" class="btn btn-ghost" onclick="closeModals()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdd() { document.getElementById('addModal').classList.add('active'); }
function openEdit(id, code, name, collegeId) {
    document.getElementById('editProgId').value = id;
    document.getElementById('editCode').value = code;
    document.getElementById('editName').value = name;
    document.getElementById('editCollege').value = collegeId;
    document.getElementById('editModal').classList.add('active');
}
function closeModals() { document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active')); }

$('#addProgramForm').on('submit', function(e) {
    e.preventDefault();
    var btn = $(this).find('[type=submit]');
    btn.prop('disabled', true).text('Saving...');
    $.ajax({
        url: '/enrollment_system/admin/ajax/programs.php',
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
        complete: function() { btn.prop('disabled', false).text('Save Program'); }
    });
});

$('#editProgramForm').on('submit', function(e) {
    e.preventDefault();
    var btn = $(this).find('[type=submit]');
    btn.prop('disabled', true).text('Saving...');
    $.ajax({
        url: '/enrollment_system/admin/ajax/programs.php',
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
        complete: function() { btn.prop('disabled', false).text('Update Program'); }
    });
});

function deleteProgram(id) {
    if (!confirm('Delete this program?')) return;
    $.ajax({
        url: '/enrollment_system/admin/ajax/programs.php',
        method: 'POST',
        data: { action: 'delete', program_id: id },
        dataType: 'json',
        success: function(res) {
            if (res.success) $('#prog-row-' + id).fadeOut(300, function(){ $(this).remove(); });
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