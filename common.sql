SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ########################################################
-- START: Content from petrol_db.sql (Database: petrol_db)
-- ########################################################

CREATE DATABASE IF NOT EXISTS `petrol_db`;
USE `petrol_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `config` (
  `id` int(11) NOT NULL DEFAULT 1,
  `current_stock` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `config` (`id`, `current_stock`) VALUES
(1, 4500.00);

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

INSERT INTO `nozzle_readings` (`id`, `reading_date`, `n1_open`, `n1_close`, `n2_open`, `n2_close`, `notes`, `created_at`) VALUES
(7, '2025-10-15', 1000.00, 2000.00, 2000.00, 5000.00, '', '2025-10-14 13:57:30'),
(9, '2025-10-14', 1000.00, 1500.00, 2000.00, 3000.00, 'hfh', '2025-10-14 15:08:55');

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `add_date` date NOT NULL,
  `liters_added` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `stock` (`id`, `add_date`, `liters_added`, `notes`, `created_at`) VALUES
(7, '2025-10-15', 10000.00, 'ramesh', '2025-10-15 17:55:23');

ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `nozzle_readings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reading_date` (`reading_date`);

ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `nozzle_readings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

-- ########################################################
-- END: Content from petrol_db.sql
-- ########################################################

----------------------------------------------------------

-- ########################################################
-- START: Content from labour_management_db.sql (Database: labour_management_db)
-- ########################################################

CREATE DATABASE IF NOT EXISTS `labour_management_db`;
USE `labour_management_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `labour_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('P','A') NOT NULL COMMENT 'P=Present, A=Absent',
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(18, 5, '2025-10-15', 'A', '2025-10-13 19:07:16');

CREATE TABLE `labours` (
  `labour_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `labours` (`labour_id`, `name`, `mobile_number`, `created_at`) VALUES
(1, 'Sample Worker 1', '9876543210', '2025-10-13 17:52:22'),
(2, 'Test Labourer 2', '9988776655', '2025-10-13 17:52:22'),
(4, 'sumit nagpure', '43345', '2025-10-13 18:10:26'),
(5, 'Hiper Test', '1234567890', '2025-10-13 19:06:31');

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `labour_id` int(11) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `note` text DEFAULT NULL,
  `payment_date` date NOT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(22, 5, 50.00, 'payment', '2025-10-01', '2025-10-13 19:06:48');

ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_attendance` (`labour_id`,`attendance_date`),
  ADD KEY `fk_labour_attendance_idx` (`labour_id`);

ALTER TABLE `labours`
  ADD PRIMARY KEY (`labour_id`),
  ADD UNIQUE KEY `mobile_number_UNIQUE` (`mobile_number`);

ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_labour_payment_idx` (`labour_id`);

ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

ALTER TABLE `labours`
  MODIFY `labour_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_labour_attendance` FOREIGN KEY (`labour_id`) REFERENCES `labours` (`labour_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `payments`
  ADD CONSTRAINT `fk_labour_payment` FOREIGN KEY (`labour_id`) REFERENCES `labours` (`labour_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

-- ########################################################
-- END: Content from labour_management_db.sql
-- ########################################################

----------------------------------------------------------

-- ########################################################
-- START: Content from account_management.sql (Database: account_manager_db)
-- ########################################################

CREATE DATABASE IF NOT EXISTS `account_manager_db`;
USE `account_manager_db`;

CREATE TABLE `profiles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
  `mobile` VARCHAR(15) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `transactions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `profile_id` INT(11) NOT NULL,
  `type` ENUM('credit','debit') COLLATE utf8mb4_general_ci NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) COLLATE utf8mb4_general_ci NOT NULL,
  `note` TEXT COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transaction_date` DATE NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_profile_transaction` 
  FOREIGN KEY (`profile_id`) 
  REFERENCES `profiles` (`id`) 
  ON DELETE CASCADE;

-- ########################################################
-- END: Content from account_management.sql
-- ########################################################

----------------------------------------------------------

-- ########################################################
-- START: Content from diesel_db.sql (Database: diesel_db)
-- ########################################################

CREATE DATABASE IF NOT EXISTS `diesel_db`;
USE `diesel_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `config` (
  `id` int(11) NOT NULL DEFAULT 1,
  `current_stock` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `config` (`id`, `current_stock`) VALUES
(1, 9300.00);

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

INSERT INTO `nozzle_readings` (`id`, `reading_date`, `n1_open`, `n1_close`, `n2_open`, `n2_close`, `notes`, `created_at`) VALUES
(4, '2025-10-14', 1000.00, 2000.00, 100.00, 200.00, 'd', '2025-10-14 15:35:19'),
(5, '2025-10-16', 10000.00, 10500.00, 500.00, 700.00, '', '2025-10-16 05:56:25');

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `add_date` date NOT NULL,
  `liters_added` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `stock` (`id`, `add_date`, `liters_added`, `notes`, `created_at`) VALUES
(3, '2025-10-14', 10000.00, 'hi', '2025-10-14 15:34:49'),
(4, '2025-10-16', 1100.00, '', '2025-10-16 05:55:47');

ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `nozzle_readings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reading_date` (`reading_date`);

ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `nozzle_readings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

-- ########################################################
-- END: Content from diesel_db.sql
-- ########################################################