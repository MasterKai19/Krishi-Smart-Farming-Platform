CREATE TABLE IF NOT EXISTS lands (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 farmer_id INT UNSIGNED NOT NULL,
 name VARCHAR(100) NOT NULL,
 thana_code CHAR(8) NOT NULL,
 address VARCHAR(255) NOT NULL,
 area_sqft DECIMAL(16,4) NOT NULL,
 display_unit ENUM('katha','decimal','acre','hectare','sqft') NOT NULL DEFAULT 'katha',
 tenure ENUM('owned','leased','shared') NOT NULL DEFAULT 'owned',
 soil_type VARCHAR(100) NULL,
 irrigation VARCHAR(100) NULL,
 notes VARCHAR(1000) NULL,
 archived TINYINT NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(farmer_id) REFERENCES farmers(id),
 FOREIGN KEY(thana_code) REFERENCES thanas(code),
 CHECK(area_sqft>0), CHECK(archived IN (0,1)),
 INDEX land_owner(farmer_id,archived), INDEX land_location(thana_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS crop_cycles (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 land_id INT UNSIGNED NOT NULL,
 crop VARCHAR(100) NOT NULL,
 variety VARCHAR(100) NULL,
 season VARCHAR(100) NOT NULL,
 sowing_date DATE NOT NULL,
 expected_harvest DATE NULL,
 budget DECIMAL(12,2) NOT NULL DEFAULT 0,
 status ENUM('planned','growing','harvested') NOT NULL DEFAULT 'growing',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(land_id) REFERENCES lands(id),
 CHECK(budget>=0), CHECK(expected_harvest IS NULL OR expected_harvest>=sowing_date),
 INDEX cycle_land(land_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS land_expenses (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 cycle_id INT UNSIGNED NOT NULL,
 category ENUM('seeds','fertilizer','labour','irrigation','pesticides','machinery','rent','other') NOT NULL,
 item VARCHAR(150) NOT NULL,
 quantity DECIMAL(10,3) NOT NULL,
 unit VARCHAR(30) NOT NULL,
 unit_cost DECIMAL(12,2) NOT NULL,
 amount DECIMAL(16,2) GENERATED ALWAYS AS (ROUND(quantity*unit_cost,2)) STORED,
 expense_date DATE NOT NULL,
 note VARCHAR(500) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(cycle_id) REFERENCES crop_cycles(id),
 CHECK(quantity>0), CHECK(unit_cost>=0),
 INDEX expense_cycle(cycle_id,expense_date,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS scheme_application_land (
 application_id INT UNSIGNED PRIMARY KEY,
 cycle_id INT UNSIGNED NOT NULL,
 district_code CHAR(4) NOT NULL,
 snapshot_json LONGTEXT NOT NULL,
 FOREIGN KEY(application_id) REFERENCES scheme_applications(id) ON DELETE CASCADE,
 FOREIGN KEY(cycle_id) REFERENCES crop_cycles(id),
 FOREIGN KEY(district_code) REFERENCES districts(code),
 CHECK(JSON_VALID(snapshot_json)),
 INDEX application_land_district(district_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE OR REPLACE SQL SECURITY INVOKER VIEW v_land_costs AS
SELECT c.id AS cycle_id,c.land_id,COUNT(e.id) AS entry_count,
 COALESCE(SUM(e.amount),0) AS total_cost,
 COALESCE(SUM(CASE WHEN e.category='seeds' THEN e.amount ELSE 0 END),0) AS seed_cost,
 COALESCE(SUM(CASE WHEN e.category='fertilizer' THEN e.amount ELSE 0 END),0) AS fertilizer_cost,
 COALESCE(SUM(CASE WHEN e.category='labour' THEN e.amount ELSE 0 END),0) AS labour_cost
FROM crop_cycles c LEFT JOIN land_expenses e ON e.cycle_id=c.id
GROUP BY c.id,c.land_id;
