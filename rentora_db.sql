-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2026 at 04:28 PM
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
-- Database: `rentora_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(10) UNSIGNED NOT NULL,
  `admin_username` varchar(50) NOT NULL,
  `admin_password_hash` varchar(255) NOT NULL,
  `admin_email` varchar(100) DEFAULT NULL,
  `last_password_change` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_username`, `admin_password_hash`, `admin_email`, `last_password_change`) VALUES
(1, 'admin', '$2y$10$mHv7idMAbSDyO1SNadCPM.5r.Ywrh/jMi.LgMhNUKnIlbsLCzL2yO', 'admin@rentora.local', '2026-09-16 17:38:53');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(3, 'Books & Stationery'),
(1, 'Electronics'),
(6, 'Furniture'),
(5, 'Lab & Project Equipment'),
(4, 'Musical Instruments'),
(2, 'Sports & Fitness');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(10) UNSIGNED NOT NULL,
  `equipment_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `condition_status` enum('New','Good','Fair','Poor') NOT NULL DEFAULT 'Good',
  `availability_status` enum('Available','Rented','Exchanged','Unavailable') NOT NULL DEFAULT 'Available',
  `rental_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `security_deposit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `campus_spot` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `owner_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exchange_agreement`
--

CREATE TABLE `exchange_agreement` (
  `exchange_id` int(10) UNSIGNED NOT NULL,
  `exchange_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Accepted','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  `lender_a_id` int(10) UNSIGNED NOT NULL,
  `lender_b_id` int(10) UNSIGNED NOT NULL,
  `equipment_a_id` int(10) UNSIGNED NOT NULL,
  `equipment_b_id` int(10) UNSIGNED NOT NULL,
  `cash_adjustment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cash_direction` enum('none','offer','demand') NOT NULL DEFAULT 'none'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `member_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `university_email` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `campus_address` varchar(255) DEFAULT NULL,
  `account_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Pending','Verified','Rejected') NOT NULL DEFAULT 'Pending',
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`member_id`, `first_name`, `last_name`, `student_id`, `dob`, `gender`, `university_email`, `phone_number`, `campus_address`, `account_balance`, `status`, `username`, `password_hash`, `created_at`) VALUES
(5, 'Prattoy Barua', 'Turja', '0222420005101171', NULL, NULL, 'prattoy_46171@bscse.puc.ac.bd', '01641691478', 'Hazari Lane', 0.00, 'Pending', 'prattoyturjo5', '$2y$10$6Xnbj3j7tk2sZq4UtG4NXe2pHHleQXZBrwrV/BiiHRa4cVwOjYPcu', '2026-09-17 16:19:56'),
(6, 'Aiman', 'Hussain', '0222420005101197', NULL, NULL, 'aiman_46197@bscse.puc.ac.bd', '01758806030', 'Hazari Lane', 0.00, 'Rejected', 'niggaiman97', '$2y$10$s2oZuopdA1Dfg7w3.WtBA.C3C02GIP7sIrIf51tlmc7/VFiYGaCze', '2026-09-18 16:52:08'),
(7, 'Shreya', 'Chakraborty', '0222420005101183', NULL, NULL, 'shreya_46183@bscse.puc.ac.bd', '01234567890', 'Hazari Lane', 0.00, 'Verified', 'shreya83', '$2y$10$hh2eVGVC0UYODzkAnqp.jOEFlmc0FNbfjaEnLDo9j/xT0QxpNQ9O6', '2026-09-18 17:04:00'),
(8, 'Samia', 'Akter', '0222420005101172', NULL, NULL, 'Samia_46172@bscse.puc.ac.bd', '01234567890', 'Hazari Lane', 0.00, 'Pending', 'samia72', '$2y$10$nzqQkZjONleaCk8KQ10qi.9S6lBZQaATbfZcimhHAXOqxzk9MBzWa', '2026-09-19 04:11:09');

-- --------------------------------------------------------

--
-- Table structure for table `rental_agreement`
--

CREATE TABLE `rental_agreement` (
  `rental_id` int(10) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `expected_end_date` date NOT NULL,
  `actual_end_date` date DEFAULT NULL,
  `total_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fine` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Pending','Active','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `handover_token` varchar(20) DEFAULT NULL,
  `deposit_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pickup_spot` varchar(100) DEFAULT NULL,
  `renter_id` int(10) UNSIGNED NOT NULL,
  `equipment_id` int(10) UNSIGNED NOT NULL
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `uq_admin_username` (`admin_username`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `uq_category_name` (`category_name`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`),
  ADD KEY `fk_equipment_owner` (`owner_id`),
  ADD KEY `fk_equipment_category` (`category_id`);

--
-- Indexes for table `exchange_agreement`
--
ALTER TABLE `exchange_agreement`
  ADD PRIMARY KEY (`exchange_id`),
  ADD KEY `fk_exchange_lender_a` (`lender_a_id`),
  ADD KEY `fk_exchange_lender_b` (`lender_b_id`),
  ADD KEY `fk_exchange_equipment_a` (`equipment_a_id`),
  ADD KEY `fk_exchange_equipment_b` (`equipment_b_id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `uq_member_email` (`university_email`),
  ADD UNIQUE KEY `uq_member_username` (`username`),
  ADD UNIQUE KEY `uq_member_student_id` (`student_id`);

--
-- Indexes for table `rental_agreement`
--
ALTER TABLE `rental_agreement`
  ADD PRIMARY KEY (`rental_id`),
  ADD UNIQUE KEY `uq_rental_handover_token` (`handover_token`),
  ADD KEY `fk_rental_renter` (`renter_id`),
  ADD KEY `fk_rental_equipment` (`equipment_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `exchange_agreement`
--
ALTER TABLE `exchange_agreement`
  MODIFY `exchange_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `member_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `rental_agreement`
--
ALTER TABLE `rental_agreement`
  MODIFY `rental_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `fk_equipment_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_equipment_owner` FOREIGN KEY (`owner_id`) REFERENCES `member` (`member_id`);

--
-- Constraints for table `exchange_agreement`
--
ALTER TABLE `exchange_agreement`
  ADD CONSTRAINT `fk_exchange_equipment_a` FOREIGN KEY (`equipment_a_id`) REFERENCES `equipment` (`equipment_id`),
  ADD CONSTRAINT `fk_exchange_equipment_b` FOREIGN KEY (`equipment_b_id`) REFERENCES `equipment` (`equipment_id`),
  ADD CONSTRAINT `fk_exchange_lender_a` FOREIGN KEY (`lender_a_id`) REFERENCES `member` (`member_id`),
  ADD CONSTRAINT `fk_exchange_lender_b` FOREIGN KEY (`lender_b_id`) REFERENCES `member` (`member_id`);

--
-- Constraints for table `rental_agreement`
--
ALTER TABLE `rental_agreement`
  ADD CONSTRAINT `fk_rental_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`),
  ADD CONSTRAINT `fk_rental_renter` FOREIGN KEY (`renter_id`) REFERENCES `member` (`member_id`);

-- --------------------------------------------------------

--
-- Table structure for table `team_applications`
--

CREATE TABLE `team_applications` (
  `application_id` int(10) UNSIGNED NOT NULL,
  `applicant_name` varchar(100) NOT NULL,
  `university_email` varchar(100) NOT NULL,
  `student_id` varchar(30) NOT NULL,
  `department` varchar(100) NOT NULL DEFAULT 'Computer Science & Engineering',
  `phone_number` varchar(25) NOT NULL,
  `role_applied` varchar(100) NOT NULL,
  `portfolio_link` varchar(255) DEFAULT NULL,
  `technical_skills` text DEFAULT NULL,
  `statement_of_purpose` text NOT NULL,
  `status` enum('Pending','Under_Review','Shortlisted','Accepted','Archived') NOT NULL DEFAULT 'Pending',
  `member_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `team_applications`
--
ALTER TABLE `team_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `idx_team_app_status` (`status`),
  ADD KEY `idx_team_app_email` (`university_email`),
  ADD KEY `fk_team_app_member` (`member_id`);

--
-- AUTO_INCREMENT for table `team_applications`
--
ALTER TABLE `team_applications`
  MODIFY `application_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `team_applications`
--
ALTER TABLE `team_applications`
  ADD CONSTRAINT `fk_team_app_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
