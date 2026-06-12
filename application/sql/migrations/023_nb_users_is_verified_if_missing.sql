-- Ensure nb_users has is_verified column (admin can toggle 0/1).
SET @db := DATABASE();
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'nb_users' AND COLUMN_NAME = 'is_verified'
    ),
    'SELECT 1',
    'ALTER TABLE `nb_users` ADD COLUMN `is_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
