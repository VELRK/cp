-- Notification audience + per-device FCM tokens (web/app) for Firebase project coimbatore-property.

ALTER TABLE `notifications`
  ADD COLUMN `target_audience` VARCHAR(32) NOT NULL DEFAULT 'all' AFTER `status`;

CREATE TABLE IF NOT EXISTS `nb_fcm_tokens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `token` VARCHAR(512) NOT NULL,
  `platform` VARCHAR(16) NOT NULL DEFAULT 'web',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_nb_fcm_token` (`token`(191)),
  KEY `idx_nb_fcm_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
