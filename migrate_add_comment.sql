-- Migration: adds an optional `comment` column to the responses table.
-- Safe to run on an existing install — does not touch existing data.
-- Import with: mysql -u youruser -p yourdatabase < migrate_add_comment.sql

ALTER TABLE responses ADD COLUMN comment TEXT DEFAULT NULL AFTER speed_stars;
