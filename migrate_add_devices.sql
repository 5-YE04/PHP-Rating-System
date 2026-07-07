-- Migration: adds a `devices` table to an EXISTING install and links it
-- to your current `responses` table, without deleting any data you already have.
-- Import with: mysql -u youruser -p yourdatabase < migrate_add_devices.sql
--
-- Safe to run even if `devices` already exists (uses IF NOT EXISTS / checks).

CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150) DEFAULT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create a device row for every distinct device_id already used in responses,
-- so the foreign key below won't fail. Skips any id that's already in devices.
INSERT INTO devices (id, name)
SELECT DISTINCT r.device_id, CONCAT('Station ', r.device_id)
FROM responses r
LEFT JOIN devices d ON d.id = r.device_id
WHERE d.id IS NULL;

-- Add the foreign key linking responses.device_id -> devices.id
-- (skip this statement if it errors saying the constraint already exists)
ALTER TABLE responses
    ADD CONSTRAINT fk_responses_device
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE;
