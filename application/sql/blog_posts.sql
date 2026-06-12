-- Blog Posts Table
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `description` text,
  `content` longtext,
  `featured_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `published_at` timestamp NULL DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `meta_keywords` text,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_published_at` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data
INSERT INTO `blog_posts` (`title`, `slug`, `description`, `content`, `featured_image`, `status`, `published_at`, `author_id`, `meta_title`, `meta_description`) VALUES
('Welcome to Our Real Estate Blog', 'welcome-to-our-real-estate-blog', 'Discover the latest trends and insights in real estate', 'This is our first blog post where we share valuable insights about the real estate market...', 'blog/welcome-post.jpg', 'published', NOW(), 1, 'Welcome to Our Real Estate Blog', 'Discover the latest trends and insights in real estate'),
('Tips for First-Time Home Buyers', 'tips-for-first-time-home-buyers', 'Essential tips for first-time home buyers', 'Buying your first home can be overwhelming. Here are some essential tips to help you through the process...', 'blog/first-time-buyers.jpg', 'published', NOW(), 1, 'Tips for First-Time Home Buyers', 'Essential tips for first-time home buyers'),
('Market Trends 2024', 'market-trends-2024', 'Latest real estate market trends for 2024', 'The real estate market is constantly evolving. Here are the key trends to watch in 2024...', 'blog/market-trends-2024.jpg', 'published', NOW(), 1, 'Market Trends 2024', 'Latest real estate market trends for 2024');
