ALTER TABLE `nb_properties` ADD COLUMN IF NOT EXISTS `property_position` VARCHAR(32) NULL DEFAULT 'new' AFTER `property_type`;
ALTER TABLE `nb_properties` ADD COLUMN IF NOT EXISTS `plot_area_sqft` INT(11) NULL DEFAULT NULL;
