-- Add agent/customer columns to nb_users when missing (MySQL 5.7 / MariaDB 10.x).
-- Safe to re-run: skips columns that already exist.

SET @db = DATABASE();

-- user_type after role
SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'nb_users' AND COLUMN_NAME = 'user_type';
SET @sql = IF(@c = 0,
  'ALTER TABLE `nb_users` ADD COLUMN `user_type` ENUM(\'agent\',\'customer\') NOT NULL DEFAULT \'customer\' AFTER `role`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- aadhar_no after city_id
SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'nb_users' AND COLUMN_NAME = 'aadhar_no';
SET @sql = IF(@c = 0,
  'ALTER TABLE `nb_users` ADD COLUMN `aadhar_no` VARCHAR(20) NULL AFTER `city_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- aadhar_file after aadhar_no
SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'nb_users' AND COLUMN_NAME = 'aadhar_file';
SET @sql = IF(@c = 0,
  'ALTER TABLE `nb_users` ADD COLUMN `aadhar_file` VARCHAR(300) NULL AFTER `aadhar_no`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- experience_years after aadhar_file
SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'nb_users' AND COLUMN_NAME = 'experience_years';
SET @sql = IF(@c = 0,
  'ALTER TABLE `nb_users` ADD COLUMN `experience_years` TINYINT UNSIGNED NULL AFTER `aadhar_file`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
