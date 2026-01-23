-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jan 23, 2026 at 03:32 PM
-- Server version: 11.5.2-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attendance_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_details`
--

CREATE TABLE `attendance_details` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `period` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `on_date` date NOT NULL,
  `on_time` time DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `timetable_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_details`
--

INSERT INTO `attendance_details` (`id`, `faculty_id`, `student_id`, `course_id`, `period`, `session_id`, `on_date`, `on_time`, `status`, `academic_year`, `created_at`, `timetable_id`) VALUES
(52, 0, 29, 35, 0, 0, '2026-01-20', '20:58:55', 'Present', '2025-2026', '2026-01-20 14:28:55', 120),
(53, 0, 20, 89, 0, 0, '2026-01-23', '13:55:13', 'Present', '2025-2026', '2026-01-23 07:25:13', 122),
(54, 0, 29, 35, 0, 0, '2026-01-23', '14:02:14', 'Present', '2025-2026', '2026-01-23 07:32:14', 117);

-- --------------------------------------------------------

--
-- Table structure for table `computer_usage_logs`
--

CREATE TABLE `computer_usage_logs` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `check_in_time` datetime DEFAULT current_timestamp(),
  `check_out_time` datetime DEFAULT NULL,
  `usage_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computer_usage_logs`
--

INSERT INTO `computer_usage_logs` (`id`, `student_id`, `check_in_time`, `check_out_time`, `usage_date`) VALUES
(22, 29, '0000-00-00 00:00:00', NULL, '2026-01-23');

-- --------------------------------------------------------

--
-- Table structure for table `course_allotment`
--

CREATE TABLE `course_allotment` (
  `faculty_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_allotment`
--

INSERT INTO `course_allotment` (`faculty_id`, `course_id`, `session_id`) VALUES
(1, 4, 2),
(1, 5, 2),
(1, 8, 1),
(1, 10, 1),
(2, 1, 1),
(2, 5, 1),
(2, 7, 2),
(3, 2, 1),
(3, 2, 2),
(3, 3, 1),
(3, 8, 2),
(4, 5, 2),
(4, 7, 2),
(4, 9, 1),
(4, 10, 1),
(5, 2, 1),
(5, 2, 2),
(5, 6, 2),
(5, 8, 1),
(6, 1, 1),
(6, 3, 2),
(6, 4, 1),
(6, 4, 2),
(7, 3, 2),
(7, 4, 1),
(7, 8, 1),
(7, 8, 2),
(8, 1, 1),
(8, 5, 2),
(8, 8, 2),
(8, 9, 1),
(9, 2, 2),
(9, 3, 1),
(9, 5, 1),
(9, 5, 2),
(10, 1, 2),
(10, 2, 1),
(10, 4, 2),
(10, 7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `course_assignments`
--

CREATE TABLE `course_assignments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `major_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_assignments`
--

INSERT INTO `course_assignments` (`id`, `course_id`, `major_id`) VALUES
(2, 117, 6),
(3, 118, 6),
(4, 116, 6),
(5, 115, 6),
(6, 114, 6),
(7, 113, 6),
(8, 112, 5),
(9, 111, 5),
(10, 110, 5),
(11, 109, 5),
(12, 108, 5),
(13, 107, 5),
(14, 106, 4),
(15, 105, 4),
(16, 104, 4),
(17, 103, 4),
(18, 102, 4),
(19, 101, 4),
(20, 100, 3),
(21, 99, 3),
(22, 98, 3),
(23, 97, 3),
(24, 96, 3),
(25, 95, 3),
(26, 94, 2),
(27, 93, 2),
(28, 92, 2),
(29, 91, 2),
(30, 90, 2),
(31, 89, 2),
(32, 88, 1),
(33, 87, 1),
(34, 86, 1),
(35, 85, 1),
(36, 84, 1),
(37, 83, 1),
(38, 82, 1),
(39, 81, 1),
(40, 80, 1),
(41, 79, 1),
(42, 78, 1),
(44, 76, 1),
(45, 75, 1),
(46, 74, 1),
(47, 73, 1),
(48, 72, 1),
(49, 71, 1),
(51, 69, 1),
(52, 68, 1),
(53, 67, 1),
(54, 66, 1),
(55, 65, 1),
(57, 63, 1),
(58, 62, 1),
(59, 61, 1),
(60, 60, 1),
(61, 59, 1),
(63, 57, 1),
(64, 56, 1),
(65, 55, 1),
(66, 53, 1),
(67, 52, 1),
(69, 50, 1),
(70, 49, 1),
(71, 48, 1),
(74, 45, 1),
(75, 45, 2),
(76, 45, 3),
(77, 45, 4),
(78, 45, 5),
(79, 45, 6),
(80, 77, 1),
(81, 77, 2),
(82, 77, 3),
(83, 77, 4),
(84, 77, 5),
(85, 77, 6),
(86, 70, 1),
(87, 70, 2),
(88, 70, 3),
(89, 70, 4),
(90, 70, 5),
(91, 70, 6),
(92, 64, 1),
(93, 64, 2),
(94, 64, 3),
(95, 64, 4),
(96, 64, 5),
(97, 64, 6),
(98, 58, 1),
(99, 58, 2),
(100, 58, 3),
(101, 58, 4),
(102, 58, 5),
(103, 58, 6),
(104, 51, 1),
(105, 51, 2),
(106, 51, 3),
(107, 51, 4),
(108, 51, 5),
(109, 51, 6),
(110, 46, 1),
(111, 46, 2),
(112, 46, 3),
(113, 46, 4),
(114, 46, 5),
(115, 46, 6),
(116, 47, 1),
(117, 47, 2),
(118, 47, 3),
(119, 47, 4),
(120, 47, 5),
(121, 47, 6),
(122, 44, 1),
(123, 43, 1),
(124, 43, 2),
(125, 43, 3),
(126, 43, 4),
(127, 43, 5),
(128, 43, 6),
(129, 42, 1),
(130, 42, 2),
(131, 42, 3),
(132, 42, 4),
(133, 42, 5),
(134, 42, 6),
(135, 41, 1),
(136, 41, 2),
(137, 41, 3),
(138, 41, 4),
(139, 41, 5),
(140, 41, 6),
(141, 40, 1),
(142, 40, 2),
(143, 40, 3),
(144, 40, 4),
(145, 40, 5),
(146, 40, 6),
(147, 39, 1),
(148, 39, 2),
(149, 39, 3),
(150, 39, 4),
(151, 39, 5),
(152, 39, 6),
(153, 38, 1),
(154, 37, 1),
(155, 37, 2),
(156, 37, 3),
(157, 37, 4),
(158, 37, 5),
(159, 37, 6),
(160, 36, 1),
(161, 36, 2),
(162, 36, 3),
(163, 36, 4),
(164, 36, 5),
(165, 36, 6),
(166, 35, 1),
(167, 35, 2),
(168, 35, 3),
(169, 35, 4),
(170, 35, 5),
(171, 35, 6),
(172, 34, 1),
(173, 34, 2),
(174, 34, 3),
(175, 34, 4),
(176, 34, 5),
(177, 34, 6),
(178, 33, 1),
(179, 33, 2),
(180, 33, 3),
(181, 33, 4),
(182, 33, 5),
(183, 33, 6);

-- --------------------------------------------------------

--
-- Table structure for table `course_details`
--

CREATE TABLE `course_details` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `credits` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `major_id` int(11) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `total_classes` int(11) DEFAULT 45
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_details`
--

INSERT INTO `course_details` (`id`, `code`, `title`, `credits`, `session_id`, `major_id`, `academic_year`, `total_classes`) VALUES
(33, 'M11001', 'Myanmar', 2, 1, NULL, '2025-2026', 45),
(34, 'E11001', 'English I', 3, 1, NULL, '2025-2026', 45),
(35, 'ECh11001', 'Engineering Chemistry', 4, 1, NULL, '2025-2026', 45),
(36, 'EM11001', 'Engineering Mathematics I', 3, 1, NULL, '2025-2026', 45),
(37, 'ME11011', 'Basic Engineering Drawing ', 3, 1, NULL, '2025-2026', 45),
(38, 'CEIT11011', 'Introduction To Computer System', 2, 1, NULL, '2025-2026', 45),
(39, 'M12001', 'Myanmar', 2, 2, NULL, '2025-2026', 45),
(40, 'E12001', 'English II', 3, 2, NULL, '2025-2026', 45),
(41, 'EM12002', 'Engineering Mathematics II', 3, 2, NULL, '2025-2026', 45),
(42, 'EPh12001', 'Engineering Physics', 4, 2, NULL, '2025-2026', 45),
(43, 'WS12012', 'Workshop Practice', 3, 2, NULL, '2025-2026', 45),
(44, 'CEIT12002', 'C Programming', 3, 2, NULL, '2025-2026', 45),
(45, 'E21001', 'English III', 3, 3, NULL, '2025-2026', 45),
(46, 'EM21003', 'Engineering Mathematics III', 3, 3, NULL, '2025-2026', 45),
(47, 'ME21015', 'Engineering Mechanics (Statics)', 3, 3, NULL, '2025-2026', 45),
(48, 'CEIT21011', 'Digital Electronics', 3, 3, NULL, '2025-2026', 45),
(49, 'CEIT21021', 'Engineering Circuit Analysics', 3, 3, NULL, '2025-2026', 45),
(50, 'CEIT21012', 'Object Oriented Programming (Using C++)', 3, 3, NULL, '2025-2026', 45),
(51, 'EM22004', 'Engineering Mathematics IV', 3, 4, NULL, '2025-2026', 45),
(52, 'CEIT22022', 'Java Programming', 3, 4, NULL, '2025-2026', 45),
(53, 'CEIT22042', 'Database Management System', 3, 4, NULL, '2025-2026', 45),
(55, 'CEIT22052', 'Data Structure and Algorithms', 3, 4, NULL, '2025-2026', 45),
(56, 'CEIT22062', 'Opertating System', 3, 4, NULL, '2025-2026', 45),
(57, 'CEIT22003', 'Digital Communications', 3, 4, NULL, '2025-2026', 45),
(58, 'EM31005', 'Engineering Mathematics V', 3, 5, NULL, '2025-2026', 45),
(59, 'CEIT31031', 'Computer Orginization and Design', 3, 5, NULL, '2025-2026', 45),
(60, 'CEIT31032', 'Advanced Java Programming', 3, 5, NULL, '2025-2026', 45),
(61, 'CEIT31072', 'Software Engineering', 3, 5, NULL, '2025-2026', 45),
(62, 'CEIT31013', 'Networking Fundamentals', 3, 5, NULL, '2025-2026', 45),
(63, 'CEIT31007', 'Professional Practice in IT', 3, 5, NULL, '2025-2026', 45),
(64, 'EM32006', 'Engineering Mathematics VI', 3, 6, NULL, '2025-2026', 45),
(65, 'CEIT32041', 'Computer Architecture', 3, 6, NULL, '2025-2026', 45),
(66, 'CEIT32023', 'Advanced Networking', 3, 6, NULL, '2025-2026', 45),
(67, 'CEIT32004', 'Information Security', 3, 6, NULL, '2025-2026', 45),
(68, 'CEIT32005', 'Artificial Intelligence', 3, 6, NULL, '2025-2026', 45),
(69, 'CEIT32006', 'Signal and Systems', 3, 6, NULL, '2025-2026', 45),
(70, 'HSS41001', 'Humanities and Social Science I', 3, 7, NULL, '2025-2026', 45),
(71, 'CEIT41051', 'Embedded System', 3, 7, NULL, '2025-2026', 45),
(72, 'CEIT41033', 'Wireless and Mobile Communications', 3, 7, NULL, '2025-2026', 45),
(73, 'CEIT41016', 'Digital Image Processing', 3, 7, NULL, '2025-2026', 45),
(74, 'CEIT41017', 'Business IT', 3, 7, NULL, '2025-2026', 45),
(75, 'CEIT41014', 'Cryptography and Network Security', 3, 7, NULL, '2025-2026', 45),
(76, 'CEIT41015', 'Data Mining', 3, 7, NULL, '2025-2026', 45),
(77, 'HSS42001', 'Humanities and Social Science II', 3, 8, NULL, '2025-2026', 45),
(78, 'CEIT42043', 'Cloud Computing', 3, 8, NULL, '2025-2026', 45),
(79, 'CEIT42053', 'Information Security', 3, 8, NULL, '2025-2026', 45),
(80, 'CEIT42034', 'Blockchain Technology and Cryptocurrency', 3, 8, NULL, '2025-2026', 45),
(81, 'CEIT42027', 'Project Management', 3, 8, NULL, '2025-2026', 45),
(82, 'CEIT42024', 'Computer Forensics', 3, 8, NULL, '2025-2026', 45),
(83, 'CEIT42025', 'Big Data Analysics', 3, 8, NULL, '2025-2026', 45),
(84, 'CEIT51047', 'Internship', 4, 9, NULL, '2025-2026', 45),
(85, 'HSS51003', 'IT Law and Regulations', 3, 9, NULL, '2025-2026', 45),
(86, 'CEIT51037', 'Research Methodology & Statistical Analysis', 3, 9, NULL, '2025-2026', 45),
(87, 'CEIT51057', 'Integrated Design Project', 6, 9, NULL, '2025-2026', 45),
(88, 'CEIT52067', 'Graduation Thesis', 12, 10, NULL, '2025-2026', 45),
(89, 'C11002', 'Steel Structure', 3, 1, NULL, '2025-2026', 45),
(90, 'C22003', 'Build', 3, 2, NULL, '2025-2026', 45),
(91, 'C33222', 'Concerte', 3, 3, NULL, '2025-2026', 45),
(92, 'C44110', 'Building Structure', 3, 4, NULL, '2025-2026', 45),
(93, 'C55111', 'Road', 3, 5, NULL, '2025-2026', 45),
(94, 'C66666', 'Construction', 3, 6, NULL, '2025-2026', 45),
(95, 'EC11111', 'Electronics Circuit', 3, 1, NULL, '2025-2026', 45),
(96, 'EC22222', 'Circuit Analysis', 3, 2, NULL, '2025-2026', 45),
(97, 'EC33333', 'Digital Electronics', 3, 3, NULL, '2025-2026', 45),
(98, 'EC44444', 'Network Fundamentals', 3, 4, NULL, '2025-2026', 45),
(99, 'EC55555', 'Digiral Image', 3, 5, NULL, '2025-2026', 45),
(100, 'EC66666', 'Data Communication', 3, 6, NULL, '2025-2026', 45),
(101, 'EP11111', 'Basics Power', 3, 1, NULL, '2025-2026', 45),
(102, 'EP22222', 'Electrical Wind Turbine', 3, 2, NULL, '2025-2026', 45),
(103, 'EP33333', 'Power Electronics', 3, 3, NULL, '2025-2026', 45),
(104, 'EP44444', 'Solar Power System', 3, 4, NULL, '2025-2026', 45),
(105, 'EP55555', 'Advanced Solar System', 3, 5, NULL, '2025-2026', 45),
(106, 'EP66666', 'Wind Solar System', 3, 6, NULL, '2025-2026', 45),
(107, 'Mech11111', 'Basica Mechanics Drawing', 3, 1, NULL, '2025-2026', 45),
(108, 'Mech22222', 'Mechanics Analyais', 3, 2, NULL, '2025-2026', 45),
(109, 'Mech33333', 'Mechanical Power System', 3, 3, NULL, '2025-2026', 45),
(110, 'Mech44444', 'Fluid Mechanics', 3, 4, NULL, '2025-2026', 45),
(111, 'Mech55555', 'Thermodynamics', 3, 5, NULL, '2025-2026', 45),
(112, 'Mech66666', 'Advance Thermo', 3, 6, NULL, '2025-2026', 45),
(113, 'MC11111', 'Basics Robot System', 3, 1, NULL, '2025-2026', 45),
(114, 'MC22222', 'Robot Analysis', 3, 2, NULL, '2025-2026', 45),
(115, 'MC33333', 'MC Robot', 3, 3, NULL, '2025-2026', 45),
(116, 'MC44444', 'Robot System Network', 3, 4, NULL, '2025-2026', 45),
(117, 'MC55555', 'Robot to AI', 3, 5, NULL, '2025-2026', 45),
(118, 'MC66666', 'Analysis Robot', 3, 6, NULL, '2025-2026', 45);

-- --------------------------------------------------------

--
-- Table structure for table `course_registration`
--

CREATE TABLE `course_registration` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `academic_year` varchar(20) DEFAULT '2024-2025'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_registration`
--

INSERT INTO `course_registration` (`id`, `student_id`, `course_id`, `session_id`, `academic_year`) VALUES
(1, 44, 112, 6, '2025-2026'),
(2, 44, 64, 6, '2025-2026'),
(3, 50, 118, 6, '2025-2026'),
(4, 50, 64, 6, '2025-2026'),
(5, 21, 69, 6, '2025-2026'),
(6, 21, 68, 6, '2025-2026'),
(7, 21, 67, 6, '2025-2026'),
(8, 21, 66, 6, '2025-2026'),
(9, 21, 65, 6, '2025-2026'),
(10, 21, 64, 6, '2025-2026'),
(11, 15, 94, 6, '2025-2026'),
(12, 15, 64, 6, '2025-2026'),
(13, 8, 100, 6, '2025-2026'),
(14, 8, 64, 6, '2025-2026'),
(15, 1, 106, 6, '2025-2026'),
(16, 1, 64, 6, '2025-2026'),
(17, 29, 38, 1, '2025-2026'),
(18, 29, 37, 1, '2025-2026'),
(19, 29, 36, 1, '2025-2026'),
(20, 29, 35, 1, '2025-2026'),
(21, 29, 34, 1, '2025-2026'),
(22, 29, 33, 1, '2025-2026'),
(23, 14, 95, 1, '2025-2026'),
(24, 14, 37, 1, '2025-2026'),
(25, 14, 36, 1, '2025-2026'),
(26, 14, 35, 1, '2025-2026'),
(27, 14, 34, 1, '2025-2026'),
(28, 14, 33, 1, '2025-2026'),
(29, 7, 101, 1, '2025-2026'),
(30, 7, 37, 1, '2025-2026'),
(31, 7, 36, 1, '2025-2026'),
(32, 7, 35, 1, '2025-2026'),
(33, 7, 34, 1, '2025-2026'),
(34, 7, 33, 1, '2025-2026'),
(35, 39, 107, 1, '2025-2026'),
(36, 39, 37, 1, '2025-2026'),
(37, 39, 36, 1, '2025-2026'),
(38, 39, 35, 1, '2025-2026'),
(39, 39, 34, 1, '2025-2026'),
(40, 39, 33, 1, '2025-2026'),
(41, 45, 113, 1, '2025-2026'),
(42, 45, 37, 1, '2025-2026'),
(43, 45, 36, 1, '2025-2026'),
(44, 45, 35, 1, '2025-2026'),
(45, 45, 34, 1, '2025-2026'),
(46, 45, 33, 1, '2025-2026'),
(47, 20, 89, 1, '2025-2026'),
(48, 20, 37, 1, '2025-2026'),
(49, 20, 36, 1, '2025-2026'),
(50, 20, 35, 1, '2025-2026'),
(51, 20, 34, 1, '2025-2026'),
(52, 20, 33, 1, '2025-2026');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_details`
--

CREATE TABLE `faculty_details` (
  `id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_details`
--

INSERT INTO `faculty_details` (`id`, `user_name`, `name`, `password`) VALUES
(1, 'admin', 'Admin User', '$2y$10$KRmzRgdfO1C5jq4/k15pKOoSd8MpoocQNiveL9X92grvEWGdZOhF.'),
(2, 'faculty1', 'Faculty One', '$2y$10$Kav9X7fmkCq1qLqOIsYWZ.QTuQEpcIHolFsvV/3uEu1reAgnU2Sim'),
(3, 'faculty2', 'Faculty Two', '$2y$10$hHV4bYOGLZiPFtXzUX2R3emRz3c3D7pqeGiGPno5XYAYDgndEvQTS'),
(4, 'faculty3', 'Faculty Three', '$2y$10$dcPnkxDdImPHILNz2BGBAevnXFc/..c13p3365qWYwpiY3EqZcDdm'),
(5, 'faculty4', 'Faculty Four', '$2y$10$4bQyGQNmgRd2uHT3QKRVNeVl8pFmyViACiWivMP9ctt6AHC9Md0vO'),
(6, 'faculty5', 'Faculty Five', '$2y$10$ZR91LNMG3FUxQDBJ2d3Lke01KTtcImquMwiArlMo4DddEXScEgAHm'),
(7, 'faculty6', 'Faculty Six', '$2y$10$52Xf/dODlAo.I1gZ3GsyteVSQ/XibcdBXEBCuqA.rbcJNu86X2Som'),
(8, 'faculty7', 'Faculty Seven', '$2y$10$9C0IQifmUt8bXxG5rMRd6eezFr1qf2CTyiSSm8aIlnDm4D0Guxgbu'),
(9, 'faculty8', 'Faculty Eight', '$2y$10$QOfSWSAuFq.KnfJtNbX4dOkqXOj2yQeqNjtp3Ll3s91EAw6caooae'),
(10, 'faculty9', 'Faculty Nine', '$2y$10$e7cYh/2v92xNsLGLmYeXfOp3JJvrHpW.wRrIBWsCxbzhfjyCzrjhG');

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL,
  `holiday_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `holiday_date`, `description`, `academic_year`) VALUES
(3, '2026-02-12', 'ပြည်ထောင်စုနေ့', '2025-2026'),
(5, '2026-02-13', 'ပြည်ထောင်စုနေ့', '2025-2026'),
(7, '2026-02-17', 'တရုတ်နှစ်သစ်ကူးနေ့', '2025-2026'),
(10, '2026-02-16', 'တရုတ်နှစ်သစ်ကူးနေ့ရုံးပိတ်ရက်', '2025-2026');

-- --------------------------------------------------------

--
-- Table structure for table `major_courses`
--

CREATE TABLE `major_courses` (
  `id` int(11) NOT NULL,
  `major_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `major_details`
--

CREATE TABLE `major_details` (
  `id` int(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `code` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `major_details`
--

INSERT INTO `major_details` (`id`, `title`, `code`) VALUES
(1, 'Computer Engineering and Information Technology', 'CEIT'),
(2, 'Civil', 'Civil'),
(3, 'Electrionics', 'EC'),
(4, 'Electrical Power', 'EP'),
(5, 'Mechanical', 'Mech'),
(6, 'Mechatronics', 'MC');

-- --------------------------------------------------------

--
-- Table structure for table `session_details`
--

CREATE TABLE `session_details` (
  `id` int(11) NOT NULL,
  `year` int(11) DEFAULT NULL,
  `term` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session_details`
--

INSERT INTO `session_details` (`id`, `year`, `term`) VALUES
(1, 2024, '1st semester'),
(2, 2025, '2nd semester'),
(3, 2026, '3rd semester'),
(4, 2027, '4th semester'),
(5, 2028, '5th semester'),
(6, 2029, '6th semester'),
(7, 2030, '7th semester'),
(8, 2031, '8th semester'),
(9, 2032, '9th semester'),
(10, 2033, '10th semester');

-- --------------------------------------------------------

--
-- Table structure for table `student_details`
--

CREATE TABLE `student_details` (
  `id` int(11) NOT NULL,
  `roll_no` varchar(50) NOT NULL,
  `rfid_uid` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `major_id` int(11) DEFAULT NULL,
  `current_semester` varchar(50) DEFAULT '1st semester',
  `academic_year` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `student_details`
--

INSERT INTO `student_details` (`id`, `roll_no`, `rfid_uid`, `name`, `major_id`, `current_semester`, `academic_year`, `photo`) VALUES
(1, 'tu(25)00001', 'TEST002', 'EP6', 4, '6th semester', '2025-2026', 'default.png'),
(2, 'tu(25)00002', 'TEST0023', 'EP5', 4, '5th semester', '2025-2026', 'default.png'),
(3, 'tu(25)00003', 'TEST0022', 'EP4', 4, '4th semester', '2025-2026', 'default.png'),
(4, 'tu(25)00004', 'TEST00121', 'EP3', 4, '3rd semester', '2025-2026', 'default.png'),
(6, 'tu(25)00006', 'TEST0020', 'EP2', 4, '2nd semester', '2025-2026', 'default.png'),
(7, 'tu(25)00007', 'TEST0019', 'EP1', 4, '1st semester', '2025-2026', 'default.png'),
(8, 'tu(25)00008', 'TEST0018', 'EC6', 3, '6th semester', '2025-2026', 'default.png'),
(9, 'tu(25)00009', 'TEST0017', 'EC5', 3, '5th semester', '2025-2026', 'default.png'),
(10, 'tu(25)00010', 'TEST0016', 'EC4', 3, '4th semester', '2025-2026', 'default.png'),
(12, 'tu(25)00012', 'TEST0014', 'EC3', 3, '3rd semester', '2025-2026', 'default.png'),
(13, 'tu(25)00013', 'TEST0013', 'EC2', 3, '2nd semester', '2025-2026', 'default.png'),
(14, 'tu(25)00014', 'TEST0012', 'EC1', 3, '1st semester', '2025-2026', 'default.png'),
(15, 'tu(25)00015', 'TEST0011', 'C6', 2, '6th semester', '2025-2026', 'default.png'),
(16, 'tu(25)00016', 'TEST0010', 'C5', 2, '5th semester', '2025-2026', 'default.png'),
(17, 'tu(25)00017', 'TEST006', 'C4', 2, '4th semester', '2025-2026', 'default.png'),
(18, 'tu(25)00018', 'TEST009', 'C3', 2, '3rd semester', '2025-2026', 'default.png'),
(19, 'tu(25)00019', 'TEST008', 'C2', 2, '2nd semester', '2025-2026', 'default.png'),
(20, 'tu(25)00020', 'TEST007', 'C1', 2, '1st semester', '2025-2026', 'default.png'),
(21, 'tu(25)00100', 'TEST005', 'CEIT6', 1, '6th semester', '2025-2026', 'default.png'),
(22, 'tu(25)00123', 'TEST004', 'CEIT5', 1, '5th semester', '2025-2026', 'default.png'),
(23, 'tu(25)00045', 'TEST001', 'CEIT4', 1, '4th semester', '2025-2026', 'default.png'),
(26, 'tu(25)01000', 'TEST0030', 'CEIT3', 1, '3rd semester', '2025-2026', 'default.png'),
(28, 'tu(25)00090', 'TEST0090', 'CEIT2', 1, '2nd semester', '2025-2026', 'default.png'),
(29, 'tu(25)00091', 'TEST0091', 'CEIT1', 1, '1st semester', '2025-2026', 'ST_1769068502.jpg'),
(39, 'tu(25)15249', 'MECH111', 'Mech1', 5, '1st semester', '2025-2026', 'default.png'),
(40, 'tu(25)45878', 'MECH222', 'Mech2', 5, '2nd semester', '2025-2026', 'default.png'),
(41, 'tu(25)32158', 'MECH333', 'Mech3', 5, '3rd semester', '2025-2026', 'default.png'),
(42, 'tu(25)54566', 'MECH444', 'Mech4', 5, '4th semester', '2025-2026', 'default.png'),
(43, 'tu(25)98452', 'MECH555', 'Mech5', 5, '5th semester', '2025-2026', 'default.png'),
(44, 'tu(25)78542', 'MECH666', 'Mech6', 5, '6th semester', '2025-2026', 'default.png'),
(45, 'tu(25)41478', 'MC111', 'MC1', 6, '1st semester', '2025-2026', 'default.png'),
(46, 'tu(25)25852', 'MC222', 'MC2', 6, '1st semester', '2025-2026', 'default.png'),
(47, 'tu(25)36963', 'MC333', 'MC3', 6, '3rd semester', '2025-2026', 'default.png'),
(48, 'tu(25)78987', 'MC444', 'MC4', 6, '1st semester', '2025-2026', 'default.png'),
(49, 'tu(25)45654', 'MC555', 'MC5', 6, '1st semester', '2025-2026', 'default.png'),
(50, 'tu(25)12321', 'MC666', 'MC6', 6, '6th semester', '2025-2026', 'default.png'),
(51, 'tu(25)15935', 'error111', 'error-test', 2, '3rd semester', '2025-2026', 'default.png');

-- --------------------------------------------------------

--
-- Table structure for table `student_leaves`
--

CREATE TABLE `student_leaves` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `leave_type` enum('Medical','Family','Other') DEFAULT 'Medical',
  `reason` text DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_leaves`
--

INSERT INTO `student_leaves` (`id`, `student_id`, `from_date`, `to_date`, `leave_type`, `reason`, `academic_year`, `created_at`) VALUES
(1, 20, '2026-01-01', '2026-01-01', 'Medical', 'holid', '2025-2026', '2025-12-31 18:16:56'),
(2, 18, '2026-01-01', '2026-01-01', 'Medical', 'fd', '2025-2026', '2026-01-01 04:12:01'),
(3, 41, '2026-01-01', '2026-01-02', 'Medical', 'dsfd', '2025-2026', '2026-01-01 05:36:17'),
(4, 26, '2026-01-01', '2026-01-01', 'Medical', 'dfsdf', '2025-2026', '2026-01-01 16:05:24'),
(5, 28, '2026-01-20', '2026-01-21', 'Medical', 'dfs', '2025-2026', '2026-01-20 14:29:46'),
(6, 19, '2026-01-23', '2026-01-23', 'Medical', 'kmm', '2025-2026', '2026-01-23 07:26:17');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `major_id` int(11) NOT NULL,
  `term` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `period` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `academic_year` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`id`, `major_id`, `term`, `course_id`, `day_of_week`, `period`, `start_time`, `end_time`, `academic_year`) VALUES
(94, 1, 1, 33, 'Monday', 1, '09:00:00', '09:59:59', '2025-2026'),
(95, 1, 1, 33, 'Monday', 2, '10:00:00', '10:59:59', '2025-2026'),
(96, 1, 1, 35, 'Monday', 3, '11:00:00', '11:59:59', '2025-2026'),
(97, 1, 1, 34, 'Monday', 5, '14:00:00', '14:59:59', '2025-2026'),
(98, 1, 1, 34, 'Monday', 6, '15:00:00', '16:00:59', '2025-2026'),
(100, 1, 1, 35, 'Tuesday', 2, '10:00:00', '10:59:59', '2025-2026'),
(101, 1, 1, 33, 'Tuesday', 3, '11:00:00', '11:59:59', '2025-2026'),
(102, 1, 1, 38, 'Tuesday', 4, '13:00:00', '13:59:59', '2025-2026'),
(103, 1, 1, 38, 'Tuesday', 5, '14:00:00', '14:59:59', '2025-2026'),
(104, 1, 1, 38, 'Tuesday', 6, '15:00:00', '16:00:59', '2025-2026'),
(106, 1, 1, 34, 'Wednesday', 2, '10:00:00', '10:59:59', '2025-2026'),
(107, 1, 1, 36, 'Thursday', 1, '09:00:00', '09:59:59', '2025-2026'),
(108, 1, 1, 36, 'Thursday', 2, '10:00:00', '10:59:59', '2025-2026'),
(109, 1, 1, 35, 'Thursday', 3, '11:00:00', '11:59:59', '2025-2026'),
(110, 1, 1, 37, 'Thursday', 4, '13:00:00', '13:59:59', '2025-2026'),
(111, 1, 1, 37, 'Thursday', 5, '14:00:00', '14:59:59', '2025-2026'),
(112, 1, 1, 37, 'Thursday', 6, '15:00:00', '16:00:59', '2025-2026'),
(113, 1, 1, 36, 'Friday', 1, '09:00:00', '09:59:59', '2025-2026'),
(114, 1, 1, 36, 'Friday', 2, '10:00:00', '10:59:59', '2025-2026'),
(115, 1, 1, 37, 'Friday', 3, '11:00:00', '11:59:59', '2025-2026'),
(116, 1, 1, 38, 'Friday', 4, '13:00:00', '13:59:59', '2025-2026'),
(117, 1, 1, 35, 'Friday', 5, '14:00:00', '14:59:59', '2025-2026'),
(118, 1, 1, 35, 'Friday', 6, '15:00:00', '16:00:59', '2025-2026'),
(120, 1, 1, 35, 'Tuesday', 1, '09:00:00', '23:59:59', '2025-2026'),
(121, 1, 1, 34, 'Wednesday', 1, '00:00:00', '23:59:59', '2025-2026'),
(122, 2, 1, 89, 'Friday', 4, '13:00:00', '14:00:00', '2025-2026'),
(123, 2, 1, 89, 'Friday', 5, '14:00:00', '15:00:00', '2025-2026');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_details`
--
ALTER TABLE `attendance_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `computer_usage_logs`
--
ALTER TABLE `computer_usage_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `course_allotment`
--
ALTER TABLE `course_allotment`
  ADD PRIMARY KEY (`faculty_id`,`course_id`,`session_id`);

--
-- Indexes for table `course_assignments`
--
ALTER TABLE `course_assignments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_details`
--
ALTER TABLE `course_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `course_registration`
--
ALTER TABLE `course_registration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`student_id`,`course_id`,`academic_year`);

--
-- Indexes for table `faculty_details`
--
ALTER TABLE `faculty_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_name` (`user_name`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `major_courses`
--
ALTER TABLE `major_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `major_id` (`major_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `major_details`
--
ALTER TABLE `major_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `session_details`
--
ALTER TABLE `session_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year` (`year`,`term`);

--
-- Indexes for table `student_details`
--
ALTER TABLE `student_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roll_no` (`roll_no`),
  ADD UNIQUE KEY `rfid_uid` (`rfid_uid`);

--
-- Indexes for table `student_leaves`
--
ALTER TABLE `student_leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_details`
--
ALTER TABLE `attendance_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `computer_usage_logs`
--
ALTER TABLE `computer_usage_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `course_assignments`
--
ALTER TABLE `course_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- AUTO_INCREMENT for table `course_details`
--
ALTER TABLE `course_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `course_registration`
--
ALTER TABLE `course_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `faculty_details`
--
ALTER TABLE `faculty_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `major_courses`
--
ALTER TABLE `major_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `major_details`
--
ALTER TABLE `major_details`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `session_details`
--
ALTER TABLE `session_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `student_details`
--
ALTER TABLE `student_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `student_leaves`
--
ALTER TABLE `student_leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `computer_usage_logs`
--
ALTER TABLE `computer_usage_logs`
  ADD CONSTRAINT `computer_usage_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_details` (`id`);

--
-- Constraints for table `major_courses`
--
ALTER TABLE `major_courses`
  ADD CONSTRAINT `major_courses_ibfk_1` FOREIGN KEY (`major_id`) REFERENCES `major_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `major_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_leaves`
--
ALTER TABLE `student_leaves`
  ADD CONSTRAINT `student_leaves_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_details` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
