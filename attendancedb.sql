-- 1. Major Table
CREATE TABLE IF NOT EXISTS `major_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL
);

-- 2. Course Details Table
CREATE TABLE IF NOT EXISTS `course_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `credits` INT,
  `session_id` INT,
  `academic_year` VARCHAR(20),
  `total_classes` INT DEFAULT 45
);

-- 3. Course Assignments
CREATE TABLE IF NOT EXISTS `course_assignments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT,
  `major_id` INT,
  FOREIGN KEY (course_id) REFERENCES course_details(id) ON DELETE CASCADE,
  FOREIGN KEY (major_id) REFERENCES major_details(id) ON DELETE CASCADE
);

-- 4. Timetable Table
CREATE TABLE IF NOT EXISTS `timetable` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `major_id` INT,
  `course_id` INT,
  `day_of_week` VARCHAR(20),
  `period` INT,
  `start_time` TIME,
  `end_time` TIME,
  `academic_year` VARCHAR(20),
  FOREIGN KEY (major_id) REFERENCES major_details(id),
  FOREIGN KEY (course_id) REFERENCES course_details(id)
);

-- 5. Session Details 
CREATE TABLE IF NOT EXISTS `session_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `term` VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS `holiday_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `holiday_name` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `type` ENUM('Public', 'University', 'Other') DEFAULT 'Public',
  `academic_year` VARCHAR(20) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student Details Table
CREATE TABLE IF NOT EXISTS `student_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `roll_no` VARCHAR(50) UNIQUE NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `major_id` INT,
  `current_semester` INT,
  `academic_year` VARCHAR(20),
  `email` VARCHAR(100),
  FOREIGN KEY (major_id) REFERENCES major_details(id)
);

-- Course Registration Table
CREATE TABLE IF NOT EXISTS `course_registration` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT,
  `course_id` INT,
  `session_id` INT,
  `academic_year` VARCHAR(20),
  `registered_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES student_details(id),
  FOREIGN KEY (course_id) REFERENCES course_details(id)
);

-- Major Table
CREATE TABLE IF NOT EXISTS `major_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL
);

-- Course Details Table 
CREATE TABLE IF NOT EXISTS `course_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) UNIQUE NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `credits` INT,
  `session_id` INT,
  `major_id` INT,
  `academic_year` VARCHAR(20),
  `total_classes` INT DEFAULT 45
);

-- Timetable Table
CREATE TABLE IF NOT EXISTS `timetable` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `major_id` INT,
  `course_id` INT,
  `day_of_week` VARCHAR(20),
  `period` INT,
  `start_time` TIME,
  `end_time` TIME,
  `academic_year` VARCHAR(20)
);

-- Holiday Table
CREATE TABLE IF NOT EXISTS `holidays` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `holiday_name` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `academic_year` VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS `faculty_details` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_name` VARCHAR(50) UNIQUE NOT NULL,
    `name` VARCHAR(100),
    `password` VARCHAR(255) 
);

INSERT IGNORE INTO `faculty_details` (`id`, `user_name`, `name`, `password`) VALUES
(1, 'admin', 'Admin User', '$2y$10$89W1h/B.S.PqQvLz7M6PueV6WpLpD3rE7H/U3L.Nn9Z3R6v5f/9G.'),
(2, 'faculty1', 'Faculty One', '$2y$10$q.yY/eLpS/6x5Z7pQ5F1uO4kI5H7/0X5R8B6U9L1M5N4z3O2P1Q2R');


-- Course id 33 to 45 
INSERT IGNORE INTO `course_assignments` (`course_id`, `major_id`) VALUES
(33, 1), (34, 1), (35, 1), (36, 1), (37, 1), (38, 1), (44, 1), (45, 1);

-- Major
INSERT IGNORE INTO `major_details` (`id`, `title`) VALUES
(1, 'Computer Engineering and Information Technology'),
(2, 'Electronic Engineering'),
(3, 'Mechanical Engineering');

-- Session (Semester) 
INSERT IGNORE INTO `session_details` (`id`, `year`, `term`) VALUES
(1, 2025, '1st Semester'),
(2, 2024, '2nd Semester'),
(3, 2025, '3rd Semester');

-- Course Details 
INSERT IGNORE INTO `course_details` (`id`, `code`, `title`, `credits`, `session_id`, `academic_year`, `total_classes`) VALUES
(33, 'M11001', 'Myanmar', 2, 1, '2025-2026', 45),
(34, 'E11001', 'English I', 3, 1, '2025-2026', 45),
(35, 'ECH11001', 'Engineering Chemistry', 4, 1, '2025-2026', 45),
(36, 'EM11001', 'Engineering Mathematics I', 3, 1, '2025-2026', 45),
(37, 'ME11011', 'Basic Engineering Drawing', 3, 1, '2025-2026', 45),
(38, 'CEIT11011', 'Introduction To Computer System', 2, 1, '2025-2026', 45),
(44, 'CEIT12002', 'C Programming', 3, 2, '2024-2025', 45),
(45, 'E21001', 'English III', 3, 3, '2024-2025', 45);

-- Faculty (Admin)
-- Password - admin123
INSERT IGNORE INTO `faculty_details` (`id`, `user_name`, `name`, `password`) VALUES
(1, 'admin', 'Admin User', '$2y$10$89W1h/B.S.PqQvLz7M6PueV6WpLpD3rE7H/U3L.Nn9Z3R6v5f/9G.');


DROP TABLE IF EXISTS `holidays`;

CREATE TABLE `holidays` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `holiday_name` VARCHAR(255) NOT NULL,
  `holiday_date` DATE NOT NULL, 
  `academic_year` VARCHAR(20) NOT NULL,
  `description` TEXT NULL
);

-- to test holiday insertion
INSERT INTO `holidays` (`holiday_name`, `holiday_date`, `academic_year`, `description`) 
VALUES ('Test Holiday', CURDATE(), '2024-2025', 'Public Holiday Test');

-- ၁။ Attendance Details Table 
CREATE TABLE IF NOT EXISTS `attendance_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `course_id` INT,
  `on_date` DATE NOT NULL,
  `status` ENUM('Present', 'Absent', 'Leave') DEFAULT 'Present',
  `uid_scanned` VARCHAR(50)
);

-- ၂။ Student Leaves Table
CREATE TABLE IF NOT EXISTS `student_leaves` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `from_date` DATE NOT NULL,
  `to_date` DATE NOT NULL,
  `reason` TEXT
);

-- ၃။ Timetable Table
CREATE TABLE IF NOT EXISTS `timetable` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `major_id` INT,
  `course_id` INT,
  `day_of_week` VARCHAR(20),
  `period` INT,
  `start_time` TIME,
  `end_time` TIME,
  `academic_year` VARCHAR(20)
);