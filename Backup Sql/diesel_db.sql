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
-- Database: `diesel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL DEFAULT 1,
  `current_stock` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `current_stock`) VALUES
(1, 9300.00);

-- --------------------------------------------------------

--
-- Table structure for table `nozzle_readings`
--

CREATE TABLE `nozzle_readings` (
  `id` int(11) NOT NULL,
  `reading_date` date NOT NULL,
  `n1_open` decimal(10,2) NOT NULL,
  `n1_close` decimal(10,2) NOT NULL,
  `n2_open` decimal(10,2) NOT NULL,
  `n2_close` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nozzle_readings`
--

INSERT INTO `nozzle_readings` (`id`, `reading_date`, `n1_open`, `n1_close`, `n2_open`, `n2_close`, `notes`, `created_at`) VALUES
(4, '2025-10-14', 1000.00, 2000.00, 100.00, 200.00, 'd', '2025-10-14 15:35:19'),
(5, '2025-10-16', 10000.00, 10500.00, 500.00, 700.00, '', '2025-10-16 05:56:25');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `add_date` date NOT NULL,
  `liters_added` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`id`, `add_date`, `liters_added`, `notes`, `created_at`) VALUES
(3, '2025-10-14', 10000.00, 'hi', '2025-10-14 15:34:49'),
(4, '2025-10-16', 1100.00, '', '2025-10-16 05:55:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nozzle_readings`
--
ALTER TABLE `nozzle_readings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reading_date` (`reading_date`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `nozzle_readings`
--
ALTER TABLE `nozzle_readings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
