-- Add optional image + submitter name to feedbacks (Next.js /api/feedback + web form).
-- Safe to run multiple times.

SET NAMES utf8mb4;
SET @dbname = DATABASE();

SET @exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'feedbacks' AND COLUMN_NAME = 'image'
);
SET @sql = IF(@exists = 0,
  'ALTER TABLE feedbacks ADD COLUMN image VARCHAR(500) NULL DEFAULT NULL AFTER description',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'feedbacks' AND COLUMN_NAME = 'name'
);
SET @sql = IF(@exists = 0,
  'ALTER TABLE feedbacks ADD COLUMN name VARCHAR(150) NULL DEFAULT NULL AFTER image',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
