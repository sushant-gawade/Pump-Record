CREATE DATABASE IF NOT EXISTS petrol_db;
USE petrol_db;

CREATE TABLE config (
  id int(11) NOT NULL DEFAULT 1,
  current_stock decimal(10,2) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE nozzle_readings (
  id int(11) NOT NULL AUTO_INCREMENT,
  reading_date date NOT NULL,
  n1_open decimal(10,2) NOT NULL,
  n1_close decimal(10,2) NOT NULL,
  n2_open decimal(10,2) NOT NULL,
  n2_close decimal(10,2) NOT NULL,
  notes text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY reading_date (reading_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE stock (
  id int(11) NOT NULL AUTO_INCREMENT,
  add_date date NOT NULL,
  liters_added decimal(10,2) NOT NULL,
  notes text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
