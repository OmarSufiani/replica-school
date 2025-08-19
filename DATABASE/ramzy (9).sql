-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2025 at 03:30 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ramzy`
--

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `school_id` int(11) NOT NULL,
  `school_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`id`, `name`, `school_id`, `school_code`) VALUES
(36, '7B', 1, 'BOWA'),
(37, '7R', 1, 'BOWA'),
(38, '8R', 1, 'BOWA'),
(39, '8B', 1, 'BOWA'),
(40, '9R', 1, 'BOWA'),
(41, '9B', 1, 'BOWA'),
(42, '7B', 3, 'PUNGU'),
(43, '7R', 3, 'PUNGU'),
(44, '8B', 3, 'PUNGU'),
(45, '8R', 3, 'PUNGU'),
(46, '9B', 3, 'PUNGU'),
(47, '9R', 3, 'PUNGU');

-- --------------------------------------------------------

--
-- Table structure for table `school`
--

CREATE TABLE `school` (
  `id` int(11) NOT NULL,
  `school_name` varchar(100) NOT NULL,
  `school_code` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school`
--

INSERT INTO `school` (`id`, `school_name`, `school_code`, `address`, `phone`, `email`, `created_at`) VALUES
(1, 'BOWA JUNIOR SECONDARY', 'BOWA', 'KOMBANI', '098765789', 'BOWA@GMAIL.COM', '2025-08-11'),
(2, 'ZIBANI JUNIOR SECONDARY', 'ZIBANI', 'NGOMBENI', '0987655433', 'kingi@gmail.com', '2025-08-14'),
(3, 'PUNGU JUNIOR SECONDARY', 'PUNGU', 'PUNGU', '0987655433', 'P@GMAIL.COM', '2025-08-18');

-- --------------------------------------------------------

--
-- Table structure for table `score`
--

CREATE TABLE `score` (
  `id` int(30) NOT NULL,
  `std_id` int(30) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `term` varchar(20) DEFAULT NULL,
  `exam_type` varchar(20) DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `Score` double NOT NULL,
  `performance` varchar(100) NOT NULL,
  `tcomments` varchar(50) NOT NULL,
  `school_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `score`
--

INSERT INTO `score` (`id`, `std_id`, `subject_id`, `term`, `exam_type`, `class_id`, `Score`, `performance`, `tcomments`, `school_id`, `teacher_id`, `created_at`) VALUES
(18, 26, 34, 'Term 1', 'Mid Term', 42, 80, 'E.E', 'Excellent', 3, 25, '2025-08-18 12:58:54'),
(19, 26, 34, 'Term 1', 'CAT', 42, 70, 'E.E', 'Excellent', 3, 25, '2025-08-18 13:02:36'),
(20, 26, 40, 'Term 1', 'CAT', 42, 56, 'M.E', 'Good', 3, 25, '2025-08-18 13:10:59'),
(21, 26, 41, 'Term 1', 'CAT', 42, 58, 'M.E', 'Good', 3, 25, '2025-08-18 13:11:52'),
(22, 26, 38, 'Term 1', 'CAT', 42, 90, 'E.E', 'Excellent', 3, 25, '2025-08-18 13:12:11'),
(23, 26, 35, 'Term 1', 'CAT', 42, 78, 'E.E', 'Excellent', 3, 25, '2025-08-18 13:12:29'),
(24, 26, 36, 'Term 1', 'CAT', 42, 45, 'A.E', 'Average', 3, 25, '2025-08-18 13:12:38'),
(25, 26, 37, 'Term 1', 'CAT', 42, 38, 'A.E', 'Average', 3, 25, '2025-08-18 13:12:46'),
(26, 26, 39, 'Term 1', 'CAT', 42, 69, 'M.E', 'Good', 3, 25, '2025-08-18 13:12:57'),
(27, 26, 43, 'Term 1', 'CAT', 42, 50, 'M.E', 'Good', 3, 25, '2025-08-18 13:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive','transferred','graduated') DEFAULT 'active',
  `photo` varchar(255) DEFAULT NULL,
  `admno` int(100) NOT NULL,
  `school_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `firstname`, `lastname`, `gender`, `dob`, `guardian_name`, `guardian_phone`, `address`, `status`, `photo`, `admno`, `school_id`, `class_id`) VALUES
(25, 'samuel', 'mwathi', 'Male', '2025-08-06', 'KKK', '0765432350', 'hhhh', 'active', 'uploads/students/1755520183_1755067844_IHSAN0.png', 56789, 1, 40),
(26, 'RASHID', 'ABDALA', 'Male', '2025-08-06', 'BAMBAULO', '0765432350', 'HJKL', 'active', 'uploads/students/1755521720_1755067844_IHSAN0.png', 9089, 3, 42);

-- --------------------------------------------------------

--
-- Table structure for table `student_subject`
--

CREATE TABLE `student_subject` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subject`
--

INSERT INTO `student_subject` (`id`, `student_id`, `school_id`, `subject_id`, `class_id`) VALUES
(67, 25, 1, 1, 40),
(68, 25, 1, 11, 40),
(69, 25, 1, 14, 40),
(70, 25, 1, 15, 40),
(71, 25, 1, 28, 40),
(72, 25, 1, 29, 40),
(73, 25, 1, 30, 40),
(74, 25, 1, 31, 40),
(82, 25, 1, 33, 40),
(83, 26, 3, 34, 42),
(84, 26, 3, 35, 42),
(85, 26, 3, 36, 42),
(86, 26, 3, 37, 42),
(87, 26, 3, 38, 42),
(88, 26, 3, 39, 42),
(89, 26, 3, 40, 42),
(90, 26, 3, 41, 42),
(98, 26, 3, 43, 42);

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `school_id` int(11) NOT NULL,
  `is_compulsory` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`id`, `name`, `school_id`, `is_compulsory`) VALUES
(1, 'MATHEMATICS', 1, 1),
(11, 'ENGLISH', 1, 1),
(14, 'KISWAHILI', 1, 1),
(15, 'PRE TECHNICAL', 1, 1),
(16, 'MATHEMATICS', 2, 1),
(17, 'ENGLISH', 2, 1),
(18, 'KISWAHILI', 2, 1),
(21, 'SOCIAL STUDIES', 2, 1),
(22, 'AGRICULTURE', 2, 1),
(23, 'CREATIVE ARTS AND SPORT', 2, 1),
(24, 'CRE', 2, 0),
(25, 'IRE', 2, 0),
(26, 'INTERGRATED-SCIENCE', 2, 1),
(27, 'PRE-TECHNICALS', 2, 1),
(28, 'INTERGRATED-SCIENCE', 1, 1),
(29, 'SOCIAL STUDIES', 1, 1),
(30, 'AGRICULTURE', 1, 1),
(31, 'CREATIVE ARTS & SPORTS', 1, 1),
(32, 'CRE', 1, 0),
(33, 'IRE', 1, 0),
(34, 'ENGLISH', 3, 1),
(35, 'KISWAHILI', 3, 1),
(36, 'MATHEMATICS', 3, 1),
(37, 'PRE-TECHNICALS', 3, 1),
(38, 'INTERGRATED-SCIENCE', 3, 1),
(39, 'SOCIAL STUDIES', 3, 1),
(40, 'AGRICULTURE', 3, 1),
(41, 'CREATIVE ARTS & SPORTS', 3, 1),
(42, 'CRE', 3, 0),
(43, 'IRE', 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `school_id` int(11) DEFAULT NULL,
  `enrolment_no` varchar(50) NOT NULL,
  `date_hired` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`id`, `user_id`, `name`, `school_id`, `enrolment_no`, `date_hired`) VALUES
(17, 164, 'ISSA Mwikali', 2, 'ZIBANI001', '2025-08-21'),
(24, 166, 'Taabu Mafimbo', 2, 'ZIBANI002', '2025-08-13'),
(25, 165, 'Omar Sufiani', 3, 'PUNGU001', '2025-08-15');

-- --------------------------------------------------------

--
-- Table structure for table `tsubject_class`
--

CREATE TABLE `tsubject_class` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `school_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tsubject_class`
--

INSERT INTO `tsubject_class` (`id`, `teacher_id`, `subject_id`, `class_id`, `school_id`) VALUES
(34, 25, 34, 42, 3),
(35, 25, 35, 42, 3),
(36, 25, 36, 42, 3),
(37, 25, 37, 42, 3),
(38, 25, 38, 42, 3),
(39, 25, 39, 42, 3),
(40, 25, 40, 42, 3),
(41, 25, 41, 42, 3),
(42, 25, 42, 42, 3),
(43, 25, 43, 42, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','user','Superadmin','teacher','dean','hoi','student') DEFAULT 'user',
  `school_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `FirstName`, `LastName`, `email`, `password`, `role`, `school_id`, `created_at`) VALUES
(83, 'Abdalla', 'Juma', 'sudi@gmail.com', '$2y$10$e9ilZwRO9SvXg8JjzS3Cd.6roVI6qK1DJX0rYDiw5MscET.PT8j3W', 'user', 1, '2025-08-14 07:25:55'),
(164, 'ISSA', 'Mwikali', 'issa@gmail.com', '$2y$10$35s9Xho7nNrQo81rW0TXH.9yAqEsyIbiQbqBiSsUEM2jOKh8W3h3a', 'teacher', 2, '2025-08-14 09:39:49'),
(165, 'Omar', 'Sufiani', 'hommiedelaco@gmail.com', '$2y$10$Y7NXG4eKbXldOnU6Dxd3W.zgyg5Ud5GWy2RWUz/d7kOde.QLDkn8W', 'admin', 3, '2025-08-14 09:44:15'),
(166, 'Taabu', 'Mafimbo', 'T@GMAIL.COM', '$2y$10$uGibWi0TPFV4g4bUMWvEdeK3OMuefnymi.Nn3aYFOqMB/LkUlrX3W', 'admin', 2, '2025-08-14 11:29:53'),
(167, 'Ali', 'Sudi', 'a@gmail.com', '$2y$10$53Fzq6qg3PAkTAWxVWB.1uiehNwKPJcaqkZj8j5meFiRYBUjW4MZW', 'user', 3, '2025-08-18 10:18:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_sch8` (`school_id`);

--
-- Indexes for table `school`
--
ALTER TABLE `school`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_code_2` (`school_code`),
  ADD KEY `school_code` (`school_code`);

--
-- Indexes for table `score`
--
ALTER TABLE `score`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_score` (`std_id`),
  ADD KEY `frk_class` (`class_id`),
  ADD KEY `frk_subject3` (`subject_id`),
  ADD KEY `frk_sch2` (`school_id`),
  ADD KEY `frk_teach` (`teacher_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_sch3` (`school_id`),
  ADD KEY `frk_class34` (`class_id`);

--
-- Indexes for table `student_subject`
--
ALTER TABLE `student_subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_std` (`student_id`),
  ADD KEY `frk_sbj` (`subject_id`),
  ADD KEY `frk_sch4` (`school_id`),
  ADD KEY `frk_class67` (`class_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_sch5` (`school_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_users` (`user_id`),
  ADD KEY `frk_sCH0` (`school_id`);

--
-- Indexes for table `tsubject_class`
--
ALTER TABLE `tsubject_class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_class2` (`class_id`),
  ADD KEY `frk_subject2` (`subject_id`),
  ADD KEY `frk_teacher2` (`teacher_id`),
  ADD KEY `frk_sch90` (`school_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_sch1` (`school_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `school`
--
ALTER TABLE `school`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `score`
--
ALTER TABLE `score`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `student_subject`
--
ALTER TABLE `student_subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tsubject_class`
--
ALTER TABLE `tsubject_class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class`
--
ALTER TABLE `class`
  ADD CONSTRAINT `frk_sch8` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `score`
--
ALTER TABLE `score`
  ADD CONSTRAINT `frk_class` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_sch2` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_score` FOREIGN KEY (`std_id`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_subject3` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_teach` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `frk_class34` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_sch3` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_subject`
--
ALTER TABLE `student_subject`
  ADD CONSTRAINT `frk_class67` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_sbj` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_sch4` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_std` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subject`
--
ALTER TABLE `subject`
  ADD CONSTRAINT `frk_sch5` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `frk_sCH0` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tsubject_class`
--
ALTER TABLE `tsubject_class`
  ADD CONSTRAINT `frk_class2` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_sch90` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_subject2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_teacher2` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `frk_sch1` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
