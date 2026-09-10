-- Mail SMTP settings and per-operation email templates (admin Settings page).
CREATE TABLE IF NOT EXISTS `nb_mail_settings` (
  `setting_key` VARCHAR(64) NOT NULL,
  `setting_value` TEXT NULL DEFAULT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `nb_mail_templates` (
  `template_key` VARCHAR(80) NOT NULL,
  `subject` VARCHAR(255) NOT NULL DEFAULT '',
  `heading` VARCHAR(255) NOT NULL DEFAULT '',
  `body` TEXT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`template_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
