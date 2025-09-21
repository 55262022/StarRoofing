-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2025 at 05:43 PM
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
-- Database: `starroofing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT 2,
  `account_status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `email`, `password`, `role_id`, `account_status`, `last_login`, `created_at`, `updated_at`) VALUES
(7, 'ajmacaraig19@gmail.com', '$2y$10$Gwf3Dx11g/6RXk6BGabrbebOWJsQVwgGFHzUAHpd4RTfBsAbyH5CW', 2, 'active', '2025-09-12 14:16:02', '2025-09-12 11:34:43', '2025-09-12 14:16:02'),
(8, 'ajmacaraig20@gmail.com', '$2y$10$Ls7fYC93AlgT4LiKWrmN9OhqcejQnACuT/hzdbKkxaFvjO8MAX5CO', 2, 'active', '2025-09-14 12:23:01', '2025-09-12 11:45:37', '2025-09-14 12:23:01');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_code` varchar(20) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_code`, `category_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'design', 'Design & Construction', 'Design and construction services', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(2, 'roofing', 'All Kinds of Roofing', 'Various roofing materials and services', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(3, 'steel', 'Steel Truss', 'Steel truss products and installation', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(4, 'upvc', 'uPVC Windows', 'uPVC window products', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(5, 'glass', 'Glass & Aluminum', 'Glass and aluminum products', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(6, 'cabinet', 'Modular Cabinet', 'Modular cabinet solutions', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(7, 'doors', 'Combi/Blind & Roll Up Doors', 'Various door types', '2025-09-14 11:46:09', '2025-09-14 11:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(20) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, `unit`, `image_path`, `created_by`, `created_at`, `updated_at`, `is_archived`) VALUES
(1, 2, 'Roofing', 'Bubong', 1000.00, 75, 'piece', 'https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.ugc.ph%2Fproduct%2Fduratile%2F&psig=AOvVaw3X_uMdXyDZnuuECXufnzjQ&ust=1757939065451000&source=images&cd=vfe&opi=89978449&ved=0CBUQjRxqFwoTCOCD-L6f2I8DFQAAAAAdAAAAABAL', 8, '2025-09-14 12:25:05', '2025-09-14 15:43:06', 1),
(2, 4, 'Aluminum Window', 'Aluminum, Glass, Window', 2500.00, 50, 'sqm', 'uploads/products/1757862544_538629338_1241032341369672_5542304872222334214_n.jpg', 8, '2025-09-14 13:07:13', '2025-09-14 15:09:04', 0);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `role_description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Can manage content and users but with some restrictions', '2025-09-10 10:16:40', '2025-09-12 11:32:29'),
(2, 'user', 'Regular user with limited access', '2025-09-10 10:16:40', '2025-09-12 11:32:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `region_code` varchar(10) DEFAULT NULL,
  `region_name` varchar(100) DEFAULT NULL,
  `province_code` varchar(10) DEFAULT NULL,
  `province_name` varchar(100) DEFAULT NULL,
  `city_code` varchar(10) DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL,
  `barangay_code` varchar(10) DEFAULT NULL,
  `barangay_name` varchar(100) DEFAULT NULL,
  `street` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `account_id`, `first_name`, `last_name`, `middle_name`, `birthdate`, `contact_number`, `gender`, `region_code`, `region_name`, `province_code`, `province_name`, `city_code`, `city_name`, `barangay_code`, `barangay_name`, `street`, `created_at`, `updated_at`) VALUES
(2, 7, 'aj', 'lin', 'mac', '2025-09-02', '091287382173721', 'male', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', '2025-09-12 11:34:43', '2025-09-12 11:43:27'),
(3, 8, 'haha', 'ai', 'no', '2025-09-01', '09127312983', 'male', '10', 'Region X (Northern Mindanao)', '1018', 'Camiguin', '101802', 'Guinsiliban', '101802003', 'Cantaan', 'haha', '2025-09-12 11:45:37', '2025-09-12 11:45:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_code` (`category_code`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
