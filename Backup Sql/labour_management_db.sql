-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2025 at 04:57 PM
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
-- Database: `labour_management_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `labour_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('P','A') NOT NULL COMMENT 'P=Present, A=Absent',
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `labour_id`, `attendance_date`, `status`, `recorded_at`) VALUES
(1, 1, '2025-10-13', 'P', '2025-10-13 17:52:22'),
(2, 1, '2025-10-12', 'A', '2025-10-13 17:52:22'),
(3, 2, '2025-10-13', 'P', '2025-10-13 17:52:22'),
(5, 1, '2025-10-14', 'P', '2025-10-13 17:57:39'),
(6, 1, '2025-10-15', 'A', '2025-10-13 17:58:06'),
(7, 1, '2025-11-01', 'P', '2025-10-13 18:07:25'),
(9, 1, '2025-09-01', 'P', '2025-10-13 18:08:06'),
(11, 1, '2026-01-01', 'P', '2025-10-13 18:29:36'),
(12, 4, '2025-10-13', 'P', '2025-10-13 18:40:34'),
(13, 4, '2025-10-15', 'A', '2025-10-13 18:40:46'),
(14, 4, '2025-11-01', 'P', '2025-10-13 18:41:34'),
(15, 4, '2025-11-02', 'A', '2025-10-13 18:42:08'),
(16, 1, '2025-11-04', 'P', '2025-10-13 18:58:44'),
(17, 5, '2025-10-13', 'P', '2025-10-13 19:07:09'),
(19, 5, '2025-10-15', 'P', '2025-10-15 18:04:56'),
(22, 6, '2025-10-15', 'A', '2025-10-16 06:04:13'),
(23, 6, '2025-10-10', 'P', '2025-10-16 06:04:20'),
(25, 6, '2025-10-16', 'P', '2025-10-16 06:04:28'),
(26, 6, '2025-02-13', 'P', '2025-10-16 06:05:14');

-- --------------------------------------------------------

--
-- Table structure for table `labours`
--

CREATE TABLE `labours` (
  `labour_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `labours`
--

INSERT INTO `labours` (`labour_id`, `name`, `mobile_number`, `created_at`) VALUES
(1, 'Sample Worker 1', '9876543210', '2025-10-13 17:52:22'),
(2, 'Test Labourer 2', '9988776655', '2025-10-13 17:52:22'),
(4, 'sumit nagpure', '43345', '2025-10-13 18:10:26'),
(5, 'Hiper Test', '1234567890', '2025-10-13 19:06:31'),
(6, 'akash', '666666666', '2025-10-16 05:57:21');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `labour_id` int(11) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `note` text DEFAULT NULL,
  `payment_date` date NOT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `labour_id`, `paid_amount`, `note`, `payment_date`, `recorded_at`) VALUES
(1, 1, 150.00, 'Advance for ', '2025-10-13', '2025-10-13 17:52:22'),
(2, 1, 500.00, 'payment', '0000-00-00', '2025-10-13 17:58:33'),
(3, 1, 500.00, 'cash', '2025-10-18', '2025-10-13 18:01:45'),
(4, 1, 200.00, 'rent', '0000-00-00', '2025-10-13 18:02:29'),
(5, 1, 200.00, 'rent', '0000-00-00', '2025-10-13 18:02:38'),
(6, 1, 200.00, 'rent', '0000-00-00', '2025-10-13 18:03:03'),
(7, 1, 125.00, 'day', '0000-00-00', '2025-10-13 18:04:20'),
(8, 4, 500.00, 'gf', '0000-00-00', '2025-10-13 18:18:02'),
(9, 1, 500.00, 'fdgd', '2025-10-15', '2025-10-13 18:22:36'),
(10, 1, 500.00, 'fdgd', '2025-10-14', '2025-10-13 18:22:43'),
(11, 1, 500.00, '', '0000-00-00', '2025-10-13 18:36:04'),
(12, 4, 1000.00, 'cash\\r\\n', '0000-00-00', '2025-10-13 18:42:42'),
(13, 4, 300.00, 'reacharge', '0000-00-00', '2025-10-13 18:43:13'),
(14, 4, 588.00, 'ggt', '0000-00-00', '2025-10-13 18:44:15'),
(15, 1, 800.00, 'online', '2025-10-01', '2025-10-13 18:45:55'),
(16, 1, 500.00, 'fdg', '0000-00-00', '2025-10-13 18:48:44'),
(17, 1, 500.00, 'fdg', '2025-10-16', '2025-10-13 18:53:33'),
(18, 1, 55.00, 'fvf', '0000-00-00', '2025-10-13 18:56:41'),
(19, 1, 55.00, 'payment', '2025-09-01', '2025-10-13 18:57:45'),
(20, 1, 55.00, 'fgdf', '2025-10-23', '2025-10-13 18:57:57'),
(21, 1, 500.00, 'fgf', '2025-09-01', '2025-10-13 19:03:56'),
(22, 5, 50.00, 'payment', '2025-10-01', '2025-10-13 19:06:48'),
(23, 5, 500.00, 'fhyf', '2025-10-14', '2025-10-14 15:07:19'),
(24, 6, 500.00, 'cash', '2025-10-15', '2025-10-16 06:03:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_attendance` (`labour_id`,`attendance_date`),
  ADD KEY `fk_labour_attendance_idx` (`labour_id`);

--
-- Indexes for table `labours`
--
ALTER TABLE `labours`
  ADD PRIMARY KEY (`labour_id`),
  ADD UNIQUE KEY `mobile_number_UNIQUE` (`mobile_number`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_labour_payment_idx` (`labour_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `labours`
--
ALTER TABLE `labours`
  MODIFY `labour_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_labour_attendance` FOREIGN KEY (`labour_id`) REFERENCES `labours` (`labour_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_labour_payment` FOREIGN KEY (`labour_id`) REFERENCES `labours` (`labour_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
