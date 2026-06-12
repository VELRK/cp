-- Property types master table for admin CRUD and dynamic filters/forms/APIs.

CREATE TABLE IF NOT EXISTS `nb_property_types` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_nb_property_types_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `nb_property_types` (`name`, `slug`, `sort_order`, `is_active`) VALUES
  ('Apartment / Flat', 'apartment', 10, 1),
  ('Studio', 'studio', 20, 1),
  ('Independent House', 'house', 30, 1),
  ('Villa', 'villa', 40, 1),
  ('Independent Floor', 'independent_floor', 50, 1),
  ('Commercial', 'commercial', 60, 1),
  ('Office Space', 'office', 70, 1),
  ('Shop / Retail', 'retail', 80, 1),
  ('Warehouse / Godown', 'warehouse', 90, 1),
  ('Plot / Land', 'plot', 100, 1),
  ('Farmhouse', 'farmhouse', 110, 1),
  ('PG', 'pg', 120, 1),
  ('Shared Flat', 'shared_flat', 130, 1),
  ('Serviced Apartment', 'serviced_apartment', 140, 1),
  ('Others', 'others', 150, 1);

