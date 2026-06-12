-- Live updates, housing news, and feedback APIs tables.

CREATE TABLE IF NOT EXISTS `live_updates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `image` VARCHAR(500) NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `liveTime` DATETIME NULL,
  `platform` ENUM('youtube','instagram','app') NOT NULL DEFAULT 'app',
  `url` VARCHAR(500) NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `housing_news` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `subtitle` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `multiImages` LONGTEXT NULL,
  `authorName` VARCHAR(150) NULL,
  `category` ENUM('market','tips','legal') NOT NULL DEFAULT 'market',
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `feedbacks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `userId` VARCHAR(100) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_feedback_userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

