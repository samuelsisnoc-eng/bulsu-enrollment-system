// assets/js/main.js

$(document).ready(function () {

    // ===== AJAX FORM SUBMISSION =====
    // Student Registration
    $('#registrationForm').on('submit', function (e) {
        e.preventDefault();
        const btn = $(this).find('[type=submit]');
        btn.prop('disabled', true).html('<span class="spinner"></span> Registering...');
        $.ajax({
            url: '/enrollment_system/student/ajax/register.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                showAlert(res.success ? 'success' : 'error', res.message, '#formMsg');
                if (res.success) {
                    setTimeout(() => window.location.href = '/enrollment_system/student/login.php', 1500);
                }
            },
            error: function () { showAlert('error', 'Server error. Please try again.', '#formMsg'); },
            complete: function () { btn.prop('disabled', false).html('Register'); }
        });
    });

    // Student Enrollment Submission
    $('#enrollmentForm').on('submit', function (e) {
        e.preventDefault();
        const btn = $(this).find('[type=submit]');
        btn.prop('disabled', true).html('<span class="spinner"></span> Submitting...');
        $.ajax({
            url: '/enrollment_system/student/ajax/submit_enrollment.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                showAlert(res.success ? 'success' : 'error', res.message, '#enrollMsg');
                if (res.success) setTimeout(() => location.reload(), 1500);
            },
            error: function () { showAlert('error', 'Server error. Please try again.', '#enrollMsg'); },
            complete: function () { btn.prop('disabled', false).html('Submit Enrollment'); }
        });
    });

    // Admin Login
    $('#adminLoginForm').on('submit', function (e) {
        e.preventDefault();
        const btn = $(this).find('[type=submit]');
        btn.prop('disabled', true).html('<span class="spinner"></span> Signing in...');
        $.ajax({
            url: '/enrollment_system/admin/ajax/login.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showAlert('success', res.message, '#loginMsg');
                    setTimeout(() => window.location.href = '/enrollment_system/admin/dashboard.php', 800);
                } else {
                    showAlert('error', res.message, '#loginMsg');
                }
            },
            error: function () { showAlert('error', 'Server error. Please try again.', '#loginMsg'); },
            complete: function () { btn.prop('disabled', false).html('Sign In'); }
        });
    });

    // Student Login
    $('#studentLoginForm').on('submit', function (e) {
        e.preventDefault();
        const btn = $(this).find('[type=submit]');
        btn.prop('disabled', true).html('<span class="spinner"></span> Signing in...');
        $.ajax({
            url: '/enrollment_system/student/ajax/login.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showAlert('success', res.message, '#loginMsg');
                    setTimeout(() => window.location.href = '/enrollment_system/student/dashboard.php', 800);
                } else {
                    showAlert('error', res.message, '#loginMsg');
                }
            },
            error: function () { showAlert('error', 'Server error.', '#loginMsg'); },
            complete: function () { btn.prop('disabled', false).html('Sign In'); }
        });
    });

    // ===== DYNAMIC STATUS CHECKING (AJAX) =====
    function checkEnrollmentStatus() {
        if ($('#statusBox').length === 0) return;
        $.ajax({
            url: '/enrollment_system/student/ajax/check_status.php',
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const statusMap = {
                        'Pending': { icon: '⏳', class: 'badge-pending', label: 'Pending Review' },
                        'Approved': { icon: '✅', class: 'badge-approved', label: 'Approved' },
                        'Rejected': { icon: '❌', class: 'badge-rejected', label: 'Rejected' }
                    };
                    const s = statusMap[res.status] || statusMap['Pending'];
                    $('#statusBox').html(
                        `<span class="status-icon">${s.icon}</span>
                        <h2>Enrollment Status</h2>
                        <span class="badge ${s.class}" style="font-size:16px;padding:8px 20px">${s.label}</span>
                        ${res.remarks ? `<p style="margin-top:16px">Remarks: ${res.remarks}</p>` : ''}
                        <p style="margin-top:8px;font-size:12px;color:var(--text-muted)">Last checked: ${new Date().toLocaleTimeString()}</p>`
                    );
                }
            }
        });
    }
    checkEnrollmentStatus();
    setInterval(checkEnrollmentStatus, 15000);

    // ===== ADMIN: APPROVE / REJECT ENROLLMENT =====
    // Moved to individual admin pages to avoid conflicts

    // ===== ADMIN: DELETE STUDENT =====
    $(document).on('click', '.btn-delete-student', function () {
        if (!confirm('Are you sure you want to delete this student?')) return;
        const id = $(this).data('id');
        const row = $(this).closest('tr');
        $.ajax({
            url: '/enrollment_system/admin/ajax/delete_student.php',
            method: 'POST',
            data: { student_id: id },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    row.fadeOut(300, function () { $(this).remove(); });
                    showToast('Student deleted.', 'success');
                } else {
                    showToast(res.message, 'error');
                }
            }
        });
    });

    // ===== ADMIN: SEARCH =====
    $('#searchInput').on('keyup', function () {
        const val = $(this).val().toLowerCase();
        $('tbody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(val));
        });
    });

    // ===== ADMIN: FILTER =====
    $('#filterCourse, #filterYear, #filterStatus').on('change', function () {
        applyFilters();
    });

    function applyFilters() {
        const course = $('#filterCourse').val().toLowerCase();
        const year = String($('#filterYear').val());
        const status = $('#filterStatus').val().toLowerCase();
        $('tbody tr').each(function () {
            const rowCourse = String($(this).data('course') || '').toLowerCase();
            const rowYear = String($(this).data('year') || '');
            const rowStatus = String($(this).data('status') || '').toLowerCase();
            const show = (!course || rowCourse.includes(course))
                && (!year || rowYear === year)
                && (!status || rowStatus === status);
            $(this).toggle(show);
        });
    }

    // ===== MODAL =====
    $(document).on('click', '[data-modal]', function () {
        const target = $(this).data('modal');
        $(`#${target}`).addClass('active');
    });

    $(document).on('click', '.modal-close, .modal-overlay', function (e) {
        if ($(e.target).hasClass('modal-overlay') || $(e.target).hasClass('modal-close')) {
            $('.modal-overlay').removeClass('active');
        }
    });

    // ===== ADMIN: EDIT STUDENT MODAL =====
    $(document).on('click', '.btn-edit-student', function () {
        const id = $(this).data('id');
        $.ajax({
            url: '/enrollment_system/admin/ajax/get_student.php',
            method: 'GET',
            data: { id },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const s = res.student;
                    $('#editStudentId').val(s.id);
                    $('#editFirstName').val(s.first_name);
                    $('#editLastName').val(s.last_name);
                    $('#editMiddleName').val(s.middle_name);
                    $('#editEmail').val(s.email);
                    $('#editPhone').val(s.phone);
                    $('#editCourse').val(s.course);
                    $('#editYear').val(s.year_level);
                    $('#editSection').val(s.section);
                    $('#editGender').val(s.gender);
                    $('#editModal').addClass('active');
                }
            }
        });
    });

    // Edit Student Submit
    $('#editStudentForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '/enrollment_system/admin/ajax/update_student.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#editModal').removeClass('active');
                    showToast('Student updated!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('error', res.message, '#editMsg');
                }
            }
        });
    });

    // ===== HELPERS =====
    function showAlert(type, message, target) {
        const icon = type === 'success' ? '✓' : '✕';
        $(target).html(`<div class="alert alert-${type}">${icon} ${message}</div>`).show();
    }

    function showToast(message, type) {
        const toast = $(`<div class="alert alert-${type}" style="position:fixed;bottom:24px;right:24px;z-index:9999;min-width:260px;animation:fadeIn 0.3s">${message}</div>`);
        $('body').append(toast);
        setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3000);
    }
});