-- Ensure users table exists and has agent verification fields.

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fullName` VARCHAR(255) NOT NULL,
  `username` VARCHAR(100) NULL,
  `email` VARCHAR(255) NULL,
  `password_hash` VARCHAR(255) NULL,
  `password` VARCHAR(255) NULL,
  `countryCode` VARCHAR(20) NULL DEFAULT '+91',
  `phoneNumber` VARCHAR(30) NULL,
  `state` VARCHAR(120) NULL,
  `city` VARCHAR(120) NULL,
  `pinCode` VARCHAR(20) NULL,
  `referralCode` VARCHAR(100) NULL,
  `profilePic` TEXT NULL,
  `loginType` VARCHAR(40) NULL DEFAULT 'manual',
  `user_type` ENUM('admin','customer','agent') NULL DEFAULT 'customer',
  `is_verified` TINYINT(1) NULL DEFAULT 0,
  `is_approved` TINYINT(1) NULL DEFAULT 1,
  `otp` VARCHAR(10) NULL,
  `otp_expires_at` DATETIME NULL,
  `fcmToken` TEXT NULL,
  `isactive` VARCHAR(20) NULL DEFAULT 'active',
  `createdAt` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastLoginAt` DATETIME NULL,
  `aadhar_no` VARCHAR(20) NULL,
  `aadhar_file` VARCHAR(300) NULL,
  `experience_years` TINYINT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  KEY `idx_users_username` (`username`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_user_type` (`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `aadhar_no` VARCHAR(20) NULL AFTER `lastLoginAt`,
  ADD COLUMN IF NOT EXISTS `aadhar_file` VARCHAR(300) NULL AFTER `aadhar_no`,
  ADD COLUMN IF NOT EXISTS `experience_years` TINYINT UNSIGNED NULL AFTER `aadhar_file`;

