-- TC_003 property type images, TC_006 site visits, TC_007 map/location columns

CREATE TABLE IF NOT EXISTS `nb_site_visits` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `property_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `scheduled_at` DATETIME NOT NULL,
  `notes` TEXT NULL,
  `status` ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_site_visits_property` (`property_id`),
  KEY `idx_site_visits_user` (`user_id`),
  KEY `idx_site_visits_status_scheduled` (`status`, `scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @db := DATABASE();

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'nb_property_types' AND COLUMN_NAME = 'image'),
    'SELECT 1',
    'ALTER TABLE `nb_property_types` ADD COLUMN `image` VARCHAR(500) NULL AFTER `slug`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'nb_properties' AND COLUMN_NAME = 'location'),
    'SELECT 1',
    'ALTER TABLE `nb_properties` ADD COLUMN `location` VARCHAR(500) NULL AFTER `locality`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'nb_properties' AND COLUMN_NAME = 'location_image'),
    'SELECT 1',
    'ALTER TABLE `nb_properties` ADD COLUMN `location_image` VARCHAR(512) NULL AFTER `location`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'nb_properties' AND COLUMN_NAME = 'map_url'),
    'SELECT 1',
    'ALTER TABLE `nb_properties` ADD COLUMN `map_url` VARCHAR(500) NULL AFTER `location_image`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
