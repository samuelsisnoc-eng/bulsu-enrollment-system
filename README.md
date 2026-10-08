# BulSU Online Enrollment System

A full-stack web enrollment system with separate student and admin portals, built for **IT 211 – Web Systems and Technologies** (2nd Semester, 2025–2026) at Bulacan State University.

Students register and submit enrollment requests. Admins review and approve them, manage academic records, and view enrollment analytics.

## Features

### Student Portal
- Registration and login
- Submit an enrollment request (program, subjects, year level)
- Track enrollment status (Pending, Approved, or Rejected)
- View class schedule and academic calendar
- Edit profile

### Admin Portal
- Login for administrators
- Dashboard with 6 charts (Chart.js):
  - Enrollments by course
  - Enrollments by year level
  - Students by course
  - Enrollments per academic year
  - Enrollments per semester
  - Daily enrollments (last 7 days)
- Approve or reject enrollment requests
- Add, edit, and delete students
- Manage colleges, programs, subjects, and schedules
- Notifications

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP (MySQLi) |
| Database | MySQL |
| Frontend | HTML, CSS, JavaScript (AJAX) |
| Charts | Chart.js |
| Local server | XAMPP |

## Database

The schema has 9 tables: `students`, `admins`, `enrollments`, `colleges`, `programs`, `subjects`, `schedules`, `notifications`, and `academic_calendar`.

## Security

- Passwords are hashed with PHP's `password_hash()` and checked with `password_verify()`
- Database queries use prepared statements

## How to Run Locally

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Copy this project folder into `C:\xampp\htdocs\` and make sure it is named **`enrollment_system`** (the links in the project expect this folder name).
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`), go to the **SQL** tab, and run the contents of `database/complete_database.sql`. This creates the `enrollment_db` database, its tables, and sample data.
4. Open `http://localhost/enrollment_system/` in your browser.

The database settings are in `config/database.php` (default XAMPP: user `root`, empty password). Change them if your setup is different.

## Demo Admin Account

For testing only:

- Username: `admin`
- Password: `admin123`

This account is created by the SQL file for local demo use. Change it before using the project anywhere real.

## Project Structure

```
enrollment_system/
├── admin/        Admin pages and AJAX handlers
├── student/      Student pages and AJAX handlers
├── assets/       CSS and JavaScript
├── config/       Database connection
├── database/     SQL schema and sample data
├── includes/     Shared session handling
└── index.php     Landing page (portal selection)
```

## Screenshots

*Add screenshots of the landing page, student dashboard, and admin dashboard here.*

## Authors

Samuel C. Sisno and classmates, BS Information Technology, Bulacan State University.
