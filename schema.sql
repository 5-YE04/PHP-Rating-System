-- Feedback kiosk schema (devices + responses)
-- FRESH INSTALL — if you already have data you want to keep, use migrate_add_devices.sql instead.
-- Import with: mysql -u youruser -p yourdatabase < schema.sql

DROP TABLE IF EXISTS responses;
DROP TABLE IF EXISTS devices;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS ratings;

CREATE TABLE devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150) DEFAULT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    overall_stars TINYINT NOT NULL CHECK (overall_stars BETWEEN 1 AND 5),
    staff_stars TINYINT NOT NULL CHECK (staff_stars BETWEEN 1 AND 5),
    speed_stars TINYINT NOT NULL CHECK (speed_stars BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample devices (rename/add more anytime via devices.php)
INSERT INTO devices (id, name, location) VALUES
(1, 'Station 1', 'Front counter'),
(2, 'Station 2', 'Side entrance'),
(3, 'Station 3', 'Checkout');

-- Sample responses so Analysis isn't empty on first load (safe to delete anytime)
INSERT INTO responses (device_id, overall_stars, staff_stars, speed_stars, created_at) VALUES
(1, 5, 5, 4, NOW() - INTERVAL 3 DAY),
(1, 4, 4, 4, NOW() - INTERVAL 2 DAY),
(2, 3, 4, 2, NOW() - INTERVAL 2 DAY),
(2, 5, 5, 5, NOW() - INTERVAL 1 DAY),
(3, 4, 3, 3, NOW() - INTERVAL 1 DAY),
(3, 2, 3, 2, NOW());
