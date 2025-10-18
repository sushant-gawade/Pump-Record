-- --------------------------------------------------------
-- SQL File to Create Database and Tables for Account Management System
-- --------------------------------------------------------

-- 1. CREATE AND SELECT THE DATABASE
CREATE DATABASE IF NOT EXISTS `account_manager_db`;
USE `account_manager_db`;

-- --------------------------------------------------------

-- 2. Create the Profiles Table
-- This table stores information about the individuals being tracked.

CREATE TABLE `profiles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
  `mobile` VARCHAR(15) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- 3. Create the Transactions Table
-- This table stores all financial movements (Credit/Debit).
-- The 'type' column is set to NOT NULL to enforce data entry.

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

-- --------------------------------------------------------

-- 4. Add Foreign Key Constraint to Transactions Table
-- This ensures data integrity and automatically deletes associated transactions 
-- when a parent profile is deleted (ON DELETE CASCADE).

ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_profile_transaction` 
  FOREIGN KEY (`profile_id`) 
  REFERENCES `profiles` (`id`) 
  ON DELETE CASCADE;

-- --------------------------------------------------------