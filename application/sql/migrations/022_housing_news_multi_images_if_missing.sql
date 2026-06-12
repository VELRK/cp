-- Ensure housing_news has multiImages column for admin multiple uploads.
SET @db := DATABASE();
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'housing_news' AND COLUMN_NAME = 'multiImages'
    ),
    'SELECT 1',
    'ALTER TABLE `housing_news` ADD COLUMN `multiImages` LONGTEXT NULL AFTER `description`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
