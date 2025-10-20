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

