-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2025 at 04:02 AM
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
-- Database: `event_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `notification_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'booking',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`notification_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 'New Pending Booking', 'Customer Katelin Ann Lilam Arana has made a new booking (Event ID: 16).', 'booking', 0, '2025-11-15 17:22:49'),
(2, 'New Pending Booking', 'Customer Katelin Ann Lilam Arana has made a new booking (Event ID: 17).', 'booking', 0, '2025-11-15 17:58:48'),
(3, 'New Pending Booking', 'Customer Katelin Ann Lilam Arana has made a new booking (Event ID: 18).', 'booking', 0, '2025-11-15 18:01:38'),
(4, 'New Pending Booking', 'Customer Katelin Ann Lilam Arana has made a new booking (Event ID: 19).', 'booking', 0, '2025-11-15 18:09:56'),
(5, 'New Pending Booking', 'Customer Katelin Ann Lilam Arana has made a new booking (Event ID: 20).', 'booking', 0, '2025-11-15 18:12:24'),
(6, 'New Pending Booking', 'Customer Katelin Ann Lilam Arana has made a new booking (Event ID: 21).', 'booking', 0, '2025-11-15 18:31:30'),
(7, 'New Pending Booking', 'Customer Katelin Ann Lilam Arana has made a new booking (Event ID: 22).', 'booking', 0, '2025-11-15 18:36:02'),
(8, 'New Pending Booking', 'Customer Katelin Ann Lilam Arana has made a new booking (Event ID: 23).', 'booking', 0, '2025-11-15 19:53:21');

-- --------------------------------------------------------

--
-- Stand-in structure for view `booking_details`
-- (See below for the actual view)
--
CREATE TABLE `booking_details` (
`event_id` int(11)
,`user_id` int(11)
,`event_name` varchar(100)
,`event_date` date
,`event_time` time
,`venue_name` varchar(100)
,`theme_name` varchar(100)
,`status` enum('pending','confirmed','completed','cancelled')
,`service_name` varchar(100)
,`rate` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `catering`
--

CREATE TABLE `catering` (
  `catering_id` int(11) NOT NULL,
  `catering_name` varchar(100) NOT NULL,
  `menu_details` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `catering_menus`
--

CREATE TABLE `catering_menus` (
  `id` int(11) NOT NULL,
  `menu_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `meal_type` enum('Appetizer','Main Course','Side Dish','Dessert','Beverages','Salad') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `catering_menus`
--

INSERT INTO `catering_menus` (`id`, `menu_name`, `description`, `price`, `image`, `meal_type`) VALUES
(3, ' cv ', 'cd', 4.00, 'asskaybilly.jpg', 'Appetizer'),
(4, 'n znxb', '3ef', 45.00, 'AGATGACHRIST.jpg', 'Main Course'),
(5, 'Chocolate cake', 'basta', 300.00, 'IMG_20241021_122219.jpg', 'Dessert'),
(6, 'hotdog', 'mapino humk-humok kag namit namit gid yah', 15.00, 'Messenger_creation_76E1593A-609B-4A4F-9B30-993CCABE58FE.jpeg', 'Side Dish'),
(7, 'DRINKS', 'colacola', 25.00, 'Messenger_creation_FD3B1A21-5EFE-42C2-AC3C-6F0DA18C9BA7.jpeg', 'Beverages');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `venue_id` int(11) DEFAULT NULL,
  `theme_id` int(11) DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `guest_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `customer_id`, `event_name`, `event_date`, `event_time`, `venue_id`, `theme_id`, `status`, `created_at`, `guest_count`) VALUES
(7, 25, 'birthday', '2025-11-13', '15:00:00', 3, 2, 'confirmed', '2025-11-13 01:12:26', 0),
(8, 25, 'hhvcj', '2025-11-13', '21:49:00', 4, 2, 'confirmed', '2025-11-13 01:49:43', 100),
(13, 25, 'HCHB H', '2025-11-15', '13:53:00', 9, 2, 'confirmed', '2025-11-14 17:51:37', 178),
(14, 25, 'BIrtday ng aking anak', '2025-11-26', '03:03:00', 10, 3, 'cancelled', '2025-11-14 18:04:14', 100),
(15, 25, 'meryl', '2025-12-04', '09:03:00', 11, 3, 'confirmed', '2025-11-15 13:03:51', 100),
(16, 25, 'KORUKOKOK', '2025-11-22', '13:22:00', 12, 2, 'confirmed', '2025-11-15 17:22:49', 20),
(18, 25, 'basta', '2025-11-13', '23:49:00', 11, 2, 'confirmed', '2025-11-15 18:01:38', 12),
(21, 25, 'BWESIT', '2025-11-13', '23:49:00', 14, 2, 'cancelled', '2025-11-15 18:31:30', 123),
(23, 25, 'NJB', '2025-11-28', '15:53:00', 17, 2, 'confirmed', '2025-11-15 19:53:21', 56);

-- --------------------------------------------------------

--
-- Table structure for table `event_catering`
--

CREATE TABLE `event_catering` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `catering_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_catering`
--

INSERT INTO `event_catering` (`id`, `event_id`, `catering_id`) VALUES
(1, 13, 3),
(2, 13, 4),
(3, 13, 5),
(4, 14, 3),
(5, 14, 5),
(6, 15, 5),
(7, 15, 4),
(8, 15, 3),
(9, 16, 4),
(10, 18, 4),
(12, 21, 5);

-- --------------------------------------------------------

--
-- Table structure for table `event_schedule`
--

CREATE TABLE `event_schedule` (
  `schedule_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `activity_name` varchar(150) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `assigned_staff` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_services`
--

CREATE TABLE `event_services` (
  `event_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_services`
--

INSERT INTO `event_services` (`event_id`, `service_id`) VALUES
(7, 4),
(7, 5),
(7, 6),
(7, 7),
(7, 9),
(8, 6),
(13, 5),
(13, 6),
(13, 9),
(14, 4),
(14, 7),
(15, 4),
(15, 5),
(16, 7),
(16, 9),
(18, 6),
(21, 6),
(23, 7);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('booking','staff','payment','general') DEFAULT 'general',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','paid','refunded') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `assigned_staff_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `description`, `rate`, `status`, `assigned_staff_count`) VALUES
(4, 'Photographer', 'Professional photography services', 2500.00, 'active', 5),
(5, 'Videographer', 'Event videography services', 3000.00, 'active', 5),
(6, 'Host', 'Event hosting and emceeing services', 4000.00, 'active', 5),
(7, 'DJ', 'DJ and music services for events. \r\n\r\n\r\nService rates are subject to change depending on the hours requested.', 1800.00, 'active', 4),
(9, 'Event Designer', 'Plans and executes the visual aspects of an event', 3500.00, 'active', 5),
(12, 'Clowns', 'I\'ll make you laugh, baby!', 1000.00, 'active', 5);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `availability` enum('available','unavailable') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_services`
--

CREATE TABLE `staff_services` (
  `staff_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `assigned_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `theme_id` int(11) NOT NULL,
  `theme_name` varchar(100) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`theme_id`, `theme_name`, `category`, `description`, `price`) VALUES
(2, 'dasfdfgg', 'Wedding', 'asfghf', 600.00),
(3, 'hehe', 'Birthday', 'wlaa charchar', 600.00);

-- --------------------------------------------------------

--
-- Table structure for table `theme_images`
--

CREATE TABLE `theme_images` (
  `image_id` int(11) NOT NULL,
  `theme_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `theme_images`
--

INSERT INTO `theme_images` (`image_id`, `theme_id`, `image_path`, `uploaded_at`) VALUES
(2, 2, '68d56e234f581_White Cream Wedding And Event Logo.png', '2025-09-25 16:30:27'),
(3, 2, '68d56e2383451_Eventlink Logo Design.png', '2025-09-25 16:30:27'),
(4, 2, '68d56e238cbec_Copy of thumbnails (3).png', '2025-09-25 16:30:27'),
(5, 2, '68d56e239e918_Copy of thumbnails (2).png', '2025-09-25 16:30:27'),
(6, 3, '68d68717de058_hd_68c1a9bc08047.png', '2025-09-26 12:29:11'),
(7, 3, '68d68717ec0f3_Copy of thumbnails (1).png', '2025-09-26 12:29:11'),
(8, 3, '68d68718045c9_7d487531a7888558cd40a9c5ac2fa593-removebg-preview.png', '2025-09-26 12:29:12'),
(9, 3, '68d687182bf52_d58901f98ccb274a6b4a2e3cb9681e6e-removebg-preview.png', '2025-09-26 12:29:12'),
(10, 3, '68d6871838611_f04057952497fac2d850b6579b010bdd-removebg-preview.png', '2025-09-26 12:29:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin','staff') NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `role`, `contact_number`, `created_at`, `status`) VALUES
(18, '', NULL, '', 'admin@gmail.com', '$2y$10$ju7hSFpSBhfj/mHb8a48Z.gC1QYIGvAzeoZyLXsx3YNkd7CJtdKxS', 'admin', '', '2025-10-05 02:21:43', 'active'),
(25, 'Katelin Ann', 'Lilam', 'Arana', 'cathlene@gmail.com', '$2y$10$OZ5GwyoTSn5GS7A7uekoy.FxbyX6W.TyyUuKIfqKzvF7TWsCfBWwu', 'customer', '09876543212', '2025-10-22 18:54:09', 'active'),
(26, 'Cathlene Ann', 'lilam', 'Arana', 'lilamcathlene@gmail.com', '$2y$10$HqQHgtX/pIbj544BZpXFz.W24FVzIdi1WLPYkSavJKoJYsxZ9Q78G', 'customer', '', '2025-10-22 19:06:42', 'active');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `prevent_admin_delete` BEFORE DELETE ON `users` FOR EACH ROW BEGIN
  IF OLD.role = 'admin' THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Deletion blocked: admin accounts cannot be deleted.';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `venue_id` int(11) NOT NULL,
  `venue_name` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`venue_id`, `venue_name`, `location`, `capacity`, `price`) VALUES
(1, 'kilid lang', NULL, NULL, 0.00),
(2, 'sdsadsa', NULL, NULL, 0.00),
(3, 'bisan diin', NULL, NULL, 0.00),
(4, 'n hvv', NULL, NULL, 0.00),
(5, 'lkcspih', NULL, NULL, 0.00),
(6, 'nhb', NULL, NULL, 0.00),
(7, 'CBCJ', NULL, NULL, 0.00),
(8, 'X BJC', NULL, NULL, 0.00),
(9, 'CSDC', NULL, NULL, 0.00),
(10, 'Sa aking bahay.', NULL, NULL, 0.00),
(11, 'gym', NULL, NULL, 0.00),
(12, 'KURIMI', NULL, NULL, 0.00),
(13, 'basta', NULL, NULL, 0.00),
(14, 'DEPUTA', NULL, NULL, 0.00),
(15, 'BWESIT', NULL, NULL, 0.00),
(16, 'HANGAG', NULL, NULL, 0.00),
(17, 'NJUB', NULL, NULL, 0.00);

-- --------------------------------------------------------

--
-- Structure for view `booking_details`
--
DROP TABLE IF EXISTS `booking_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `booking_details`  AS SELECT `e`.`event_id` AS `event_id`, `e`.`customer_id` AS `user_id`, `e`.`event_name` AS `event_name`, `e`.`event_date` AS `event_date`, `e`.`event_time` AS `event_time`, `v`.`venue_name` AS `venue_name`, `t`.`theme_name` AS `theme_name`, `e`.`status` AS `status`, `s`.`service_name` AS `service_name`, `s`.`rate` AS `rate` FROM ((((`events` `e` left join `venues` `v` on(`e`.`venue_id` = `v`.`venue_id`)) left join `themes` `t` on(`e`.`theme_id` = `t`.`theme_id`)) left join `event_services` `es` on(`e`.`event_id` = `es`.`event_id`)) left join `services` `s` on(`es`.`service_id` = `s`.`service_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `catering`
--
ALTER TABLE `catering`
  ADD PRIMARY KEY (`catering_id`);

--
-- Indexes for table `catering_menus`
--
ALTER TABLE `catering_menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `events_ibfk_1` (`customer_id`),
  ADD KEY `events_ibfk_2` (`venue_id`),
  ADD KEY `events_ibfk_3` (`theme_id`);

--
-- Indexes for table `event_catering`
--
ALTER TABLE `event_catering`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `catering_id` (`catering_id`);

--
-- Indexes for table `event_schedule`
--
ALTER TABLE `event_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `assigned_staff` (`assigned_staff`),
  ADD KEY `event_schedule_ibfk_1` (`event_id`);

--
-- Indexes for table `event_services`
--
ALTER TABLE `event_services`
  ADD PRIMARY KEY (`event_id`,`service_id`),
  ADD KEY `event_services_ibfk_2` (`service_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notifications_user` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `payments_ibfk_1` (`event_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `staff_services`
--
ALTER TABLE `staff_services`
  ADD PRIMARY KEY (`staff_id`,`service_id`),
  ADD KEY `fk_ss_service` (`service_id`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`theme_id`);

--
-- Indexes for table `theme_images`
--
ALTER TABLE `theme_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `theme_images_ibfk_1` (`theme_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`venue_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `catering`
--
ALTER TABLE `catering`
  MODIFY `catering_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `catering_menus`
--
ALTER TABLE `catering_menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `event_catering`
--
ALTER TABLE `event_catering`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `event_schedule`
--
ALTER TABLE `event_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `theme_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `theme_images`
--
ALTER TABLE `theme_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `venue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `events_ibfk_3` FOREIGN KEY (`theme_id`) REFERENCES `themes` (`theme_id`) ON UPDATE CASCADE;

--
-- Constraints for table `event_catering`
--
ALTER TABLE `event_catering`
  ADD CONSTRAINT `event_catering_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_catering_ibfk_2` FOREIGN KEY (`catering_id`) REFERENCES `catering_menus` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_schedule`
--
ALTER TABLE `event_schedule`
  ADD CONSTRAINT `event_schedule_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_services`
--
ALTER TABLE `event_services`
  ADD CONSTRAINT `event_services_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `event_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `staff_services`
--
ALTER TABLE `staff_services`
  ADD CONSTRAINT `fk_ss_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ss_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_services_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `theme_images`
--
ALTER TABLE `theme_images`
  ADD CONSTRAINT `theme_images_ibfk_1` FOREIGN KEY (`theme_id`) REFERENCES `themes` (`theme_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
