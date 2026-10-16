CREATE TABLE IF NOT EXISTS divisions (
 code CHAR(2) PRIMARY KEY,
 name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS districts (
 code CHAR(4) PRIMARY KEY,
 division_code CHAR(2) NOT NULL,
 name VARCHAR(100) NOT NULL,
 CONSTRAINT fk_district_division FOREIGN KEY (division_code) REFERENCES divisions(code),
 INDEX (division_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS thanas (
 code CHAR(8) PRIMARY KEY,
 district_code CHAR(4) NOT NULL,
 name VARCHAR(100) NOT NULL,
 CONSTRAINT fk_thana_district FOREIGN KEY (district_code) REFERENCES districts(code),
 INDEX (district_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS farmers (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 phone VARCHAR(30) NOT NULL,
 village VARCHAR(150) NOT NULL,
 nid_number VARCHAR(17) NULL,
 thana_code CHAR(8) NULL,
 detailed_address VARCHAR(255) NULL,
 land_katha DECIMAL(9,2) NULL,
 status ENUM('pending','active','inactive') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT uq_farmer_nid UNIQUE (nid_number),
 CONSTRAINT fk_farmer_thana FOREIGN KEY (thana_code) REFERENCES thanas(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS officers (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 phone VARCHAR(30) NOT NULL,
 office VARCHAR(150) NOT NULL,
 specialization VARCHAR(150) NOT NULL,
 thana_code CHAR(8) NULL,
 detailed_address VARCHAR(255) NULL,
 status ENUM('pending','active','inactive') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_officer_thana FOREIGN KEY (thana_code) REFERENCES thanas(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 phone VARCHAR(30) NOT NULL,
 admin_scope VARCHAR(100) NOT NULL DEFAULT 'account_review',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS appointments (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 farmer_id INT UNSIGNED NOT NULL,
 officer_id INT UNSIGNED NOT NULL,
 crop VARCHAR(100) NOT NULL,
 problem TEXT NOT NULL,
 appointment_date DATE NOT NULL,
 appointment_time TIME NOT NULL,
 status ENUM('pending','accepted','declined','cancelled','completed') NOT NULL DEFAULT 'pending',
 officer_note TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (farmer_id) REFERENCES farmers(id),
 FOREIGN KEY (officer_id) REFERENCES officers(id),
 INDEX officer_schedule (officer_id, appointment_date, status, appointment_time),
 INDEX officer_recent (officer_id,created_at,id),
 INDEX farmer_requests (farmer_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 appointment_id INT UNSIGNED NOT NULL,
 message VARCHAR(500) NOT NULL,
 is_read TINYINT NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (appointment_id) REFERENCES appointments(id),
 CONSTRAINT notification_read_flag CHECK (is_read IN (0,1)),
 INDEX appointment_unread (appointment_id,is_read,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS appointment_photos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 appointment_id INT UNSIGNED NOT NULL UNIQUE,
 storage_name VARCHAR(52) NOT NULL UNIQUE,
 mime_type VARCHAR(30) NOT NULL,
 byte_size INT UNSIGNED NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_photo_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS schemes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(64) NULL UNIQUE,
 title VARCHAR(160) NOT NULL,
 benefit VARCHAR(255) NOT NULL,
 description TEXT NOT NULL,
 max_land_katha DECIMAL(9,2) NULL,
 district_code CHAR(4) NULL,
 source_url VARCHAR(500) NULL,
 status ENUM('draft','active','closed') NOT NULL DEFAULT 'draft',
 is_example TINYINT(1) NOT NULL DEFAULT 0,
 created_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (district_code) REFERENCES districts(code),
 FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL,
 INDEX scheme_scope (status,district_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS scheme_applications (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 scheme_id INT UNSIGNED NOT NULL,
 farmer_id INT UNSIGNED NOT NULL,
 land_katha_snapshot DECIMAL(9,2) NOT NULL,
 farmer_note VARCHAR(500) NULL,
 status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
 officer_id INT UNSIGNED NULL,
 officer_note VARCHAR(500) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 reviewed_at TIMESTAMP NULL,
 UNIQUE KEY one_application (scheme_id,farmer_id),
 FOREIGN KEY (scheme_id) REFERENCES schemes(id),
 FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE,
 FOREIGN KEY (officer_id) REFERENCES officers(id) ON DELETE SET NULL,
 INDEX application_queue (status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
