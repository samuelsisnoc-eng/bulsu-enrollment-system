-- Run this in phpMyAdmin to add colleges and programs tables
-- Select your enrollment_db database first, then import this file

USE enrollment_db;

-- Create colleges table
CREATE TABLE IF NOT EXISTS colleges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    college_code VARCHAR(20) NOT NULL UNIQUE,
    college_name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create programs table
CREATE TABLE IF NOT EXISTS programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_code VARCHAR(20) NOT NULL UNIQUE,
    program_name VARCHAR(150) NOT NULL,
    college_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE SET NULL
);

-- Insert sample colleges
INSERT INTO colleges (college_code, college_name) VALUES
('CICT', 'College of Information and Communications Technology'),
('CAS',  'College of Arts and Sciences'),
('CBA',  'College of Business Administration'),
('CED',  'College of Education'),
('COE',  'College of Engineering')
ON DUPLICATE KEY UPDATE college_code = college_code;

-- Insert sample programs
INSERT INTO programs (program_code, program_name, college_id) VALUES
('BSIT', 'Bachelor of Science in Information Technology', 1),
('BSCS', 'Bachelor of Science in Computer Science', 1),
('BSIS', 'Bachelor of Science in Information Systems', 1),
('BSBA', 'Bachelor of Science in Business Administration', 3),
('BSEd', 'Bachelor of Secondary Education', 4),
('BSCE', 'Bachelor of Science in Civil Engineering', 5)
ON DUPLICATE KEY UPDATE program_code = program_code;
