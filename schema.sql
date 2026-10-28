-- Reconstructed local demonstration schema from application queries; no original data.
CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(191) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL, name VARCHAR(191) NOT NULL DEFAULT '', email VARCHAR(191) NOT NULL DEFAULT '',
 role ENUM('student','admin','super_admin') NOT NULL DEFAULT 'student', points INT NOT NULL DEFAULT 0,
 reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, role_expiry DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE qr_codes (
 id INT AUTO_INCREMENT PRIMARY KEY, unique_text VARCHAR(191) NOT NULL UNIQUE,
 booth_name VARCHAR(191) NOT NULL, description TEXT, points INT NOT NULL DEFAULT 0,
 expiration_time DATETIME NOT NULL, created_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE scan_history (
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, qr_code_id INT NOT NULL,
 points_added INT NOT NULL DEFAULT 0, description TEXT, scan_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE claim_history (
 id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, item_name VARCHAR(191) NOT NULL,
 points INT NOT NULL, processed_by INT NULL, claim_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
