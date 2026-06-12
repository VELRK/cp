-- Create/upgrade properties table for mobile core API contract.

CREATE TABLE IF NOT EXISTS `properties` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `listing_type` ENUM('buy','rent') NOT NULL DEFAULT 'buy',
  `property_category` ENUM('residential','commercial') NOT NULL DEFAULT 'residential',
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `images_json` LONGTEXT NULL,
  `map_url` VARCHAR(500) NULL,
  `youtube_url` VARCHAR(500) NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_recommended` TINYINT(1) NOT NULL DEFAULT 0,
  `tags_best_rate_localities` TINYINT(1) NOT NULL DEFAULT 0,
  `tags_high_growth_localities` TINYINT(1) NOT NULL DEFAULT 0,
  `agent_id` VARCHAR(100) NULL,
  `added_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(30) NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_properties_listing_type` (`listing_type`),
  KEY `idx_properties_property_category` (`property_category`),
  KEY `idx_properties_is_featured` (`is_featured`),
  KEY `idx_properties_is_recommended` (`is_recommended`),
  KEY `idx_properties_agent_id` (`agent_id`),
  KEY `idx_properties_status` (`status`),
  KEY `idx_properties_added_time` (`added_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `properties`
  ADD COLUMN IF NOT EXISTS `listing_type` ENUM('buy','rent') NOT NULL DEFAULT 'buy' AFTER `id`,
  ADD COLUMN IF NOT EXISTS `property_category` ENUM('residential','commercial') NOT NULL DEFAULT 'residential' AFTER `listing_type`,
  ADD COLUMN IF NOT EXISTS `title` VARCHAR(255) NULL AFTER `property_category`,
  ADD COLUMN IF NOT EXISTS `images_json` LONGTEXT NULL AFTER `description`,
  ADD COLUMN IF NOT EXISTS `map_url` VARCHAR(500) NULL AFTER `images_json`,
  ADD COLUMN IF NOT EXISTS `youtube_url` VARCHAR(500) NULL AFTER `map_url`,
  ADD COLUMN IF NOT EXISTS `is_recommended` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_featured`,
  ADD COLUMN IF NOT EXISTS `tags_best_rate_localities` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_recommended`,
  ADD COLUMN IF NOT EXISTS `tags_high_growth_localities` TINYINT(1) NOT NULL DEFAULT 0 AFTER `tags_best_rate_localities`,
  ADD COLUMN IF NOT EXISTS `agent_id` VARCHAR(100) NULL AFTER `tags_high_growth_localities`,
  ADD COLUMN IF NOT EXISTS `added_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `agent_id`;

