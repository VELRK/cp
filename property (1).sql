-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2026 at 05:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `property`
--

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `imageUrl` text NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  `createdAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `shortdescription` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `authorname` varchar(120) DEFAULT NULL,
  `coverImageUrl` text DEFAULT NULL,
  `imageUrls` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `categoryID` varchar(80) DEFAULT NULL,
  `categoryName` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `cityName` varchar(120) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(40) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(30) DEFAULT 'new',
  `userDetails` longtext DEFAULT NULL,
  `ip_address` varchar(60) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `contactCount` int(11) DEFAULT 0,
  `status` varchar(30) DEFAULT 'active',
  `source` varchar(80) DEFAULT NULL,
  `lastContactDate` datetime DEFAULT NULL,
  `lastEnquiryProperty` varchar(255) DEFAULT NULL,
  `lastEnquiryPropertyId` varchar(80) DEFAULT NULL,
  `ip_address` varchar(60) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `id` int(11) NOT NULL,
  `propertyId` varchar(80) DEFAULT NULL,
  `propertyName` varchar(255) DEFAULT NULL,
  `propertyPrice` varchar(120) DEFAULT NULL,
  `coverImageUrl` text DEFAULT NULL,
  `userId` varchar(80) DEFAULT NULL,
  `userName` varchar(255) DEFAULT NULL,
  `userEmail` varchar(255) DEFAULT NULL,
  `userPhone` varchar(40) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `enquiryType` varchar(80) DEFAULT 'property_enquiry',
  `status` varchar(30) DEFAULT 'new',
  `message` text DEFAULT NULL,
  `ipAddress` varchar(60) DEFAULT NULL,
  `userAgent` text DEFAULT NULL,
  `userDetails` longtext DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `city_id` int(11) DEFAULT NULL,
  `locationName` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nb_amenities`
--

CREATE TABLE `nb_amenities` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nb_cities`
--

CREATE TABLE `nb_cities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nb_enquiries`
--

CREATE TABLE `nb_enquiries` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(200) NOT NULL,
  `status` enum('new','read','responded','closed') DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nb_localities`
--

CREATE TABLE `nb_localities` (
  `id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nb_notifications`
--

CREATE TABLE `nb_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(300) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nb_properties`
--

CREATE TABLE `nb_properties` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `title` varchar(300) NOT NULL,
  `slug` varchar(255) DEFAULT NULL COMMENT 'URL segment for /property/{slug}',
  `description` text DEFAULT NULL,
  `property_type` varchar(64) NOT NULL DEFAULT 'apartment',
  `listing_type` enum('rent','sale') NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `bedrooms` tinyint(3) UNSIGNED DEFAULT NULL,
  `bathrooms` tinyint(3) UNSIGNED DEFAULT NULL,
  `area_sqft` int(11) DEFAULT NULL,
  `address` text NOT NULL,
  `locality` varchar(200) NOT NULL,
  `city_id` int(11) NOT NULL,
  `latitude` decimal(11,8) DEFAULT NULL COMMENT 'WGS84',
  `longitude` decimal(12,8) DEFAULT NULL COMMENT 'WGS84',
  `google_place_id` varchar(255) DEFAULT NULL COMMENT 'Google Places place_id',
  `is_price_negotiable` tinyint(1) NOT NULL DEFAULT 0,
  `rate_per_sqft` decimal(12,2) DEFAULT NULL COMMENT 'Derived or entered',
  `available_from` date DEFAULT NULL,
  `plot_length_ft` decimal(10,2) DEFAULT NULL COMMENT 'Plot length (feet)',
  `plot_width_ft` decimal(10,2) DEFAULT NULL COMMENT 'Plot width (feet)',
  `has_boundary_wall` tinyint(1) DEFAULT NULL COMMENT '1=yes 0=no NULL=n/a',
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenities`)),
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `video_url` varchar(512) DEFAULT NULL COMMENT 'YouTube/Vimeo URL',
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nb_users`
--

CREATE TABLE `nb_users` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('owner','tenant','admin') NOT NULL DEFAULT 'tenant',
  `user_type` enum('agent','customer') NOT NULL DEFAULT 'customer',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `city_id` int(11) DEFAULT NULL,
  `aadhar_no` varchar(20) DEFAULT NULL,
  `aadhar_file` varchar(300) DEFAULT NULL,
  `experience_years` tinyint(3) UNSIGNED DEFAULT NULL,
  `profile_pic` varchar(300) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `api_token` varchar(64) DEFAULT NULL,
  `api_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `propertyName` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `propertyPriceRange` decimal(14,2) DEFAULT NULL,
  `propertyPriceRangeText` varchar(120) DEFAULT NULL,
  `propertyRange` varchar(120) DEFAULT NULL,
  `desc` text DEFAULT NULL,
  `propertiesMainImage` text DEFAULT NULL,
  `projectThumbnailImage` text DEFAULT NULL,
  `projectVideoUrl` text DEFAULT NULL,
  `propertySliderImages` longtext DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `beds` int(11) DEFAULT NULL,
  `baths` int(11) DEFAULT NULL,
  `sqft` int(11) DEFAULT NULL,
  `index` int(11) DEFAULT NULL,
  `orderValue` int(11) DEFAULT NULL,
  `is_recommended` tinyint(1) DEFAULT 0,
  `status` varchar(20) DEFAULT 'active',
  `createdAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reels_videos`
--

CREATE TABLE `reels_videos` (
  `id` int(11) NOT NULL,
  `videoUrl` text NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `index_no` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'active',
  `createdAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `users`
-- (See below for the actual view)
--
CREATE TABLE `users` (
`id` int(11)
,`fullName` varchar(255)
,`username` varchar(100)
,`email` varchar(255)
,`password_hash` varchar(255)
,`password` varchar(255)
,`countryCode` varchar(20)
,`phoneNumber` varchar(30)
,`state` varchar(120)
,`city` varchar(120)
,`pinCode` varchar(20)
,`referralCode` varchar(100)
,`profilePic` text
,`loginType` varchar(40)
,`user_type` enum('admin','customer','agent')
,`is_verified` tinyint(1)
,`is_approved` tinyint(1)
,`otp` varchar(10)
,`otp_expires_at` datetime
,`fcmToken` text
,`isactive` varchar(20)
,`createdAt` datetime
,`updatedAt` datetime
,`lastLoginAt` datetime
);

-- --------------------------------------------------------

--
-- Table structure for table `users_data`
--

CREATE TABLE `users_data` (
  `id` int(11) NOT NULL,
  `fullName` varchar(255) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `countryCode` varchar(20) DEFAULT '+91',
  `phoneNumber` varchar(30) DEFAULT NULL,
  `state` varchar(120) DEFAULT '',
  `city` varchar(120) DEFAULT '',
  `pinCode` varchar(20) DEFAULT '',
  `referralCode` varchar(100) DEFAULT '',
  `profilePic` text DEFAULT NULL,
  `loginType` varchar(40) DEFAULT 'manual',
  `user_type` enum('admin','customer','agent') DEFAULT 'customer',
  `is_verified` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 1,
  `otp` varchar(10) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `fcmToken` text DEFAULT NULL,
  `isactive` varchar(20) DEFAULT 'active',
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `lastLoginAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `videoUrl` text NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `index_no` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'active',
  `createdAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video_play_events`
--

CREATE TABLE `video_play_events` (
  `id` int(11) NOT NULL,
  `videoId` varchar(80) NOT NULL,
  `videoUrl` text DEFAULT NULL,
  `playTime` datetime DEFAULT NULL,
  `userId` varchar(80) DEFAULT NULL,
  `ipAddress` varchar(60) DEFAULT NULL,
  `userAgent` text DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `ts` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `users`
--
DROP TABLE IF EXISTS `users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `users`  AS SELECT `users_data`.`id` AS `id`, `users_data`.`fullName` AS `fullName`, `users_data`.`username` AS `username`, `users_data`.`email` AS `email`, `users_data`.`password_hash` AS `password_hash`, `users_data`.`password` AS `password`, `users_data`.`countryCode` AS `countryCode`, `users_data`.`phoneNumber` AS `phoneNumber`, `users_data`.`state` AS `state`, `users_data`.`city` AS `city`, `users_data`.`pinCode` AS `pinCode`, `users_data`.`referralCode` AS `referralCode`, `users_data`.`profilePic` AS `profilePic`, `users_data`.`loginType` AS `loginType`, `users_data`.`user_type` AS `user_type`, `users_data`.`is_verified` AS `is_verified`, `users_data`.`is_approved` AS `is_approved`, `users_data`.`otp` AS `otp`, `users_data`.`otp_expires_at` AS `otp_expires_at`, `users_data`.`fcmToken` AS `fcmToken`, `users_data`.`isactive` AS `isactive`, `users_data`.`createdAt` AS `createdAt`, `users_data`.`updatedAt` AS `updatedAt`, `users_data`.`lastLoginAt` AS `lastLoginAt` FROM `users_data` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nb_amenities`
--
ALTER TABLE `nb_amenities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_nb_amenities_slug` (`slug`),
  ADD UNIQUE KEY `uq_nb_amenities_name` (`name`);

--
-- Indexes for table `nb_cities`
--
ALTER TABLE `nb_cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nb_enquiries`
--
ALTER TABLE `nb_enquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `nb_localities`
--
ALTER TABLE `nb_localities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `city_id` (`city_id`);

--
-- Indexes for table `nb_notifications`
--
ALTER TABLE `nb_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `nb_properties`
--
ALTER TABLE `nb_properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_nb_properties_slug` (`slug`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `city_id` (`city_id`);

--
-- Indexes for table `nb_users`
--
ALTER TABLE `nb_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uq_nb_users_api_token` (`api_token`),
  ADD KEY `city_id` (`city_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reels_videos`
--
ALTER TABLE `reels_videos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_data`
--
ALTER TABLE `users_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD UNIQUE KEY `uq_users_phoneNumber` (`phoneNumber`),
  ADD UNIQUE KEY `uq_users_username` (`username`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `video_play_events`
--
ALTER TABLE `video_play_events`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nb_amenities`
--
ALTER TABLE `nb_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nb_cities`
--
ALTER TABLE `nb_cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nb_enquiries`
--
ALTER TABLE `nb_enquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nb_localities`
--
ALTER TABLE `nb_localities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nb_notifications`
--
ALTER TABLE `nb_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nb_properties`
--
ALTER TABLE `nb_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nb_users`
--
ALTER TABLE `nb_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reels_videos`
--
ALTER TABLE `reels_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_data`
--
ALTER TABLE `users_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `video_play_events`
--
ALTER TABLE `video_play_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `nb_enquiries`
--
ALTER TABLE `nb_enquiries`
  ADD CONSTRAINT `nb_enquiries_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `nb_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nb_enquiries_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `nb_properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nb_localities`
--
ALTER TABLE `nb_localities`
  ADD CONSTRAINT `nb_localities_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `nb_cities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nb_notifications`
--
ALTER TABLE `nb_notifications`
  ADD CONSTRAINT `nb_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `nb_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nb_properties`
--
ALTER TABLE `nb_properties`
  ADD CONSTRAINT `nb_properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `nb_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nb_properties_ibfk_2` FOREIGN KEY (`city_id`) REFERENCES `nb_cities` (`id`);

--
-- Constraints for table `nb_users`
--
ALTER TABLE `nb_users`
  ADD CONSTRAINT `nb_users_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `nb_cities` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
