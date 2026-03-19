-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 27, 2026 at 11:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `infoctess`
--

-- --------------------------------------------------------

--
-- Table structure for table `programme`
--

CREATE TABLE `programme` (
  `id` int(11) NOT NULL,
  `programme_code` varchar(7) NOT NULL,
  `programme_name` varchar(110) NOT NULL,
  `num_of_Stu` varchar(122) NOT NULL,
  `year` varchar(5) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `programme`
--

INSERT INTO `programme` (`id`, `programme_code`, `programme_name`, `num_of_Stu`, `year`, `created_at`) VALUES
(1, '526214', 'BE.D INFORMATION COMMUNICATION TECHNOLOGY', '', '2026', '2026-02-27 11:01:32');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(10) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `student_mail` varchar(255) NOT NULL,
  `recovery_mail` varchar(111) NOT NULL,
  `programme` int(11) NOT NULL,
  `roles` enum('user','rep','admin','ta','lec') NOT NULL,
  `active` enum('0','1','2','') NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `group_id` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `student_name`, `student_mail`, `recovery_mail`, `programme`, `roles`, `active`, `updated_at`, `created_at`, `group_id`) VALUES
(1, '5262140032', 'PEPRAH DANIEL', '5262140032@stu.uew.edu.gh', 'UEW90988', 526214, 'rep', '1', '2026-02-27 21:57:11', '2026-02-27 11:08:23', '1'),
(2, '5262140033', 'HEISDANITO', 'heis@uew', 'uew222', 526214, 'user', '1', '2026-02-27 22:04:18', '2026-02-27 19:04:29', '1');

-- --------------------------------------------------------

--
-- Table structure for table `group_main`
--

CREATE TABLE `group_main` (
  `group_id` varchar(10) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `programme_id` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `group_main`
--

INSERT INTO `group_main` (`group_id`, `group_name`, `programme_id`) VALUES
('1', 'GROUP 1 ICTE', '526214');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_id` varchar(7) NOT NULL,
  `course_name` varchar(50) NOT NULL,
  `programme_id` varchar(7) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `group_id` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_id`, `course_name`, `programme_id`, `created_at`, `updated_at`, `group_id`) VALUES
(1, 'EDF111', 'FOUNDATION OF EDUCATION', '526214', '2026-02-27 11:02:27', '2026-02-27 11:02:27', '1'),
(3, 'ICTW111', 'VISUAL LITERACY', '526214', '2026-02-27 18:25:20', '2026-02-27 18:25:20', '1'),
(4, 'MATD112', 'INTO TO CALCULUS', '526214', '2026-02-27 18:25:20', '2026-02-27 18:25:20', '1');

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE `tokens` (
  `id` int(11) NOT NULL,
  `student_id` varchar(10) DEFAULT NULL,
  `token` varchar(200) NOT NULL,
  `is_active` enum('0','1','','') NOT NULL,
  `severToken` varchar(255) NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tokens`
--

INSERT INTO `tokens` (`id`, `student_id`, `token`, `is_active`, `severToken`, `updated_at`, `created_at`) VALUES
(1, '5262140032', ')a35!g54&b46@c86#c53#h23*b64@j35)5262140032', '1', '16165262140032)a35!g54&b46@c86#c53#h23*b64@j35)52621400321', '2026-02-27 10:10:30', '2026-02-27 10:10:30'),
(2, '5262140032', '74$h60*h47*h59*i77(e48%d25$5262140032', '1', '982526214003274$h60*h47*h59*i77(e48%d25$52621400321', '2026-02-27 10:30:15', '2026-02-27 10:30:15'),
(3, '5262140032', 'edc52#i14(a14!e17%d45$5262140032', '1', '19665262140032edc52#i14(a14!e17%d45$52621400320', '2026-02-27 11:34:45', '2026-02-27 11:34:45'),
(4, '5262140032', 'eda99!a33!d6$5262140032', '1', '10345262140032eda99!a33!d6$52621400320', '2026-02-27 11:50:19', '2026-02-27 11:50:19'),
(5, '5262140032', 'edi65(b18@d77$f83^h27*i78(5262140032', '1', '335262140032edi65(b18@d77$f83^h27*i78(52621400320', '2026-02-27 13:18:53', '2026-02-27 13:18:53'),
(6, '5262140032', '61&d10$e35%a36!g65&h42*g81&a2!i39(f77^5262140032', '1', '1916526214003261&d10$e35%a36!g65&h42*g81&a2!i39(f77^52621400321', '2026-02-27 13:39:00', '2026-02-27 13:39:00'),
(7, '5262140032', '73$d84$h9*j90)i68(b2@b58@i44(5262140032', '1', '1111526214003273$d84$h9*j90)i68(b2@b58@i44(52621400321', '2026-02-27 13:47:29', '2026-02-27 13:47:29'),
(8, '5262140032', 'edc57#h52*f74^c59#5262140032', '1', '14485262140032edc57#h52*f74^c59#52621400321', '2026-02-27 13:50:36', '2026-02-27 13:50:36'),
(9, '5262140032', 'edj45)f84^d86$e1%5262140032', '1', '9925262140032edj45)f84^d86$e1%52621400320', '2026-02-27 13:52:59', '2026-02-27 13:52:59'),
(10, '5262140032', 'edc18#5262140032', '1', '6085262140032edc18#52621400321', '2026-02-27 15:03:00', '2026-02-27 15:03:00'),
(11, '5262140032', '69*g99&5262140032', '1', '1645526214003269*g99&52621400320', '2026-02-27 16:04:46', '2026-02-27 16:04:46'),
(12, '5262140032', 'eda28!b1@5262140032', '1', '15535262140032eda28!b1@52621400321', '2026-02-27 16:19:16', '2026-02-27 16:19:16'),
(13, '5262140032', 'edh33*g66&c60#5262140032', '1', '10855262140032edh33*g66&c60#52621400320', '2026-02-27 17:21:21', '2026-02-27 17:21:21'),
(14, '5262140032', 'edb79@5262140032', '1', '2075262140032edb79@52621400321', '2026-02-27 17:28:47', '2026-02-27 17:28:47'),
(15, '5262140032', 'edi56(5262140032', '1', '5995262140032edi56(52621400320', '2026-02-27 17:32:13', '2026-02-27 17:32:13'),
(16, '5262140033', ')b70@g61&b68@h33*d86$c89#j55)h20*e69%c89#j18)c15#f26^c96#f55^g48&a90!e79%a2!a74!h77*c70#c6#a70!b81@i6(f59^h41*g73&h23*5262140033', '1', '2805262140033)b70@g61&b68@h33*d86$c89#j55)h20*e69%c89#j18)c15#f26^c96#f55^g48&a90!e79%a2!a74!h77*c70#c6#a70!b81@i6(f59^h41*g73&h23*52621400331', '2026-02-27 18:10:56', '2026-02-27 18:10:56'),
(17, '5262140033', '@e30%j37)i25(f82^a16!d29$j17)b70@g61&b68@h33*d86$c89#j55)h20*e69%c89#j18)c15#f26^c96#f55^g48&a90!e79%a2!5262140033', '1', '9895262140033@e30%j37)i25(f82^a16!d29$j17)b70@g61&b68@h33*d86$c89#j55)h20*e69%c89#j18)c15#f26^c96#f55^g48&a90!e79%a2!52621400330', '2026-02-27 18:10:56', '2026-02-27 18:10:56'),
(18, '5262140033', 'c89#j55)h20*e69%c89#j18)c15#f26^c96#f55^g48&a90!e79%a2!a74!h77*c70#c6#a70!b81@i6(f59^h41*g73&h23*a74!j26)g97&a53!f86^d60$a14!g6&e52%5262140033', '1', '345262140033c89#j55)h20*e69%c89#j18)c15#f26^c96#f55^g48&a90!e79%a2!a74!h77*c70#c6#a70!b81@i6(f59^h41*g73&h23*a74!j26)g97&a53!f86^d60$a14!g6&e52%52621400331', '2026-02-27 18:15:50', '2026-02-27 18:15:50'),
(19, '5262140032', 'edi12(d14$j16)b84@5262140032', '1', '9695262140032edi12(d14$j16)b84@52621400321', '2026-02-27 18:39:09', '2026-02-27 18:39:09'),
(20, '5262140033', '2(h90*5262140033', '1', '186152621400332(h90*52621400330', '2026-02-27 20:02:38', '2026-02-27 20:02:38'),
(21, '5262140033', 'edf41^c23#5262140033', '1', '1625262140033edf41^c23#52621400331', '2026-02-27 20:24:40', '2026-02-27 20:24:40'),
(22, '5262140032', '36(e57%a71!c66#5262140032', '1', '921526214003236(e57%a71!c66#52621400320', '2026-02-27 20:42:19', '2026-02-27 20:42:19'),
(23, '5262140033', 'edi94(d94$a51!i44(j42)5262140033', '1', '18015262140033edi94(d94$a51!i44(j42)52621400330', '2026-02-27 21:17:28', '2026-02-27 21:17:28'),
(24, '5262140033', 'b52@g67&j95)b32@b3@i12(b79@f78^d20$g63&d15$c94#b41@c22#g97&i56(a31!e26%d11$d31$d17$g44&a70!h90*d57$d40$c36#e8%d30$h88*5262140033', '1', '7855262140033b52@g67&j95)b32@b3@i12(b79@f78^d20$g63&d15$c94#b41@c22#g97&i56(a31!e26%d11$d31$d17$g44&a70!h90*d57$d40$c36#e8%d30$h88*52621400330', '2026-02-27 21:35:53', '2026-02-27 21:35:53'),
(25, '5262140033', 'edj4)5262140033', '1', '5235262140033edj4)52621400330', '2026-02-27 21:49:12', '2026-02-27 21:49:12');

-- --------------------------------------------------------

--
-- Table structure for table `qrcode`
--

CREATE TABLE `qrcode` (
  `id` int(11) NOT NULL,
  `QRcode` varchar(255) NOT NULL,
  `session_code` varchar(255) DEFAULT NULL,
  `longitude` varchar(50) NOT NULL,
  `latitude` varchar(50) NOT NULL,
  `serial_status` varchar(20) NOT NULL,
  `is_active` varchar(2) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `created_at` varchar(255) NOT NULL,
  `expire_at` datetime NOT NULL,
  `group_id` varchar(7) DEFAULT NULL,
  `course_id` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `qrcode`
--

INSERT INTO `qrcode` (`id`, `QRcode`, `session_code`, `longitude`, `latitude`, `serial_status`, `is_active`, `created_by`, `created_at`, `expire_at`, `group_id`, `course_id`) VALUES
(1, 'QRCodeForUEW101att1070h3!#e1i%2s$3', '4166UEW0QR81224heis', '-0.1902000', '5.5545000', 'qrcode', '0', '5262140032', '2026-02-27 10:10:42', '2026-02-27 11:21:42', '1', 'EDF111'),
(2, 'QRCodeForUEW101att77h3!#e1i%2s$1', '4166UEW0QR81224heis', '', '', '', '0', '5262140032', '2026-02-27 11:55:04', '2026-02-27 13:58:28', '1', 'EDF111'),
(4, 'QRCodeForUEW101att2753he$^**is0', '4166UEW0QR81224heis', '-0.1902000', '5.5545000', '', '0', '5262140032', '2026-02-27 14:00:40', '2026-02-27 15:13:02', '1', 'EDF111'),
(5, 'QRCodeForUEW101att546^&*sd%gh%h3!#e1i%2s$5', '1436UEW5QR528095heis', '', '', '', '0', '5262140032', '2026-02-27 15:20:57', '2026-02-27 16:33:00', '1', 'EDF111'),
(6, 'QRCodeForUEW101att104h3!#e1i%2s$3', '8124UEW3QR1184685heis', '-0.1902000', '5.5545000', '', '0', '5262140032', '2026-02-27 16:41:01', '2026-02-27 18:26:58', '1', 'EDF111'),
(7, 'QRCodeForUEW101att35h3!#e1i%2s$3', '693UEW3QR666012heis', '-0.1902000', '5.5545000', 'qrcode', '0', '5262140032', '2026-02-27 18:27:30', '2026-02-27 20:05:01', '1', 'EDF111'),
(8, 'QRCodeForUEW101att1868^&*sd%gh%h3!#e1i%2s$4', '1275UEW4QR627021heis', '', '', '', '1', '5262140032', '2026-02-27 21:10:43', '0000-00-00 00:00:00', '1', 'EDF111');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(50) NOT NULL,
  `status` enum('active','deactive','','') NOT NULL,
  `group_rep_id` varchar(12) NOT NULL,
  `group_rep_id_2` varchar(12) NOT NULL,
  `programme_id` varchar(7) NOT NULL,
  `course_id` varchar(255) DEFAULT NULL,
  `academic_year` varchar(5) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `group_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `group_name`, `status`, `group_rep_id`, `group_rep_id_2`, `programme_id`, `course_id`, `academic_year`, `created_at`, `updated_at`, `group_id`) VALUES
(2, 'ICTE GROUP ONE', 'active', '5262140032', '5262140032', '526214', 'EDF111', '2026', '2026-02-27 11:06:44', '2026-02-27 11:06:44', '1'),
(3, 'GROUP ONE 1', 'active', '5262140032', '5262140032', '526214', 'ICTW111', '2026', '2026-02-27 18:30:13', '2026-02-27 18:30:13', '1'),
(4, 'GROUP 1', 'active', '5262140032', '5262140032', '526214', 'MATD112', '2026', '2026-02-27 18:30:13', '2026-02-27 18:30:13', '1');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` varchar(12) NOT NULL,
  `group_id` int(22) NOT NULL,
  `course_id` varchar(11) NOT NULL,
  `session_user_token` varchar(200) NOT NULL,
  `session_code` varchar(202) NOT NULL,
  `qrcode` varchar(250) NOT NULL,
  `serial` varchar(222) NOT NULL,
  `latitude` varchar(222) NOT NULL,
  `longitude` varchar(222) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `group_id`, `course_id`, `session_user_token`, `session_code`, `qrcode`, `serial`, `latitude`, `longitude`, `created_at`) VALUES
(1, '2147483640', 1, 'EDF111', '', '693UEW3QR666012heis', '8687UEW1QR160318heis', 'qrcode', '5.5545000', '-0.1902000', '2026-02-27 10:14:29'),
(2, '2147483600', 1, 'EDF111', '', '693UEW3QR666012heis', '8687UEW1QR160318heis', 'qrcode', '5.5545000', '-0.1902000', '2026-02-27 10:14:44'),
(3, '5314483647', 1, 'EDF111', '', '693UEW3QR666012heis', '8687UEW1QR160318heis', 'qrcode', '5.5545000', '-0.1902000', '2026-02-27 10:14:44'),
(4, '5263002933', 1, 'EDF111', '', '693UEW3QR666012heis', '8687UEW1QR160318heis', 'qrcode', '5.5545000', '-0.1902000', '2026-02-27 10:14:44'),
(5, '3147483647', 1, 'EDF111', '', '693UEW3QR666012heis', '8687UEW1QR160318heis', 'qrcode', '5.5545000', '-0.1902000', '2026-02-27 10:14:44'),
(6, '2147483647', 1, 'EDF111', '', '693UEW3QR666012heis', '8687UEW1QR160318heis', 'qrcode', '5.5545000', '-0.1902000', '2026-02-27 10:14:44'),
(7, '5262140032', 1, 'EDF111', '', '1275UEW4QR627021heis', '8124UEW3QR1184685heis', 'qrcode', '5.5545000', '-0.1902000', '2026-02-27 18:38:57');

-- --------------------------------------------------------

--
-- Table structure for table `user_pref`
--

CREATE TABLE `user_pref` (
  `id` int(11) NOT NULL,
  `pageColor` varchar(111) NOT NULL,
  `user_index` varchar(122) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for table `programme`
--
ALTER TABLE `programme`
  ADD PRIMARY KEY (`programme_code`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `idx_students_programme` (`programme`),
  ADD KEY `idx_students_group` (`group_id`);

--
-- Indexes for table `group_main`
--
ALTER TABLE `group_main`
  ADD PRIMARY KEY (`group_id`),
  ADD KEY `idx_group_main_programme` (`programme_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `idx_courses_programme` (`programme_id`),
  ADD KEY `idx_courses_group` (`group_id`);

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tokens_student` (`student_id`),
  ADD KEY `idx_tokens_token` (`token`);

--
-- Indexes for table `qrcode`
--
ALTER TABLE `qrcode`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qrcode_created_by` (`created_by`),
  ADD KEY `idx_qrcode_group` (`group_id`),
  ADD KEY `idx_qrcode_course` (`course_id`),
  ADD KEY `idx_qrcode_session_code` (`session_code`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `idx_groups_rep1` (`group_rep_id`),
  ADD KEY `idx_groups_rep2` (`group_rep_id_2`),
  ADD KEY `idx_groups_programme` (`programme_id`),
  ADD KEY `idx_groups_course` (`course_id`),
  ADD KEY `idx_groups_group` (`group_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attendance_student` (`student_id`),
  ADD KEY `idx_attendance_group` (`group_id`),
  ADD KEY `idx_attendance_course` (`course_id`),
  ADD KEY `idx_attendance_session_token` (`session_user_token`),
  ADD KEY `idx_attendance_session_code` (`session_code`);

--
-- Indexes for table `user_pref`
--
ALTER TABLE `user_pref`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `programme`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

ALTER TABLE `qrcode`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `user_pref`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for tables
--

-- Constraints for students table
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_programme` FOREIGN KEY (`programme`) REFERENCES `programme` (`programme_code`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_students_group` FOREIGN KEY (`group_id`) REFERENCES `group_main` (`group_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Constraints for group_main table
ALTER TABLE `group_main`
  ADD CONSTRAINT `fk_group_main_programme` FOREIGN KEY (`programme_id`) REFERENCES `programme` (`programme_code`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Constraints for courses table
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_programme` FOREIGN KEY (`programme_id`) REFERENCES `programme` (`programme_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_courses_group` FOREIGN KEY (`group_id`) REFERENCES `group_main` (`group_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Constraints for tokens table
ALTER TABLE `tokens`
  ADD CONSTRAINT `fk_tokens_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for qrcode table
ALTER TABLE `qrcode`
  ADD CONSTRAINT `fk_qrcode_created_by` FOREIGN KEY (`created_by`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_qrcode_group` FOREIGN KEY (`group_id`) REFERENCES `group_main` (`group_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_qrcode_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Constraints for groups table
ALTER TABLE `groups`
  ADD CONSTRAINT `fk_groups_rep1` FOREIGN KEY (`group_rep_id`) REFERENCES `students` (`student_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_groups_rep2` FOREIGN KEY (`group_rep_id_2`) REFERENCES `students` (`student_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_groups_programme` FOREIGN KEY (`programme_id`) REFERENCES `programme` (`programme_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_groups_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_groups_group` FOREIGN KEY (`group_id`) REFERENCES `group_main` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for attendance table
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_group` FOREIGN KEY (`group_id`) REFERENCES `group_main` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_session_token` FOREIGN KEY (`session_user_token`) REFERENCES `tokens` (`token`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_session_code` FOREIGN KEY (`session_code`) REFERENCES `qrcode` (`session_code`) ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;