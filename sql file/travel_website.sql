-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Feb 23, 2026 at 09:14 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `travel_website`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','editor') DEFAULT 'admin',
  `profile_image` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `status` enum('Active','Inactive','Suspended') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `profile_image`, `last_login`, `last_login_ip`, `status`, `created_at`, `updated_at`) VALUES
(1, 'divu', 'dishantkbhagat@gmail.com', '$2y$10$bTdO8cye20WSBiCiFIbj9O6RS7mkhnbOZ7kqrwq2S03v5T4Cy0O0q', 'Divyansh Nanavati', 'admin', NULL, '2026-02-23 05:19:28', '::1', 'Active', '2026-02-22 20:05:57', '2026-02-23 05:19:28');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('New','Read','Replied','Archived') DEFAULT 'New',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `full_name`, `email`, `phone`, `subject`, `message`, `status`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 'DISHANT BHAGAT', 'dishantkbhagat@gmail.com', '09033186905', 'sdvbbc', 'dsvbdsv bccvd', 'New', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 18:40:02', '2026-02-22 19:35:18'),
(2, 'manav', 'visualvibe.space@gmail.com', '09601982190', 'delivery', 'dfbvfdvb v', 'New', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 19:02:07', '2026-02-22 19:35:15');

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `travel_date` date DEFAULT NULL,
  `travelers` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `source` varchar(50) DEFAULT 'navbar',
  `status` enum('New','Contacted','Closed') DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `package_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`id`, `full_name`, `email`, `phone`, `package_name`, `travel_date`, `travelers`, `message`, `source`, `status`, `created_at`, `updated_at`, `package_id`) VALUES
(1, 'Divyansh Nanavati', 'dishantkbhagat@gmail.com', '09574191532', NULL, '2026-02-25', '3', 'sdcvdv', 'navbar', 'New', '2026-02-21 08:13:10', '2026-02-21 08:13:10', NULL),
(2, 'DISHANT BHAGAT', 'dishantkbhagat@gmail.com', '09033186905', 'dsvfb', '2026-02-24', '5+', 'dsfvdsddfvbvfdv', 'navbar', '', '2026-02-21 08:16:06', '2026-02-22 11:03:42', NULL),
(3, 'DISHANT BHAGAT', 'dishantkbhagat@gmail.com', '09033186905', 'dvd', '2026-02-24', '5+', 'dfvadsfvbfsd', 'package_details', 'New', '2026-02-21 08:41:04', '2026-02-21 08:41:04', NULL),
(4, 'hello', 'visualvibe.space@gmail.com', '09601982190', 'dsvfb', '2026-03-04', '4', 'fwevdfwev', 'aboutus', '', '2026-02-21 18:24:42', '2026-02-22 11:03:31', NULL),
(5, 'bhavk', 'divyanshnanavati08@gmail.com', '09574191532', 'dsvfb', '2026-02-26', '5+', 'dsfvdsv', 'offers', '', '2026-02-22 05:15:19', '2026-02-22 11:22:48', NULL),
(6, 'bhavk', 'dishantkbhagat@gmail.com', '09033186905', 'dsvfb', '2026-02-26', '2', 'asdfbgnvmnmbhgfd', 'navbar', 'New', '2026-02-23 08:13:08', '2026-02-23 08:13:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `rating` int(1) DEFAULT NULL COMMENT '1-5 star rating',
  `status` enum('New','Published','Pending','Rejected','Archived') DEFAULT 'New',
  `admin_notes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `phone`, `subject`, `message`, `rating`, `status`, `admin_notes`, `ip_address`, `user_agent`, `page_url`, `created_at`, `updated_at`) VALUES
(1, 'John Doe', 'john.doe@example.com', '+1234567890', 'Amazing Tour Experience', 'I had an amazing experience with ExploreWorld! The tour was well organized and the guide was very knowledgeable. Would definitely recommend to others!', 5, 'Published', 'Very satisfied customer, might be a good candidate for testimonial on homepage', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '/tours/paris', '2026-02-23 06:17:44', '2026-02-23 07:39:40'),
(2, 'Divyansh Nanavati', 'visualvibe.space@gmail.com', '09601982190', 'VDDBBDS', 'vbcnvbmvfdsdsfb', 4, 'Published', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/Travelcation/submit_feedback.php', '2026-02-23 07:15:41', '2026-02-23 07:36:13'),
(3, 'Rajesh Sharma', 'rajesh.sharma@email.com', '+919876543210', 'Amazing Kashmir Tour', 'The Kashmir tour was absolutely breathtaking! The houseboat stay in Dal Lake was magical, and the guide was very knowledgeable. Everything was well organized and hassle-free. Will definitely book again!', 5, 'Published', 'Excellent feedback, very satisfied customer. Good candidate for homepage testimonial.', '192.168.1.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0', '/packages/kashmir', '2026-02-23 07:44:48', '2026-02-23 07:44:48'),
(4, 'Priya Patel', 'priya.patel@email.com', '+919876543211', 'Good Experience in Goa', 'Had a wonderful time in Goa. The hotels were nice and the beach activities were fun. Only minor issue was the cab service was sometimes late. Otherwise, a great experience overall.', 4, 'Published', 'Good feedback, minor issue with transport. Follow up with transport team.', '192.168.1.106', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/537.36', '/packages/goa', '2026-02-21 07:44:48', '2026-02-23 07:44:48'),
(5, 'Amit Kumar', 'amit.kumar@email.com', '+919876543212', 'Average Experience in Manali', 'The Manali trip was okay. The hotel was good but the food could be better. Some activities mentioned in the itinerary were not available. Need to improve on service quality.', 3, 'Published', 'Average feedback, needs improvement in service delivery. Follow up with operations team.', '192.168.1.107', 'Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36', '/packages/manali', '2026-02-18 07:44:48', '2026-02-23 07:44:48'),
(6, 'Anita Desai', 'anita.desai@email.com', '+919876543213', 'Luxury Thailand Package', 'Just came back from our Thailand honeymoon and it was perfect! The resort in Phuket was stunning, the private dinner on the beach was romantic, and everything was arranged perfectly. Thank you ExploreWorld for making our honeymoon unforgettable!', 5, 'Published', 'Excellent honeymoon feedback, perfect for luxury package promotion.', '192.168.1.108', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', '/packages/thailand', '2026-02-22 07:44:48', '2026-02-23 07:44:48');

-- --------------------------------------------------------

--
-- Table structure for table `hero_carousel`
--

CREATE TABLE `hero_carousel` (
  `id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hero_carousel`
--

INSERT INTO `hero_carousel` (`id`, `image_url`, `thumbnail_url`, `alt_text`, `title`, `description`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(3, 'uploads/hero/1771051822_paris.jpg', 'uploads/hero/thumbnails/thumb_1771051822_paris.jpg', 'asdfvds', 'sdfvvfd', '', 1, 1, '2026-02-14 06:15:18', '2026-02-14 06:50:22'),
(4, 'uploads/hero/1771049750_download (3).jpeg', 'uploads/hero/thumbnails/thumb_1771049750_download (3).jpeg', 'sadsfvdsdasd', 'sdffvdsas', '', 1, 2, '2026-02-14 06:15:50', '2026-02-14 06:15:50');

-- --------------------------------------------------------

--
-- Table structure for table `hero_content`
--

CREATE TABLE `hero_content` (
  `id` int(11) NOT NULL,
  `main_title` varchar(300) NOT NULL,
  `main_description` text NOT NULL,
  `button_text` varchar(100) DEFAULT 'Explore Packages',
  `button_link` varchar(200) DEFAULT '#packages',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hero_content`
--

INSERT INTO `hero_content` (`id`, `main_title`, `main_description`, `button_text`, `button_link`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Discover Amazing Destinations', 'We offer the best national and international tour packages with hotel bookings, transportation, and guided tours. Your dream vacation is just a click away!', 'Explore Packages', '#packages', 1, '2026-02-02 07:03:37', '2026-02-02 07:03:37');

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `hotel_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price_per_night` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` enum('Luxury','Beachfront','Mountain') DEFAULT NULL,
  `features` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `destination_id`, `hotel_name`, `description`, `price_per_night`, `image`, `category`, `features`, `status`, `created_at`) VALUES
(3, 3, 'weerghn', 'dsfsdbfddv', 123, '1770058327goa-3-day-tour-with-panjim-beaches-and-aguda-nightlife.jpg', 'Beachfront', 'saaadsfv', 'Active', '2026-02-02 18:42:07'),
(4, 3, 'asdfghjk', 'sdfdgbnbmngfdfsa', 234, '1770273753african-print-fabrics-also-known-as-ankara-fabrics-490282.jpg', 'Mountain', 'sdfg, dfdg, dsfdb', 'Active', '2026-02-05 06:42:33');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_enquiries`
--

CREATE TABLE `hotel_enquiries` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `hotel_name` varchar(255) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `guests` varchar(50) NOT NULL,
  `rooms` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('New','Read','In Progress','Closed') NOT NULL DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotel_enquiries`
--

INSERT INTO `hotel_enquiries` (`id`, `hotel_id`, `full_name`, `email`, `phone`, `hotel_name`, `check_in_date`, `check_out_date`, `guests`, `rooms`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 'Divyansh Nanavati', 'visualvibe.space@gmail.com', '09574191532', 'asdfghjk', '2026-03-03', '2026-03-04', '5+', '3', 'zdxgfcvhbm', 'In Progress', '2026-02-22 10:37:31', '2026-02-22 13:53:49');

-- --------------------------------------------------------

--
-- Table structure for table `other_service`
--

CREATE TABLE `other_service` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `travel_date` date DEFAULT NULL,
  `travelers` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('New','Contacted','Closed') DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `other_service`
--

INSERT INTO `other_service` (`id`, `full_name`, `email`, `phone`, `package_name`, `travel_date`, `travelers`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'DISHANT BHAGAT', 'dishantkbhagat@gmail.com', '09033186905', 'Hotel Bookings (travel)', '2026-03-31', '23', 'dfghhgfdvvb', 'New', '2026-02-21 07:39:29', '2026-02-21 07:39:29'),
(2, 'DISHANT BHAGAT', 'dishantkbhagat@gmail.com', '09033186905', 'Hotel Bookings (travel)', '2026-03-31', '23', 'dfghhgfdvvb', 'New', '2026-02-21 07:40:26', '2026-02-21 07:40:26'),
(3, 'DISHANT BHAGAT', 'dishantkbhagat@gmail.com', '09033186905', 'Hotel Bookings (travel)', '2026-03-31', '23', 'dfghhgfdvvb', 'New', '2026-02-21 07:43:14', '2026-02-21 07:43:14'),
(4, 'DISHANT BHAGAT', 'dishantkbhagat@gmail.com', '09033186905', 'wefgdfdsv (modal)', '2026-02-25', '3', 'sdavvdasc', 'New', '2026-02-21 08:06:13', '2026-02-21 08:06:13');

-- --------------------------------------------------------

--
-- Table structure for table `popular_destinations`
--

CREATE TABLE `popular_destinations` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `popular_destinations`
--

INSERT INTO `popular_destinations` (`id`, `title`, `slug`, `image`, `status`, `display_order`, `created_at`) VALUES
(3, 'Paris', 'paris', '1770058344_goa-3-day-tour-with-panjim-beaches-and-aguda-nightlife.jpg', 'Active', 1, '2026-02-02 18:02:51'),
(4, 'france', 'france', '1771768657_Untitled design (21).png', 'Active', 2, '2026-02-22 13:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `media_type` enum('flyer','video') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `offer_link` varchar(500) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `title`, `description`, `media_type`, `file_path`, `thumbnail_path`, `offer_link`, `package_id`, `destination_id`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Summer offer', 'dergthjghkgreghm', 'flyer', 'uploads/promotions/flyers/1771731879_699a7ba73e9c7.png', 'uploads/promotions/thumbnails/thumb_1771731879_699a7ba747cf4.png', '', NULL, NULL, 1, 1, '2026-02-22 03:44:39', '2026-02-22 03:44:39'),
(2, 'diwali offer', 'dvbdvdvv', 'video', 'uploads/promotions/videos/1771734637_699a866d6279d.mp4', 'uploads/promotions/thumbnails/thumb_1771734637_699a866d62b87.png', '', NULL, NULL, 2, 1, '2026-02-22 04:30:37', '2026-02-22 04:30:37');

-- --------------------------------------------------------

--
-- Table structure for table `tour_packages`
--

CREATE TABLE `tour_packages` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `locations_covered` text DEFAULT NULL,
  `price` int(11) NOT NULL,
  `price_type` enum('fixed','starting_from') DEFAULT 'fixed',
  `min_people` int(11) DEFAULT 1,
  `image` varchar(255) NOT NULL,
  `package_type` enum('National','International') NOT NULL,
  `duration` varchar(50) NOT NULL,
  `days` int(11) DEFAULT NULL,
  `nights` int(11) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `inclusions` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tour_packages`
--

INSERT INTO `tour_packages` (`id`, `destination_id`, `title`, `description`, `locations_covered`, `price`, `price_type`, `min_people`, `image`, `package_type`, `duration`, `days`, `nights`, `features`, `inclusions`, `status`, `created_at`) VALUES
(6, 3, 'erghn', 'wedfrgfhgf', NULL, 234, 'fixed', 1, '430814665_20231016144736.jpg', 'International', '7 days', NULL, NULL, 'asdfdghg, sdf, wdfgh', NULL, 0, '2026-02-02 18:03:39'),
(7, 3, 'dfvvbv', 'sdfbdfsvv', 'hirjs, sdfbdf, dsfbdffv, sdffwdv', 234, 'fixed', 1, '240dba_f7104a3431614c8aba17cbe19892b417.jpg', 'International', '4 days', 2, 4, 'asdfdghg, dsf, sddf', '', 0, '2026-02-04 14:48:17'),
(8, 3, 'sdfsdbfds', 'dffddfv', NULL, 23, 'fixed', 1, 'Custom-Fabric.jpg', 'International', '7 days', NULL, NULL, 'dfdggfddfd, df, dfd', NULL, 0, '2026-02-04 14:49:31'),
(9, 3, 'dssfvdbvfds', 'sdfdvfdfv', NULL, 2322, 'fixed', 1, 'Mens-Unstitched-Fabric.jpg', 'International', '7 days', NULL, NULL, 'dsv, dsfvd, dfvd', NULL, 0, '2026-02-04 15:07:15'),
(10, 3, 'dvd', 'dfveddfvb', '', 1234, 'fixed', 1, 'Mens-Unstitched-Fabric.jpg', 'National', '4 days', 4, 5, 'saaadsfv, dfdv, dssfvdf', '', 0, '2026-02-04 15:07:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_source` (`source`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `rating` (`rating`);

--
-- Indexes for table `hero_carousel`
--
ALTER TABLE `hero_carousel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hero_content`
--
ALTER TABLE `hero_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_hotels_destination` (`destination_id`);

--
-- Indexes for table `hotel_enquiries`
--
ALTER TABLE `hotel_enquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `other_service`
--
ALTER TABLE `other_service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `popular_destinations`
--
ALTER TABLE `popular_destinations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `destination_id` (`destination_id`),
  ADD KEY `idx_media_type` (`media_type`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `tour_packages`
--
ALTER TABLE `tour_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_packages_destination` (`destination_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `hero_carousel`
--
ALTER TABLE `hero_carousel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hero_content`
--
ALTER TABLE `hero_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hotel_enquiries`
--
ALTER TABLE `hotel_enquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `other_service`
--
ALTER TABLE `other_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `popular_destinations`
--
ALTER TABLE `popular_destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tour_packages`
--
ALTER TABLE `tour_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hotels`
--
ALTER TABLE `hotels`
  ADD CONSTRAINT `fk_hotels_destination` FOREIGN KEY (`destination_id`) REFERENCES `popular_destinations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hotel_enquiries`
--
ALTER TABLE `hotel_enquiries`
  ADD CONSTRAINT `hotel_enquiries_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `tour_packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promotions_ibfk_2` FOREIGN KEY (`destination_id`) REFERENCES `popular_destinations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tour_packages`
--
ALTER TABLE `tour_packages`
  ADD CONSTRAINT `fk_packages_destination` FOREIGN KEY (`destination_id`) REFERENCES `popular_destinations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
