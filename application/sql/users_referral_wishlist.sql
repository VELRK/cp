-- ============================================================
-- SQL SCRIPT FOR USERS, REFERRAL, AND WISHLIST MODULES
-- ============================================================
-- Generated for DVM Real Estate Management System
-- Database: demosite

-- ============================================================
-- TABLE 1: USERS
-- ============================================================
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `referralcode` varchar(255) DEFAULT NULL,
ADD UNIQUE KEY `unique_referralcode` (`referralcode`);

-- Create index for faster queries
ALTER TABLE `users`
ADD INDEX IF NOT EXISTS `idx_email` (`email`),
ADD INDEX IF NOT EXISTS `idx_phonenumber` (`phonenumber`),
ADD INDEX IF NOT EXISTS `idx_isactive` (`isactive`),
ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`);

-- ============================================================
-- TABLE 2: REFERRALS
-- ============================================================
CREATE TABLE IF NOT EXISTS `referrals` (
  `id` varchar(191) NOT NULL PRIMARY KEY,
  `referral_code` varchar(255) UNIQUE NOT NULL COMMENT 'Unique referral code',
  `referrer_id` varchar(191) NOT NULL COMMENT 'User ID of the referrer',
  `referred_id` varchar(191) NOT NULL COMMENT 'User ID of the referred user',
  `status` varchar(50) DEFAULT 'pending' COMMENT 'pending, completed, cancelled',
  `reward_points` int(11) DEFAULT 0,
  `reward_amount` decimal(10, 2) DEFAULT 0.00,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- Foreign Key Constraints
  CONSTRAINT `fk_referral_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_referral_referred` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

  -- Indexes
  INDEX `idx_referrer_id` (`referrer_id`),
  INDEX `idx_referred_id` (`referred_id`),
  INDEX `idx_referral_code` (`referral_code`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referral tracking and rewards';

-- ============================================================
-- TABLE 3: WISHLISTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `wishlists` (
  `id` varchar(191) NOT NULL PRIMARY KEY,
  `user_id` varchar(191) NOT NULL COMMENT 'Foreign key to users table',
  `property_id` varchar(191) NOT NULL COMMENT 'Foreign key to properties table',
  `property_name` varchar(255) DEFAULT NULL COMMENT 'Denormalized property name',
  `property_image` varchar(255) DEFAULT NULL COMMENT 'Denormalized property image',
  `property_price` decimal(15, 2) DEFAULT NULL COMMENT 'Denormalized property price',
  `property_location` varchar(255) DEFAULT NULL COMMENT 'Denormalized property location',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- Constraints
  UNIQUE KEY `unique_user_property` (`user_id`, `property_id`),
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wishlist_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

  -- Indexes
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_property_id` (`property_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User property wishlist';

-- ============================================================
-- INSERT SAMPLE DATA (Optional)
-- ============================================================

-- Sample Referral Records
INSERT INTO `referrals` (`id`, `referral_code`, `referrer_id`, `referred_id`, `status`, `reward_points`, `reward_amount`, `created_at`)
SELECT
  CONCAT('ref_', UUID()),
  CONCAT('REF', UPPER(SUBSTRING(MD5(RAND()), 1, 6))),
  (SELECT id FROM users LIMIT 1),
  (SELECT id FROM users LIMIT 1 OFFSET 1),
  'completed',
  100,
  500.00,
  NOW()
WHERE NOT EXISTS (SELECT 1 FROM referrals LIMIT 1)
LIMIT 1;

-- ============================================================
-- QUERIES FOR ANALYTICS
-- ============================================================

-- Get referral statistics
-- SELECT
--   COUNT(*) as total_referrals,
--   SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_referrals,
--   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_referrals,
--   SUM(reward_amount) as total_rewards_paid
-- FROM referrals;

-- Get top referrers
-- SELECT
--   u.fullname,
--   u.email,
--   COUNT(r.id) as referral_count,
--   SUM(r.reward_amount) as total_earned
-- FROM users u
-- LEFT JOIN referrals r ON u.id = r.referrer_id AND r.status = 'completed'
-- GROUP BY u.id
-- ORDER BY total_earned DESC
-- LIMIT 10;

-- Get wishlist statistics
-- SELECT
--   COUNT(DISTINCT user_id) as users_with_wishlist,
--   COUNT(*) as total_wishlist_items,
--   COUNT(DISTINCT property_id) as unique_properties_wishlisted
-- FROM wishlists;
