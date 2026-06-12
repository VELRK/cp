-- Wishlist table for mobile API and admin.

CREATE TABLE IF NOT EXISTS `wishlists` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(100) NOT NULL,
  `property_id` INT(11) NOT NULL,
  `property_name` VARCHAR(255) NULL,
  `property_image` VARCHAR(500) NULL,
  `property_price` DECIMAL(15,2) NULL,
  `property_location` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_wishlist_user_property` (`user_id`, `property_id`),
  KEY `idx_wishlist_user` (`user_id`),
  KEY `idx_wishlist_property` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

