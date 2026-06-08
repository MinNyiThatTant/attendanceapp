-- 1. Database အသစ်တည်ဆောက်ခြင်း
CREATE DATABASE IF NOT EXISTS attendance_app;
USE attendance_app;

-- 2. Major Details Table
CREATE TABLE `major_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `code` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Faculty Details Table
CREATE TABLE `faculty_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL UNIQUE,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Session Details Table
CREATE TABLE `session_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(11) DEFAULT NULL,
  `term` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `year_term` (`year`,`term`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Course Details Table
CREATE TABLE `course_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `title` varchar(100) NOT NULL,
  `credits` int(11) DEFAULT NULL,
  `major_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Student Details Table
CREATE TABLE `student_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roll_no` varchar(50) NOT NULL UNIQUE,
  `rfid_uid` varchar(50) DEFAULT NULL UNIQUE,
  `name` varchar(100) NOT NULL,
  `major_id` int(11) DEFAULT NULL,
  `current_semester` varchar(50) DEFAULT '1st semester',
  `academic_year` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`major_id`) REFERENCES `major_details`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Attendance Details Table
CREATE TABLE `attendance_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `faculty_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `on_date` date NOT NULL,
  `status` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `student_details`(`id`),
  FOREIGN KEY (`faculty_id`) REFERENCES `faculty_details`(`id`),
  FOREIGN KEY (`course_id`) REFERENCES `course_details`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Timetable Table
CREATE TABLE `timetable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `major_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `day_of_week` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`major_id`) REFERENCES `major_details`(`id`),
  FOREIGN KEY (`course_id`) REFERENCES `course_details`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Computer Usage Logs Table
CREATE TABLE `computer_usage_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `check_in_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `check_out_time` datetime DEFAULT NULL,
  `usage_date` date NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `student_details`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Student Leaves Table
CREATE TABLE `student_leaves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `student_details`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;