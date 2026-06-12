-- Add video_url to nb_properties only if missing (safe to run after 005 or on fresh DBs).
-- Run in phpMyAdmin / MySQL client: source this file or paste and execute.
SET NAMES utf8mb4;
SET @dbname = DATABASE();
SET @exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'nb_properties' AND COLUMN_NAME = 'video_url'
);
SET @sql = IF(@exists = 0,
  'ALTER TABLE nb_properties ADD COLUMN video_url VARCHAR(512) NULL DEFAULT NULL COMMENT ''YouTube/Vimeo URL'' AFTER images',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
