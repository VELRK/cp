-- FCM device token for push to individual devices (optional; topic `all_users` is still primary).

SET @db := DATABASE();
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'nb_users' AND COLUMN_NAME = 'fcm_token'
    ),
    'SELECT 1',
    'ALTER TABLE `nb_users` ADD COLUMN `fcm_token` VARCHAR(512) NULL'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
