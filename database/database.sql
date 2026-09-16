-- ==============================================================================
-- Rentora: Campus Equipment Exchange & Rental Hub
-- Database Definition & Mock Dataset for Bangladeshi University Campus Operations
-- Target DBMS: MySQL / MariaDB (phpMyAdmin)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `rentora` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rentora`;

-- ------------------------------------------------------------------------------
-- 1. Table structure for table `users`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `disputes`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `rental_requests`;
DROP TABLE IF EXISTS `items`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(50) NOT NULL UNIQUE,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `role` enum('Renter','Owner','Admin') NOT NULL DEFAULT 'Renter',
  `status` enum('Pending','Verified','Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 2. Table structure for table `categories`
-- ------------------------------------------------------------------------------
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 3. Table structure for table `items`
-- ------------------------------------------------------------------------------
CREATE TABLE `items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `security_deposit` decimal(10,2) NOT NULL,
  `item_condition` varchar(50) NOT NULL DEFAULT 'Good',
  `campus_spot` varchar(150) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`),
  KEY `fk_items_owner` (`owner_id`),
  KEY `fk_items_category` (`category_id`),
  CONSTRAINT `fk_items_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_items_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 4. Table structure for table `rental_requests`
-- ------------------------------------------------------------------------------
CREATE TABLE `rental_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `renter_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_rent` decimal(10,2) NOT NULL,
  `deposit` decimal(10,2) NOT NULL,
  `handover_token` varchar(20) NOT NULL,
  `pickup_spot` varchar(150) NOT NULL,
  `status` enum('Pending','Approved','Active','Returned','Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`),
  KEY `fk_rentals_item` (`item_id`),
  KEY `fk_rentals_renter` (`renter_id`),
  CONSTRAINT `fk_rentals_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rentals_renter` FOREIGN KEY (`renter_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 5. Table structure for table `payments`
-- ------------------------------------------------------------------------------
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `method` varchar(50) NOT NULL DEFAULT 'bKash',
  `amount` decimal(10,2) NOT NULL,
  `trx_id` varchar(50) NOT NULL,
  `status` enum('Escrow Locked','Released to Owner','Refunded to Renter') NOT NULL DEFAULT 'Escrow Locked',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `fk_payments_request` (`request_id`),
  CONSTRAINT `fk_payments_request` FOREIGN KEY (`request_id`) REFERENCES `rental_requests` (`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 6. Table structure for table `disputes`
-- ------------------------------------------------------------------------------
CREATE TABLE `disputes` (
  `dispute_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `raised_by` int(11) NOT NULL,
  `issue_type` varchar(100) NOT NULL,
  `notes` text NOT NULL,
  `status` enum('Open','Resolved','Dismissed') NOT NULL DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dispute_id`),
  KEY `fk_disputes_request` (`request_id`),
  KEY `fk_disputes_user` (`raised_by`),
  CONSTRAINT `fk_disputes_request` FOREIGN KEY (`request_id`) REFERENCES `rental_requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_disputes_user` FOREIGN KEY (`raised_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==============================================================================
-- INSERT MOCK DATA
-- ==============================================================================

-- 1. Users
INSERT INTO `users` (`user_id`, `student_id`, `name`, `email`, `password`, `phone`, `role`, `status`) VALUES
(1, 'CSE-22-0145', 'Rafiqul Islam', 'rafiqul.cse22@univ.ac.bd', '123456', '+8801712-345678', 'Renter', 'Verified'),
(2, 'CSE-21-0342', 'Tanvir Ahmed', 'tanvir.cse21@univ.ac.bd', '123456', '+8801723-984421', 'Owner', 'Verified'),
(3, 'CSE-22-0892', 'Farhan Kabir', 'farhan.cse22@univ.ac.bd', '123456', '+8801688-442211', 'Owner', 'Verified'),
(4, 'ARCH-20-0081', 'Nusrat Jahan', 'nusrat.arch20@univ.ac.bd', '123456', '+8801819-556677', 'Owner', 'Verified'),
(5, 'CSE-23-0182', 'Fahim Muntasir', 'fahim.cse23@univ.ac.bd', '123456', '+8801712-998811', 'Renter', 'Pending'),
(6, 'ARCH-23-0044', 'Samira Akhtar', 'samira.arch23@univ.ac.bd', '123456', '+8801898-765432', 'Renter', 'Pending'),
(7, 'EEE-22-0511', 'Tahsin Zaman', 'tahsin.eee22@univ.ac.bd', '123456', '+8801911-223344', 'Renter', 'Pending'),
(8, 'ADMIN-01', 'University Proctor Admin', 'admin@univ.ac.bd', 'admin123', '+8801700-000000', 'Admin', 'Verified');

-- 2. Categories
INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, 'Scientific Calculators'),
(2, 'Drafter Kits'),
(3, 'Lab Coats & Safety'),
(4, 'Arduino / IoT Kits'),
(5, 'DSLR Cameras'),
(6, 'Sports Gear'),
(7, 'Lab Instruments');

-- 3. Items
INSERT INTO `items` (`item_id`, `owner_id`, `category_id`, `title`, `description`, `daily_rate`, `security_deposit`, `item_condition`, `campus_spot`, `image_url`, `is_available`) VALUES
(1, 2, 1, 'Casio fx-991EX ClassWiz Scientific Calculator', 'Authentic Casio ClassWiz fx-991EX with high-res Natural Textbook display and 552 functions. Allowed in mid/final exams.', 60.00, 500.00, 'Like New', 'Central Library Front Gate', 'https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?w=600&auto=format&fit=crop&q=80', 1),
(2, 4, 2, 'Rotring Precision Mini Drafter & 60cm T-Square Kit', 'Rotring college drafter with 360-degree protractor head, steel desk clamp, acrylic T-square and waterproof case.', 120.00, 800.00, 'Good', 'Academic Building-1 Gate', 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=600&auto=format&fit=crop&q=80', 1),
(3, 3, 5, 'Canon EOS 80D DSLR Kit + 18-135mm IS USM Lens', '24.2 MP APS-C Dual Pixel camera kit with 64GB Extreme SD card, 2x batteries, charger and bag. Ideal for university media fest.', 650.00, 4000.00, 'Like New', 'TSC Ground / Student Union', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&auto=format&fit=crop&q=80', 1),
(4, 2, 4, 'Arduino Mega 2560 Pro IoT Suite & 37 Sensor Kit', 'Complete hardware dev kit with ESP8266 Wi-Fi, Bluetooth, Ultrasonic, DHT22, relays, servos, breadboards and jumpers.', 90.00, 600.00, 'Like New', 'Engineering Lab Complex (3rd Floor)', 'https://images.unsplash.com/photo-1553406830-ef2513450d76?w=600&auto=format&fit=crop&q=80', 1),
(5, 2, 3, 'Premium White Cotton Lab Coat + Anti-Fog Splash Goggles', '100% Bleached Heavy Cotton lab coat (Medium size) and chemical splash goggles. Freshly washed and ironed.', 40.00, 250.00, 'Good', 'Campus Cafeteria Entrance', 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80', 1),
(6, 3, 6, 'Yonex Astrox 88D Pro Badminton Rackets (Pair + Feathers)', 'Stiff head-heavy balanced rackets with 26 lbs BG65 strings and padded carrying bag. Great for evening campus tournament matches.', 80.00, 450.00, 'Good', 'TSC Ground / Student Union', 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=600&auto=format&fit=crop&q=80', 1),
(7, 2, 4, 'Raspberry Pi 4 Model B (8GB RAM) with 3.5\" Touchscreen', 'Quad-core Cortex-A72 SoC, 64GB MicroSD with OS, 3.5 inch resistive touch LCD and official 5.1V power supply.', 140.00, 1200.00, 'Like New', 'Central Library Front Gate', 'https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?w=600&auto=format&fit=crop&q=80', 1),
(8, 3, 7, 'Hantek 2D42 3-in-1 Handheld Digital Oscilloscope & Multimeter', '40MHz Dual Channel oscilloscope, True RMS 4000 counts multimeter, and Arbitrary Waveform Generator.', 220.00, 2500.00, 'Like New', 'Engineering Lab Complex (3rd Floor)', 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=600&auto=format&fit=crop&q=80', 1);

-- 4. Rental Requests
INSERT INTO `rental_requests` (`request_id`, `item_id`, `renter_id`, `start_date`, `end_date`, `total_rent`, `deposit`, `handover_token`, `pickup_spot`, `status`) VALUES
(1, 3, 1, '2026-09-11', '2026-09-14', 1950.00, 4000.00, 'TRX-8291', 'TSC Ground / Student Union', 'Approved'),
(2, 1, 1, '2026-09-08', '2026-09-10', 120.00, 500.00, 'RENT-4092', 'Central Library Front Gate', 'Active'),
(3, 2, 1, '2026-09-15', '2026-09-17', 240.00, 800.00, 'REQ-1144', 'Academic Building-1 Gate', 'Pending'),
(4, 5, 1, '2026-09-01', '2026-09-03', 80.00, 250.00, 'RET-9011', 'Campus Cafeteria Entrance', 'Returned');

-- 5. Payments
INSERT INTO `payments` (`payment_id`, `request_id`, `method`, `amount`, `trx_id`, `status`) VALUES
(1, 1, 'bKash', 5950.00, 'BK9928172X', 'Escrow Locked'),
(2, 2, 'Nagad', 620.00, 'NG7744119Z', 'Escrow Locked'),
(3, 4, 'bKash', 330.00, 'BK1100994A', 'Refunded to Renter');

-- 6. Disputes
INSERT INTO `disputes` (`dispute_id`, `request_id`, `raised_by`, `issue_type`, `notes`, `status`) VALUES
(1, 1, 3, 'Item Damage', 'Lender reports hairline scratch on camera UV filter. Renter claims scratch was pre-existing.', 'Open'),
(2, 2, 2, 'Late Return', 'Renter exceeded rental by 4 hours without notice; requesting ৳50 university overtime fee.', 'Open');
