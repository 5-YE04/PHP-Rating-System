-- Kiosk feedback survey schema
-- Run this fresh, or DROP the old `items`/`ratings` tables first if reusing the same database.
-- Import with: mysql -u youruser -p yourdatabase < schema.sql

DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS items;

CREATE TABLE IF NOT EXISTS responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id TINYINT NOT NULL,
    overall_stars TINYINT NOT NULL CHECK (overall_stars BETWEEN 1 AND 5),
    staff_stars TINYINT NOT NULL CHECK (staff_stars BETWEEN 1 AND 5),
    speed_stars TINYINT NOT NULL CHECK (speed_stars BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data so Analysis isn't empty on first load (safe to delete anytime)
INSERT INTO responses (device_id, overall_stars, staff_stars, speed_stars, created_at) VALUES
(1, 5, 5, 4, NOW() - INTERVAL 3 DAY),
(1, 4, 4, 4, NOW() - INTERVAL 2 DAY),
(2, 3, 4, 2, NOW() - INTERVAL 2 DAY),
(2, 5, 5, 5, NOW() - INTERVAL 1 DAY),
(3, 4, 3, 3, NOW() - INTERVAL 1 DAY),
(3, 2, 3, 2, NOW());
