-- Home banner flag + dedicated hero image per listing (admin only)
ALTER TABLE `nb_properties`
  ADD COLUMN `is_home_banner` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_premium`;
ALTER TABLE `nb_properties`
  ADD COLUMN `home_banner_image` VARCHAR(512) DEFAULT NULL AFTER `is_home_banner`;
