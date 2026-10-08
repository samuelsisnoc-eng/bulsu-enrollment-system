<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BulSU Enrollment System</title>
<link rel="stylesheet" href="/enrollment_system/assets/css/style.css">
<style>
.portal-page { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; }
.portal-header { text-align: center; margin-bottom: 56px; }
.portal-header .tag { display: inline-block; background: rgba(233,69,96,0.15); color: var(--highlight); border: 1px solid rgba(233,69,96,0.3); padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; }
.portal-header h1 { font-size: 48px; font-weight: 800; line-height: 1.1; margin-bottom: 12px; }
.portal-header h1 span { color: var(--highlight); }
.portal-header p { color: var(--text-muted); font-size: 16px; }
.portals { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; max-width: 700px; width: 100%; }
.portal-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 20px; padding: 40px 36px; text-align: center; text-decoration: none; color: var(--text); transition: all 0.25s; position: relative; overflow: hidden; }
.portal-card::before { content: ''; position: absolute; inset: 0; opacity: 0; transition: opacity 0.25s; }
.portal-card.student::before { background: linear-gradient(135deg, rgba(0,184,148,0.08), transparent); }
.portal-card.admin::before { background: linear-gradient(135deg, rgba(233,69,96,0.08), transparent); }
.portal-card:hover { transform: translateY(-4px); border-color: rgba(255,255,255,0.15); }
.portal-card:hover::before { opacity: 1; }
.portal-icon { font-size: 52px; margin-bottom: 20px; display: block; }
.portal-card h2 { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
.portal-card p { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; line-height: 1.5; }
.portal-btn { display: inline-block; padding: 10px 24px; border-radius: 10px; font-size: 13px; font-weight: 600; text-decoration: none; }
.student .portal-btn { background: rgba(0,184,148,0.15); color: var(--green); border: 1px solid rgba(0,184,148,0.3); }
.admin .portal-btn { background: rgba(233,69,96,0.15); color: var(--highlight); border: 1px solid rgba(233,69,96,0.3); }
</style>
</head>
<body>
<div class="wrapper">
    <div class="portal-page">
        <div class="portal-header">
            <div class="tag">🎓 Bulacan State University</div>
            <h1>Student Enrollment<br><span>Management System</span></h1>
            <p>IT 211 – Web Systems and Technologies | 2nd Semester 2025–2026</p>
        </div>
        <div class="portals">
            <a href="/enrollment_system/student/login.php" class="portal-card student">
                <span class="portal-icon">🎒</span>
                <h2>Student Portal</h2>
                <p>Register, submit enrollment, and view your enrollment status.</p>
                <span class="portal-btn">Enter as Student →</span>
            </a>
            <a href="/enrollment_system/admin/login.php" class="portal-card admin">
                <span class="portal-icon">🛡️</span>
                <h2>Admin Portal</h2>
                <p>Manage student records, approve enrollments, and view analytics.</p>
                <span class="portal-btn">Enter as Admin →</span>
            </a>
        </div>
    </div>
</div>
</body>
</html>
