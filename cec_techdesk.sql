-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2025 at 04:16 PM
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
-- Database: `cec_techdesk`
--

-- --------------------------------------------------------

--
-- Table structure for table `incidents`
--

CREATE TABLE `incidents` (
  `id` int(11) NOT NULL,
  `incident_type` varchar(100) NOT NULL,
  `incident_details` text NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `severity` enum('Low','Medium','High') DEFAULT 'Low',
  `reported_by` varchar(100) NOT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ticket_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incidents`
--

INSERT INTO `incidents` (`id`, `incident_type`, `incident_details`, `title`, `description`, `severity`, `reported_by`, `student_name`, `reported_at`, `ticket_id`) VALUES
(1, 'Mouse', 'poured water', '', '', 'Low', 'student', NULL, '2025-08-25 12:28:42', NULL),
(2, 'lost', 'lost mouse pad', '', '', 'Low', 'student', NULL, '2025-08-26 06:06:49', NULL),
(3, 'Fire', 'burning', '', '', 'Low', 'student', NULL, '2025-08-26 10:26:54', NULL),
(5, 'burning chair', 'in the room 304 ', '', '', 'Low', 'student', NULL, '2025-09-24 09:50:52', NULL),
(6, 'nanika', 'halaaaa', '', '', 'Low', 'student', NULL, '2025-09-24 16:06:48', NULL),
(7, 'broken monitor', 'hhhhhnnnj', '', '', 'Low', 'student', NULL, '2025-10-16 09:10:57', NULL),
(8, 'fire', 'nag fire', '', '', 'Low', 'student', NULL, '2025-10-22 19:19:11', NULL),
(9, 'water', 'nag tubig', '', '', 'Low', 'student', NULL, '2025-10-22 19:19:20', NULL),
(10, 'earth', 'nag linog', '', '', 'Low', 'student', NULL, '2025-10-22 19:19:30', NULL),
(11, 'air', 'air pollution', '', '', 'Low', 'student', NULL, '2025-10-22 19:19:41', NULL),
(12, 'fire', 'nag kayu', '', '', 'Low', 'student', NULL, '2025-10-22 19:30:07', NULL),
(13, 'fire', 'water', '', '', 'Low', 'student', 'Jussa', '2025-10-23 08:32:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `incident_reports`
--

CREATE TABLE `incident_reports` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `reported_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `incident_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `problem` text DEFAULT NULL,
  `urgency` enum('Low','Medium','High','Prioritize') DEFAULT 'Low',
  `status` enum('Open','In Progress','Closed') DEFAULT 'Open',
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lab_room` varchar(50) DEFAULT NULL,
  `teacher_name` varchar(100) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `pc_number` int(11) DEFAULT NULL,
  `ticket_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `comment`, `problem`, `urgency`, `status`, `created_by`, `created_at`, `lab_room`, `teacher_name`, `student_name`, `pc_number`, `ticket_date`) VALUES
(1, 'chuchu', 'monitor broke', 'High', 'Closed', 'student', '2025-08-25 11:30:26', NULL, NULL, NULL, NULL, NULL),
(2, 'cha', 'pc problem', 'Medium', 'In Progress', 'student', '2025-08-25 11:42:54', NULL, NULL, NULL, NULL, NULL),
(3, 'che', 'keyboard', 'Low', 'Closed', 'student', '2025-08-26 06:06:15', NULL, NULL, NULL, NULL, NULL),
(4, 'Monitor', 'Won\'t On', 'High', 'In Progress', 'student', '2025-08-26 07:08:18', 'Lab 4', 'Nino', 'jussa', 12, '2025-08-26'),
(5, 'chucha', 'keyboard broke', 'High', 'Closed', 'student', '2025-08-26 10:26:30', 'Lab 6', 'Joejean', 'Jeandelle', 3, '2025-08-21'),
(6, 'nevermind', 'gwapo rakay ko', 'High', 'Closed', 'student', '2025-09-03 07:09:39', 'Lab 2', 'Jussa', 'yaya', 1, '2025-09-02'),
(7, 'nevermind', 'aaaaa', 'Low', 'Closed', 'admin', '2025-09-24 06:57:49', 'Lab 2', 'Jussa', 'yaya', 1, '2025-09-02'),
(8, 'Monitor', 'poured water', 'High', 'In Progress', 'student', '2025-09-24 07:09:56', 'OCL', 'Cris', 'psy', 22, '2025-10-05'),
(9, 'Nihao', 'Wako ka palit og sabon', 'High', 'Closed', 'student', '2025-09-24 09:22:07', 'Lab 5', 'Jussabelle', 'Niño', 100, '2025-09-24'),
(10, 'Naputol ang Mouse', 'Broken Mouse', 'Low', 'Closed', 'student', '2025-10-13 02:06:56', 'Lab 4', 'Mariscal', 'Veliganio', 45, '2025-10-13'),
(11, 'fghh', 'no internet', 'Medium', 'Open', 'student', '2025-10-16 09:09:58', 'Lab 2', 'Mariscal', 'lumayag', 3, '2025-10-25'),
(12, '123', 'Lag', 'Low', 'Open', 'student', '2025-10-21 18:19:41', 'Lab 1', 'Mariscal', 'Jussa', 20, '2025-12-08'),
(13, '123', 'internet', 'Low', 'Open', 'student', '2025-10-22 18:23:28', 'Lab 1', 'Haduken', 'kenken', 3, '2028-10-24'),
(14, 'dady', 'scfswe', 'High', 'Open', 'student', '2025-10-22 20:47:24', 'Lab 1', 'haoni', 'nihao', 35, '2025-10-23'),
(15, 'gg', 'gg', 'Medium', 'Open', 'student', '2025-10-22 20:48:53', 'CL 1', 'gg', 'gg', 33, '2025-10-23'),
(16, 'ruiz', 'downloading', 'Medium', 'Open', 'student', '2025-10-23 08:09:10', 'CL 3', 'Psyche', 'Jussa', 35, '2025-10-23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student','staff') NOT NULL,
  `availability` enum('Available','On Break','Teaching','Working on an Issue','Offline','Lunch') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `availability`) VALUES
(1, 'admin', 'admin123', 'admin', ''),
(2, 'student', 'student123', 'student', 'Available'),
(5, 'staff', 'staff123', 'staff', 'Available');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_incidents_reported_by` (`reported_by`),
  ADD KEY `fk_incidents_ticket_id` (`ticket_id`);

--
-- Indexes for table `incident_reports`
--
ALTER TABLE `incident_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_incident_reports_reported_by` (`reported_by`),
  ADD KEY `fk_incident_reports_incident_id` (`incident_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tickets_created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `incident_reports`
--
ALTER TABLE `incident_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `incidents`
--
ALTER TABLE `incidents`
  ADD CONSTRAINT `fk_incidents_reported_by` FOREIGN KEY (`reported_by`) REFERENCES `users` (`username`),
  ADD CONSTRAINT `fk_incidents_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`);

--
-- Constraints for table `incident_reports`
--
ALTER TABLE `incident_reports`
  ADD CONSTRAINT `fk_incident_reports_incident_id` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`),
  ADD CONSTRAINT `fk_incident_reports_reported_by` FOREIGN KEY (`reported_by`) REFERENCES `users` (`username`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_tickets_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
