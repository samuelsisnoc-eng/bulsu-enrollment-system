-- Additional tables for enhanced enrollment system
-- Run this after the initial colleges_programs.sql

USE enrollment_db;

-- Create subjects table
CREATE TABLE IF NOT EXISTS subjects (
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
);

-- Create notifications table for new enrollments
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    student_no VARCHAR(20),
    student_name VARCHAR(150),
    course VARCHAR(20),
    program_name VARCHAR(150),
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Create academic calendar table
CREATE TABLE IF NOT EXISTS academic_calendar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT,
    course_code VARCHAR(20),
    academic_year VARCHAR(20),
    semester VARCHAR(20),
    event_date DATE,
    event_title VARCHAR(200),
    event_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
);

-- Create schedule table
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT,
    course_code VARCHAR(20),
    section VARCHAR(10),
    subject_id INT,
    academic_year VARCHAR(20),
    semester VARCHAR(20),
    day_of_week VARCHAR(10),
    start_time TIME,
    end_time TIME,
    room_no VARCHAR(20),
    professor_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Update students table to include program_id
ALTER TABLE students ADD COLUMN program_id INT AFTER course;
ALTER TABLE students ADD FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL;

-- Insert sample subjects
INSERT INTO subjects (subject_code, subject_name, units, program_id, college_id) VALUES
('CS101', 'Introduction to Computer Science', 3, 1, 1),
('CS102', 'Data Structures and Algorithms', 3, 1, 1),
('CS103', 'Database Management Systems', 3, 1, 1),
('IT101', 'Web Development Fundamentals', 3, 1, 1),
('IT102', 'Network Administration', 3, 2, 1),
('MATH101', 'Calculus I', 3, 1, 1),
('ENG101', 'English Communication', 3, 1, 1),
('BA101', 'Principles of Management', 3, 3, 3),
('BA102', 'Financial Accounting', 3, 3, 3),
('EDU101', 'Educational Psychology', 3, 4, 4),
('CE101', 'Structural Engineering', 3, 5, 5);

-- Insert sample academic calendar events
INSERT INTO academic_calendar (program_id, course_code, academic_year, semester, event_date, event_title, event_description) VALUES
(1, 'BSIT', '2024-2025', '1st', '2024-08-15', 'Classes Start', 'First day of classes for BSIT program'),
(1, 'BSIT', '2024-2025', '1st', '2024-10-15', 'Midterm Exams', 'Midterm examination period'),
(1, 'BSIT', '2024-2025', '1st', '2024-12-15', 'Final Exams', 'Final examination period'),
(2, 'BSCS', '2024-2025', '1st', '2024-08-15', 'Classes Start', 'First day of classes for BSCS program'),
(2, 'BSCS', '2024-2025', '1st', '2024-09-15', 'Programming Workshop', 'Advanced programming workshop'),
(3, 'BSBA', '2024-2025', '1st', '2024-08-15', 'Classes Start', 'First day of classes for BSBA program'),
(3, 'BSBA', '2024-2025', '1st', '2024-11-15', 'Business Case Study', 'Real-world business case analysis');

-- Insert sample schedules
INSERT INTO schedules (program_id, course_code, section, subject_id, academic_year, semester, day_of_week, start_time, end_time, room_no, professor_name) VALUES
(1, 'BSIT', 'A', 1, '2024-2025', '1st', 'Monday', '08:00:00', '10:00:00', 'Room 101', 'Dr. Juan Dela Cruz'),
(1, 'BSIT', 'A', 2, '2024-2025', '1st', 'Tuesday', '10:00:00', '12:00:00', 'Room 102', 'Prof. Maria Santos'),
(1, 'BSIT', 'A', 4, '2024-2025', '1st', 'Wednesday', '13:00:00', '15:00:00', 'Lab 201', 'Engr. Pedro Reyes'),
(2, 'BSCS', 'A', 1, '2024-2025', '1st', 'Monday', '08:00:00', '10:00:00', 'Room 103', 'Dr. Ana Garcia'),
(2, 'BSCS', 'A', 5, '2024-2025', '1st', 'Thursday', '10:00:00', '12:00:00', 'Lab 202', 'Prof. Carlos Mendoza'),
(3, 'BSBA', 'A', 8, '2024-2025', '1st', 'Monday', '08:00:00', '10:00:00', 'Room 201', 'Dr. Rosa Lim'),
(3, 'BSBA', 'A', 9, '2024-2025', '1st', 'Tuesday', '13:00:00', '15:00:00', 'Room 202', 'Prof. Miguel Torres');