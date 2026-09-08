-- Full KYC audit trail: submit, resubmit, approve, reject, comment edits.
CREATE TABLE IF NOT EXISTS `nb_kyc_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `actor_id` INT(11) NULL DEFAULT NULL,
  `actor_name` VARCHAR(150) NULL DEFAULT NULL,
  `actor_role` VARCHAR(32) NULL DEFAULT NULL,
  `action` VARCHAR(40) NOT NULL,
  `from_status` VARCHAR(20) NULL DEFAULT NULL,
  `to_status` VARCHAR(20) NOT NULL,
  `comment` TEXT NULL DEFAULT NULL,
  `meta` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nb_kyc_history_user` (`user_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
