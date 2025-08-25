-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 25, 2025 at 08:21 AM
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
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`id`, `name`, `school_id`) VALUES
(36, '7B', 1),
(37, '7R', 1),
(38, '8R', 1),
(39, '8B', 1),
(40, '9R', 1),
(41, '9B', 1),
(42, '7B', 3),
(43, '7R', 3),
(44, '8B', 3),
(45, '8R', 3),
(46, '9B', 3),
(47, '9R', 3),
(48, '7B', 2),
(49, '7R', 2),
(50, '8B', 2),
(51, '8R', 2),
(52, '9B', 2),
(53, '9R', 2),
(54, '7BLUE', 4),
(55, '7YELLOW', 4),
(56, '8BLUE', 4),
(57, '8YELLOW', 4),
(58, '9BLUE', 4),
(59, '9YELLOW', 4);

-- --------------------------------------------------------

--
-- Table structure for table `class_teachers`
--

CREATE TABLE `class_teachers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `class_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_teachers`
--

INSERT INTO `class_teachers` (`id`, `name`, `class_id`, `school_id`) VALUES
(1, 'Amina Gakurya', 48, 2),
(2, 'Omar Sufiani', 50, 2);

-- --------------------------------------------------------

--
-- Table structure for table `exam`
--

CREATE TABLE `exam` (
  `id` int(11) NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `term` varchar(50) NOT NULL,
  `exam_type` varchar(100) NOT NULL,
  `year` year(4) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `school_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam`
--

INSERT INTO `exam` (`id`, `exam_name`, `term`, `exam_type`, `year`, `subject`, `file_path`, `created_at`, `school_id`, `teacher_id`) VALUES
(14, 'FORM 4 MATHEMATICS', 'TERM1', 'CAT', '2025', 'MATHS', '../uploads/exams/1755693718_report_27_Term 1_CAT_2025 (11).pdf', '2025-08-20 12:41:58', 3, 25),
(15, 'FORM 4 english', 'TERM1', 'CAT', '2025', 'english', '../uploads/exams/1755694664_1755693718_report_27_Term 1_CAT_2025 (11).pdf', '2025-08-20 12:57:44', 3, 25);

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
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school`
--

INSERT INTO `school` (`id`, `school_name`, `school_code`, `address`, `phone`, `email`, `created_at`, `status`) VALUES
(1, 'BOWA JUNIOR SECONDARY', 'BOWA', 'KOMBANI', '098765789', 'BOWA@GMAIL.COM', '2025-08-11', 1),
(2, 'ZIBANI JUNIOR SECONDARY', 'ZIBANI', 'NGOMBENI', '0987655433', 'kingi@gmail.com', '2025-08-14', 1),
(3, 'PUNGU JUNIOR SECONDARY', 'PUNGU', 'PUNGU', '0987655433', 'P@GMAIL.COM', '2025-08-18', 1),
(4, 'VORONI JUNIOR SCHOOL', 'VORONI', 'Matuga,Kwale county', '0702418632', 'fikratuljannah2@gmail.com', '2025-08-18', 1);

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
(26, 'RASHID', 'ABDALA', 'Male', '2025-08-06', 'BAMBAULO', '0765432350', 'HJKL', 'active', 'uploads/students/1755521720_1755067844_IHSAN0.png', 9089, 3, 42),
(27, 'Iddris', 'Matao', 'Male', '2025-08-20', 'Kk', 'Hhhh', 'Hhh', 'graduated', '', 6756, 2, 52),
(28, 'Adam', 'Dzila', 'Male', '2025-08-20', 'Null', 'Maka', 'Shimoni', 'active', 'uploads/students/1755546698_1000244199.jpg', 9066, 4, 56),
(29, 'HAMAD', 'JUMA', 'Male', '2025-08-06', 'kkkk', '0769565800', 'kwale', 'graduated', 'uploads/students/1755696295_1755067844_IHSAN0.png', 7890, 2, 52),
(30, 'imran', 'abdallah', 'Male', '2025-08-06', 'kuku', '0769565800', 'kombani', 'transferred', '', 8907, 2, 53);

-- --------------------------------------------------------

--
-- Table structure for table `student_subject`
--

CREATE TABLE `student_subject` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `year` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subject`
--

INSERT INTO `student_subject` (`id`, `student_id`, `school_id`, `subject_id`, `class_id`, `year`) VALUES
(67, 25, 1, 1, 40, '2025-08-19'),
(68, 25, 1, 11, 40, '2025-08-19'),
(69, 25, 1, 14, 40, '2025-08-19'),
(70, 25, 1, 15, 40, '2025-08-19'),
(71, 25, 1, 28, 40, '2025-08-19'),
(72, 25, 1, 29, 40, '2025-08-19'),
(73, 25, 1, 30, 40, '2025-08-19'),
(74, 25, 1, 31, 40, '2025-08-19'),
(82, 25, 1, 33, 40, '2025-08-19'),
(83, 26, 3, 34, 42, '2025-08-19'),
(84, 26, 3, 35, 42, '2025-08-19'),
(85, 26, 3, 36, 42, '2025-08-19'),
(86, 26, 3, 37, 42, '2025-08-19'),
(87, 26, 3, 38, 42, '2025-08-19'),
(88, 26, 3, 39, 42, '2025-08-19'),
(89, 26, 3, 40, 42, '2025-08-19'),
(90, 26, 3, 41, 42, '2025-08-19'),
(98, 26, 3, 43, 42, '2025-08-19'),
(99, 27, 2, 16, 50, '2025-08-19'),
(100, 27, 2, 17, 50, '2025-08-19'),
(101, 27, 2, 18, 50, '2025-08-19'),
(102, 27, 2, 21, 50, '2025-08-19'),
(103, 27, 2, 22, 50, '2025-08-19'),
(104, 27, 2, 23, 50, '2025-08-19'),
(105, 27, 2, 26, 50, '2025-08-19'),
(106, 27, 2, 27, 50, '2025-08-19'),
(114, 27, 2, 24, 50, '2025-08-19'),
(115, 28, 4, 44, 55, '2025-08-19'),
(116, 28, 4, 45, 55, '2025-08-19'),
(117, 28, 4, 46, 55, '2025-08-19'),
(118, 28, 4, 47, 55, '2025-08-19'),
(119, 28, 4, 48, 55, '2025-08-19'),
(120, 28, 4, 49, 55, '2025-08-19'),
(121, 28, 4, 50, 55, '2025-08-19'),
(122, 28, 4, 51, 55, '2025-08-19'),
(130, 28, 4, 53, 55, '2025-08-19'),
(131, 29, 2, 16, 52, '2025-08-20'),
(132, 29, 2, 17, 52, '2025-08-20'),
(133, 29, 2, 18, 52, '2025-08-20'),
(134, 29, 2, 21, 52, '2025-08-20'),
(135, 29, 2, 22, 52, '2025-08-20'),
(136, 29, 2, 23, 52, '2025-08-20'),
(137, 29, 2, 26, 52, '2025-08-20'),
(138, 29, 2, 27, 52, '2025-08-20'),
(146, 30, 2, 16, 49, '2025-08-21'),
(147, 30, 2, 17, 49, '2025-08-21'),
(148, 30, 2, 18, 49, '2025-08-21'),
(149, 30, 2, 21, 49, '2025-08-21'),
(150, 30, 2, 22, 49, '2025-08-21'),
(151, 30, 2, 23, 49, '2025-08-21'),
(152, 30, 2, 26, 49, '2025-08-21'),
(153, 30, 2, 27, 49, '2025-08-21'),
(161, 30, 2, 25, 49, '2025-08-21');

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
(43, 'IRE', 3, 0),
(44, 'ENGLISH', 4, 1),
(45, 'KISWAHILI', 4, 1),
(46, 'MATHEMATICS', 4, 1),
(47, 'PRE-TECHNICALS', 4, 1),
(48, 'INTERGRATED-SCIENCE', 4, 1),
(49, 'SOCIAL STUDIES', 4, 1),
(50, 'AGRICULTURE', 4, 1),
(51, 'CREATIVE ARTS & SPORTS', 4, 1),
(52, 'CRE', 4, 0),
(53, 'IRE', 4, 0);

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
(26, 167, 'Ali Sudi', 3, 'PUNGU002', '2025-08-20'),
(27, 172, 'Amina Gakurya', 2, 'ZIBANI003', '2025-08-18'),
(28, 171, 'HAMISI HINDI', 2, 'ZIBANI004', '2025-08-09');

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
(44, 26, 39, 42, 3),
(45, 26, 43, 42, 3),
(55, 27, 16, 52, 2),
(56, 27, 17, 52, 2),
(57, 27, 18, 52, 2),
(58, 27, 21, 52, 2),
(59, 27, 22, 52, 2),
(60, 27, 23, 52, 2),
(61, 27, 25, 52, 2),
(62, 27, 26, 52, 2),
(63, 27, 27, 52, 2),
(64, 28, 16, 53, 2),
(65, 28, 17, 53, 2),
(66, 28, 21, 53, 2),
(67, 28, 22, 53, 2),
(68, 28, 23, 53, 2),
(69, 28, 25, 53, 2),
(70, 28, 26, 53, 2),
(71, 28, 27, 53, 2);

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
(164, 'ISSA', 'Mwikali', 'issa@gmail.com', '$2y$10$35s9Xho7nNrQo81rW0TXH.9yAqEsyIbiQbqBiSsUEM2jOKh8W3h3a', 'admin', 2, '2025-08-14 09:39:49'),
(165, 'Omar', 'Sufiani', 'hommiedelaco@gmail.com', '$2y$10$Y7NXG4eKbXldOnU6Dxd3W.zgyg5Ud5GWy2RWUz/d7kOde.QLDkn8W', 'dean', 2, '2025-08-14 09:44:15'),
(166, 'Taabu', 'Mafimbo', 'T@GMAIL.COM', '$2y$10$uGibWi0TPFV4g4bUMWvEdeK3OMuefnymi.Nn3aYFOqMB/LkUlrX3W', 'dean', 2, '2025-08-14 11:29:53'),
(167, 'Ali', 'Sudi', 'a@gmail.com', '$2y$10$53Fzq6qg3PAkTAWxVWB.1uiehNwKPJcaqkZj8j5meFiRYBUjW4MZW', 'Superadmin', 3, '2025-08-18 10:18:32'),
(168, 'Mwanasiti', 'Mwataila', 'mwatailamwanasiti@gmail.com', '$2y$10$l9DIzuheRv05N/0xVhxuwOoDoCB/5xu8BpsBLMSEYN9U1vyzOBa.2', 'teacher', 2, '2025-08-18 17:41:43'),
(169, 'Iddi', 'Boga', 'sufyanomar58@gmail.com', '$2y$10$0w5MQeBfGgFc77BsIUdJheQK6qAml.gzhw6gYLY4qjdUYSbWJY9.2', 'dean', 2, '2025-08-18 19:34:20'),
(170, 'è´¹ä¸', 'è´¹ä¸', 'faithnjoroge035@gmail.com', '$2y$10$/HzniqMVyKPrGn/uW3A2XepOKTMpvqk6bJzPpopWJLML4NGVMSX0q', 'user', 2, '2025-08-19 19:47:04'),
(171, 'HAMISI', 'HINDI', 'hindihamisi@gmail.com', '$2y$10$UTP9dJA3B.Mb.pu3l5VYF.eUt2tYmkv37umIGJ5Mz8xF8fxVEOfoC', 'teacher', 2, '2025-08-20 07:02:15'),
(172, 'Amina', 'Gakurya', 'aminagakurya802@gmail.com', '$2y$10$1pvj5nnqN74FkftOJw6m5ugQrUlV3Wpc47DaIzOjJUzzQTOr3pyxG', 'teacher', 2, '2025-08-20 09:28:55'),
(174, 'Hamis', 'Salim', 'salimhamis200@gmail.com', '$2y$10$37t3VbfaortnBLNb6fS.hedLYvrBlNgziQAptYkTsG7Od3NVHjaDS', 'user', 1, '2025-08-20 12:33:23');

-- --------------------------------------------------------

--
-- Table structure for table `year_log`
--

CREATE TABLE `year_log` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `done_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `year_log`
--

INSERT INTO `year_log` (`id`, `school_id`, `year`, `done_at`) VALUES
(2, 3, 2025, '2025-08-21 12:13:19'),
(3, 2, 2025, '2025-08-21 12:33:38');

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
-- Indexes for table `class_teachers`
--
ALTER TABLE `class_teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_classteacher` (`class_id`),
  ADD KEY `frk_scghl` (`school_id`);

--
-- Indexes for table `exam`
--
ALTER TABLE `exam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_school90` (`school_id`),
  ADD KEY `frk_tch1` (`teacher_id`);

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
-- Indexes for table `year_log`
--
ALTER TABLE `year_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_id` (`school_id`,`year`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `class_teachers`
--
ALTER TABLE `class_teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `exam`
--
ALTER TABLE `exam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `school`
--
ALTER TABLE `school`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `score`
--
ALTER TABLE `score`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `student_subject`
--
ALTER TABLE `student_subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `tsubject_class`
--
ALTER TABLE `tsubject_class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `year_log`
--
ALTER TABLE `year_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class`
--
ALTER TABLE `class`
  ADD CONSTRAINT `frk_sch8` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `class_teachers`
--
ALTER TABLE `class_teachers`
  ADD CONSTRAINT `frk_classteacher` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_scghl` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

--
-- Constraints for table `year_log`
--
ALTER TABLE `year_log`
  ADD CONSTRAINT `frk_sch` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
