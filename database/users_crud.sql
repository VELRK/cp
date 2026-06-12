-- ============================================================
-- DVM Admin Panel - Users CRUD SQL Script
-- Generated for MySQL / MariaDB (XAMPP)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Table: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`              VARCHAR(30)     NOT NULL,                        -- uniqid('user_') e.g. user_64abc123
    `fullname`        VARCHAR(150)    DEFAULT NULL,
    `email`           VARCHAR(191)    DEFAULT NULL,
    `countrycode`     VARCHAR(10)     NOT NULL DEFAULT '+91',
    `phonenumber`     VARCHAR(15)     DEFAULT NULL,
    `city`            VARCHAR(100)    DEFAULT NULL,
    `state`           VARCHAR(100)    DEFAULT NULL,
    `pincode`         VARCHAR(10)     DEFAULT NULL,
    `logintype`       ENUM('manual','google','facebook') NOT NULL DEFAULT 'manual',
    `profilepic`      VARCHAR(255)    DEFAULT NULL,
    `referralcode`    VARCHAR(20)     DEFAULT NULL,
    `password`        VARCHAR(255)    DEFAULT NULL,                    -- bcrypt hash
    `otp`             VARCHAR(10)     DEFAULT NULL,
    `otp_expires_at`  DATETIME        DEFAULT NULL,
    `is_verified`     TINYINT(1)      NOT NULL DEFAULT 0,
    `isactive`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email`       (`email`),
    UNIQUE KEY `uq_users_phone`       (`phonenumber`, `countrycode`),
    UNIQUE KEY `uq_users_referralcode`(`referralcode`),
    KEY `idx_users_isactive`          (`isactive`),
    KEY `idx_users_created_at`        (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- Table: referrals
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `referrals` (
    `id`            INT             NOT NULL AUTO_INCREMENT,
    `referrer_id`   VARCHAR(30)     NOT NULL,                         -- user who shared the code
    `referred_id`   VARCHAR(30)     NOT NULL,                         -- user who signed up using the code
    `referral_code` VARCHAR(20)     NOT NULL,
    `status`        ENUM('pending','completed','expired','cancelled') NOT NULL DEFAULT 'pending',
    `reward_points` INT             NOT NULL DEFAULT 0,
    `reward_amount` DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_referrals_pair`    (`referrer_id`, `referred_id`),
    KEY `idx_referrals_referrer`      (`referrer_id`),
    KEY `idx_referrals_referred`      (`referred_id`),
    KEY `idx_referrals_code`          (`referral_code`),
    KEY `idx_referrals_status`        (`status`),

    CONSTRAINT `fk_referrals_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_referrals_referred` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- Table: wishlists
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wishlists` (
    `id`                INT             NOT NULL AUTO_INCREMENT,
    `user_id`           VARCHAR(30)     NOT NULL,
    `property_id`       INT             NOT NULL,
    `property_name`     VARCHAR(255)    DEFAULT NULL,                  -- snapshot at time of save
    `property_image`    VARCHAR(255)    DEFAULT NULL,
    `property_price`    DECIMAL(15,2)   DEFAULT NULL,
    `property_location` VARCHAR(255)    DEFAULT NULL,
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_wishlists_pair`    (`user_id`, `property_id`),
    KEY `idx_wishlists_user`          (`user_id`),
    KEY `idx_wishlists_property`      (`property_id`),

    CONSTRAINT `fk_wishlists_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================
-- Sample Data (optional — remove if not needed)
-- ============================================================

INSERT INTO `users`
    (`id`, `fullname`, `email`, `countrycode`, `phonenumber`, `city`, `state`, `pincode`, `logintype`, `referralcode`, `isactive`, `is_verified`, `created_at`, `updated_at`)
VALUES
    ('user_001', 'Rahul Sharma',  'rahul@example.com',  '+91', '9876543210', 'Mumbai',    'Maharashtra', '400001', 'manual', 'REF001A2B', 'active',   1, NOW(), NOW()),
    ('user_002', 'Priya Patel',   'priya@example.com',  '+91', '9876543211', 'Ahmedabad', 'Gujarat',     '380001', 'google', 'REF002C3D', 'active',   1, NOW(), NOW()),
    ('user_003', 'Amit Singh',    'amit@example.com',   '+91', '9876543212', 'Delhi',     'Delhi',       '110001', 'manual', 'REF003E4F', 'inactive', 0, NOW(), NOW());
