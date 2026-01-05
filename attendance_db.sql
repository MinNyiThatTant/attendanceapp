-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jan 05, 2026 at 03:21 PM
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
(61, NULL, 1),
(62, NULL, 2),
(63, NULL, 3),
(64, NULL, 4),
(65, NULL, 5),
(66, NULL, 6),
(294, 89, 2),
(295, 37, 1),
(296, 37, 2),
(297, 37, 3),
(298, 37, 4),
(299, 37, 5),
(300, 37, 6),
(301, 95, 3),
(303, 34, 1),
(304, 34, 2),
(305, 34, 3),
(306, 34, 4),
(307, 34, 5),
(308, 34, 6),
(309, 38, 1),
(310, 35, 1),
(311, 35, 2),
(312, 35, 3),
(313, 35, 4),
(314, 35, 5),
(315, 35, 6),
(316, 36, 1),
(317, 36, 2),
(318, 36, 3),
(319, 36, 4),
(320, 36, 5),
(321, 36, 6),
(322, 101, 4),
(323, 33, 1),
(324, 33, 2),
(325, 33, 3),
(326, 33, 4),
(327, 33, 5),
(328, 33, 6),
(329, 113, 6),
(331, 50, 1),
(332, 46, 1),
(333, 46, 2),
(334, 46, 3),
(335, 46, 4),
(336, 46, 5),
(337, 46, 6),
(338, 114, 6),
(339, 107, 5),
(340, 90, 2),
(341, 44, 1),
(342, 96, 3),
(343, 41, 1),
(344, 41, 2),
(345, 41, 3),
(346, 41, 4),
(347, 41, 5),
(348, 41, 6),
(349, 40, 1),
(350, 40, 2),
(351, 40, 3),
(352, 40, 4),
(353, 40, 5),
(354, 40, 6),
(355, 102, 4),
(356, 42, 1),
(357, 42, 2),
(358, 42, 3),
(359, 42, 4),
(360, 42, 5),
(361, 42, 6),
(362, 39, 1),
(363, 39, 2),
(364, 39, 3),
(365, 39, 4),
(366, 39, 5),
(367, 39, 6),
(368, 108, 5),
(369, 43, 1),
(370, 43, 2),
(371, 43, 3),
(372, 43, 4),
(373, 43, 5),
(374, 43, 6),
(375, 91, 2),
(376, 48, 1),
(377, 45, 1),
(378, 45, 2),
(379, 45, 3),
(380, 45, 4),
(381, 45, 5),
(382, 45, 6),
(383, 49, 1),
(384, 97, 3),
(385, 103, 4),
(386, 115, 6),
(387, 47, 1),
(388, 47, 2),
(389, 47, 3),
(390, 47, 4),
(391, 47, 5),
(392, 47, 6),
(393, 109, 5),
(394, 92, 2),
(395, 57, 1),
(396, 52, 1),
(397, 53, 1),
(398, 55, 1),
(399, 56, 1),
(400, 98, 3),
(401, 51, 1),
(402, 51, 2),
(403, 51, 3),
(404, 51, 4),
(405, 51, 5),
(406, 51, 6),
(407, 104, 4),
(408, 116, 6),
(409, 110, 5),
(410, 93, 2),
(411, 63, 1),
(412, 62, 1),
(413, 59, 1),
(414, 60, 1),
(415, 61, 1),
(416, 99, 3),
(417, 58, 1),
(418, 58, 2),
(419, 58, 3),
(420, 58, 4),
(421, 58, 5),
(422, 58, 6),
(423, 105, 4),
(424, 117, 6),
(425, 111, 5),
(426, 94, 2),
(427, 67, 1),
(428, 68, 1),
(429, 69, 1),
(430, 66, 1),
(431, 65, 1),
(432, 100, 3),
(433, 64, 1),
(434, 64, 2),
(435, 64, 3),
(436, 64, 4),
(437, 64, 5),
(438, 64, 6),
(439, 106, 4),
(440, 118, 6),
(441, 112, 5),
(442, 75, 1),
(443, 76, 1),
(444, 73, 1),
(445, 74, 1),
(446, 72, 1),
(447, 71, 1),
(448, 70, 1),
(449, 70, 2),
(450, 70, 3),
(451, 70, 4),
(452, 70, 5),
(453, 70, 6),
(454, 82, 1),
(455, 83, 1),
(456, 81, 1),
(457, 80, 1),
(458, 78, 1),
(459, 79, 1),
(460, 77, 1),
(461, 77, 2),
(462, 77, 3),
(463, 77, 4),
(464, 77, 5),
(465, 77, 6),
(466, 86, 1),
(467, 84, 1),
(468, 87, 1),
(469, 85, 1),
(470, 88, 1);

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
(64, 'EM32006', 'Engineering Mathemaics VI', 3, 6, NULL, '2025-2026', 45),
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
(43, 51, 101, 1, '2024-2025'),
(45, 26, 45, 3, '2025-2026'),
(46, 20, 33, 1, '2025-2026'),
(47, 18, 45, 3, '2025-2026'),
(48, 51, 45, 3, '2025-2026'),
(49, 39, 34, 1, '2025-2026'),
(50, 41, 45, 3, '2025-2026'),
(51, 26, 46, 3, '2025-2026');

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
(6, '2026-02-16', 'တရုတ်နှစ်သစ်ကူးနေ့ရုံးပိိတ်ရက်', '2025-2026'),
(7, '2026-02-17', 'တရုတ်နှစ်သစ်ကူးနေ့', '2025-2026');

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
(1, 'tu(25)/00001', 'TEST002', 'EP6', 4, '6th semester', '2025-2026', 'default.png'),
(2, 'tu(25)/00002', 'TEST0023', 'EP5', 4, '5th semester', '2025-2026', 'default.png'),
(3, 'tu(25)/00003', 'TEST0022', 'EP4', 4, '4th semester', '2025-2026', 'default.png'),
(4, 'tu(25)/00004', 'TEST00121', 'EP5', 4, '5th semester', '2025-2026', 'default.png'),
(5, 'tu(25)/00005', 'TEST0021', 'EP3', 4, '3rd semester', '2025-2026', 'default.png'),
(6, 'tu(25)/00006', 'TEST0020', 'EP2', 4, '2nd semester', '2025-2026', 'default.png'),
(7, 'tu(25)/00007', 'TEST0019', 'EP1', 4, '1st semester', '2025-2026', 'default.png'),
(8, 'tu(25)/00008', 'TEST0018', 'EC6', 3, '6th semester', '2025-2026', 'default.png'),
(9, 'tu(25)/00009', 'TEST0017', 'EC5', 3, '5th semester', '2025-2026', 'default.png'),
(10, 'tu(25)/00010', 'TEST0016', 'EC4', 3, '4th semester', '2025-2026', 'default.png'),
(12, 'tu(25)/00012', 'TEST0014', 'EC3', 3, '3rd semester', '2024-2025', 'default.png'),
(13, 'tu(25)/00013', 'TEST0013', 'EC2', 3, '2nd semester', '2025-2026', 'default.png'),
(14, 'tu(25)/00014', 'TEST0012', 'EC1', 3, '1st semester', '2025-2026', 'default.png'),
(15, 'tu(25)/00015', 'TEST0011', 'C6', 2, '6th semester', '2025-2026', 'default.png'),
(16, 'tu(25)/00016', 'TEST0010', 'C5', 2, '5th semester', '2025-2026', 'default.png'),
(17, 'tu(25)/00017', 'TEST006', 'C4', 2, '4th semester', '2025-2026', 'default.png'),
(18, 'tu(25)/00018', 'TEST009', 'C3', 2, '3rd semester', '2025-2026', 'default.png'),
(19, 'tu(25)/00019', 'TEST008', 'C2', 2, '2nd semester', '2025-2026', 'default.png'),
(20, 'tu(25)/00020', 'TEST007', 'C1', 2, '1st semester', '2025-2026', 'default.png'),
(21, 'tu(25)00100', 'TEST005', 'CEIT6', 1, '6th semester', '2025-2026', 'default.png'),
(22, 'tu(25)00123', 'TEST004', 'CEIT5', 1, '5th semester', '2025-2026', 'default.png'),
(23, 'tu(25)00045', 'TEST001', 'CEIT4', 1, '4th semester', '2025-2026', 'default.png'),
(26, 'tu(25)01000', 'TEST0030', 'CEIT3', 1, '3rd semester', '2025-2026', 'default.png'),
(28, 'tu(25)00090', 'TEST0090', 'CEIT2', 1, '2nd semester', '2025-2026', 'default.png'),
(29, 'tu(25)00091', 'TEST0091', 'CEIT1', 1, '1st semester', '2025-2026', 'ST_1767203815.jpg'),
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
(50, 'tu(25)12321', 'MC666', 'MC6', 6, '1st semester', '2025-2026', 'default.png'),
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
(4, 26, '2026-01-01', '2026-01-01', 'Medical', 'dfsdf', '2025-2026', '2026-01-01 16:05:24');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `major_id` int(11) NOT NULL,
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

INSERT INTO `timetable` (`id`, `major_id`, `course_id`, `day_of_week`, `period`, `start_time`, `end_time`, `academic_year`) VALUES
(64, 1, 33, 'Monday', 1, '09:00:00', '09:59:59', '2025-2026'),
(65, 1, 33, 'Monday', 2, '10:00:00', '10:59:59', '2025-2026');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_details`
--
ALTER TABLE `attendance_details`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `course_assignments`
--
ALTER TABLE `course_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=471;

--
-- AUTO_INCREMENT for table `course_details`
--
ALTER TABLE `course_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `course_registration`
--
ALTER TABLE `course_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `faculty_details`
--
ALTER TABLE `faculty_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- Constraints for dumped tables
--

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
