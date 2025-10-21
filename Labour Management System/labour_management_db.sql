CREATE DATABASE IF NOT EXISTS labour_management_db;
USE labour_management_db;

CREATE TABLE labours (
  labour_id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  mobile_number varchar(15) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (labour_id),
  UNIQUE KEY mobile_number_UNIQUE (mobile_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE attendance (
  attendance_id int(11) NOT NULL AUTO_INCREMENT,
  labour_id int(11) NOT NULL,
  attendance_date date NOT NULL,
  status enum('P','A') NOT NULL,
  recorded_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (attendance_id),
  UNIQUE KEY unique_attendance (labour_id, attendance_date),
  KEY fk_labour_attendance_idx (labour_id),
  CONSTRAINT fk_labour_attendance FOREIGN KEY (labour_id) REFERENCES labours (labour_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE payments (
  payment_id int(11) NOT NULL AUTO_INCREMENT,
  labour_id int(11) NOT NULL,
  paid_amount decimal(10,2) NOT NULL,
  note text DEFAULT NULL,
  payment_date date NOT NULL,
  recorded_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (payment_id),
  KEY fk_labour_payment_idx (labour_id),
  CONSTRAINT fk_labour_payment FOREIGN KEY (labour_id) REFERENCES labours (labour_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
