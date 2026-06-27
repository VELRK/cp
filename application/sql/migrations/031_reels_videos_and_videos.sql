-- YouTube reels and property videos (admin panel + api/mobile/reels + api/mobile/videos)

CREATE TABLE IF NOT EXISTS `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `videoUrl` text NOT NULL,
  `thumbnail` text NULL,
  `title` varchar(255) NULL,
  `index_no` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_videos_status_index` (`status`, `index_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `reels_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `videoUrl` text NOT NULL,
  `thumbnail` text NULL,
  `title` varchar(255) NULL,
  `index_no` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reels_videos_status_index` (`status`, `index_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
