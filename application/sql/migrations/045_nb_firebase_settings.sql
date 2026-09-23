-- Firebase web + VAPID + service account stored in admin Settings (not .env).
CREATE TABLE IF NOT EXISTS `nb_firebase_settings` (
    `setting_key` VARCHAR(64) NOT NULL,
    `setting_value` MEDIUMTEXT NULL DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
