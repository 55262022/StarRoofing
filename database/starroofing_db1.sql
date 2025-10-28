-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 01:44 PM
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
-- Database: `starroofing_db1`
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
(7, 'ajmacaraig19@gmail.com', '$2y$10$vpvhTBLnHY84MDjuEUlAQ.XWjiJ2MW8iUUxsJB0kzF97DtmQU3H/G', 2, 'active', '2025-10-25 13:43:51', '2025-09-12 11:34:43', '2025-10-25 13:43:51'),
(8, 'ajmacaraig20@gmail.com', '$2y$10$0xiOQg8aTUFKO9g/vajF.u5fM.nD9vMHXLqLPLoCia/HEzwR8XqAe', 1, 'active', '2025-10-27 02:02:44', '2025-09-12 11:45:37', '2025-10-27 02:02:44'),
(9, 'ajmacaraig18@gmail.com', '$2y$10$TRYyLzGYJgBEC7JTpo5qD.8IOknPQ/Nhpa04gkhQmrHFe02.P4mLu', 2, 'active', '2025-10-08 06:52:34', '2025-09-15 09:34:45', '2025-10-12 07:29:51'),
(10, '57842022@holycross.edu.ph', '$2y$10$VbwIwOWnPmhDvFsPwccoaOBzujWxl9waRxoJJbhXxMZpWPIR/Mmlu', 2, 'active', NULL, '2025-09-16 11:56:11', '2025-09-16 11:59:57'),
(13, 'admin@gmail.com', '$2y$10$d7Cg4ccEJ1OLypxRhgg3rutDJYaVZwUrCcpzEv0vkIwH0Ddl.wp3a', 1, 'active', '2025-10-19 04:26:59', '2025-10-09 04:09:49', '2025-10-19 04:26:59'),
(15, 'client@example.com', '186474c1f2c2f735a54c2cf82ee8e87f2a5cd30940e280029363fecedfc5328c', 5, 'active', NULL, '2025-10-20 07:26:25', '2025-10-20 07:26:25');

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
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `is_accepted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `email`, `is_accepted`, `created_at`, `updated_at`) VALUES
(6, 'ajmacaraig19@gmail.com', 1, '2025-10-23 07:44:44', '2025-10-24 02:10:08');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `hire_date` date NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `first_name`, `last_name`, `email`, `phone`, `position`, `department`, `hire_date`, `salary`, `status`, `image_path`, `created_at`, `updated_at`, `is_archived`) VALUES
(1, 'archie', 'ramirez', 'chiechie@gmail.com', '09627646372', '69', 'Sales', '2025-09-17', 45345.00, 'active', 'uploads/employees/68cff607c1937.jpg', '2025-09-21 12:56:39', '2025-09-21 13:04:05', 0),
(2, 'Joshua', 'Docena', 'pogiako@gmaill.com', '09728374893', 'Architect', 'Roofing', '2025-09-27', 213123.00, 'active', 'uploads/employees/68d8299b2fef9.png', '2025-09-27 18:14:51', '2025-10-01 09:34:53', 0);

-- --------------------------------------------------------

--
-- Table structure for table `generated_3d_models`
--

CREATE TABLE `generated_3d_models` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `meshy_task_id` varchar(255) NOT NULL,
  `image_hash` varchar(32) DEFAULT NULL,
  `original_image_name` varchar(255) DEFAULT NULL,
  `original_image_hash` varchar(64) DEFAULT NULL COMMENT 'SHA256 hash to detect duplicates',
  `model_filename` varchar(255) DEFAULT NULL,
  `model_path` varchar(255) DEFAULT NULL,
  `model_url` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `generation_status` enum('pending','processing','succeeded','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `generated_3d_models`
--

INSERT INTO `generated_3d_models` (`id`, `product_id`, `meshy_task_id`, `image_hash`, `original_image_name`, `original_image_hash`, `model_filename`, `model_path`, `model_url`, `file_size`, `generation_status`, `error_message`, `created_by`, `created_at`, `updated_at`) VALUES
(1, NULL, '019a174c-c43f-78ea-9fd2-2b8579746ac2', 'c9560bbf6796ed9b0d0fa6fa0ed46602', 'image-18.png', NULL, 'model_019a174c-c43f-78ea-9fd2-2b8579746ac2_1761328644_68fbbe0492632.glb', 'uploads/3dmodels/model_019a174c-c43f-78ea-9fd2-2b8579746ac2_1761328644_68fbbe0492632.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a174c-c43f-78ea-9fd2-2b8579746ac2/output/model.glb?Expires=1761586840&Signature=MALVhCg8Zkej7enmGuABqmPqGYRtxuRexrhQ5FK48zme~T0t7JxaMlt0CyaXC2o92WhLp7xivcB0f3wxvzJ653e2LFnYU24UZIoIiR02at7njZ7BPtE-CYEIKaZBa6xIfXPITJfzR9DMk6Sv-efi~ECu41anCbReYT46yBPqH9VsViZtK7TlTVCYAAdPBU0MGZMeuScHXpXtTAkGw-yADtvHu8RdlbxrG3vJT36BjoMrunV2Prxi1GLW9dRtDZdgXIftaVqgeYdL33WMGjhnkFpJ~ngFqP8HmftOZj4bH3aZT0ZjzZOZwkOVTu8H47jQhG1Mb4-mjm2eIhyztccqeQ__&Key-P', 6442324, 'succeeded', NULL, NULL, '2025-10-24 17:38:19', '2025-10-24 17:57:25'),
(2, NULL, '019a17e2-bde6-7b37-a231-e243255c5e71', '0a76f5b593e77358329f3a2d6d51e8fd', 'a48fcbacc4b04ca1a099e6ebc3df1b40.jpg', NULL, 'model_68fbe07c37346.glb', 'uploads/3dmodels/model_68fbe07c37346.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a17e2-bde6-7b37-a231-e243255c5e71/output/model.glb?Expires=1761596659&Signature=CwEg8tTcMt78hUDh9IAeuD2UubHIiqT366ke18I1wtNChNNadYylOEdZn-04~WlqAdJ2B7LZSKrYwkn5ijs7AGCZ9yTTTFM8bwAFL07ftVzoUFGXbpp4YDvDedheeq5wocNjCvrbASAL~iWn9uBYmoeeDeSfYyp2esIvl7SAJ0TiCwgCNb9J7BqYUQprawA8piQuf~02QxQqe4hi5FKT6BeGe5hR3etje0ZKvHaZevn3rUyzjL-GG3QrDpf8v33lmw-qhqs~D78NY98j-88WmnzQMesP4TI-zpdUjryzO21ZeqczDMDCXjRRKYvKJ4qUH8PitN~phjYBUCOhZT4vaw__&Key-P', 7210324, 'succeeded', NULL, NULL, '2025-10-24 20:22:07', '2025-10-26 18:46:52'),
(3, NULL, '019a19f2-f895-786a-ad90-d85151ecf360', '7f1a41bb0027657cd3692a6e9191d1da', 'images.jpg', NULL, 'model_68fc67be914b7.glb', 'uploads/3dmodels/model_68fc67be914b7.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a19f2-f895-786a-ad90-d85151ecf360/output/model.glb?Expires=1761631290&Signature=dyiaG8HggXt5LaRTcMmGSTEDQG6oQ~YQbePsRXTrZygj2A-QWeVCDNbR-9eto1YUaQaxieHTa2lTfpuHBnvqbAAOB8f9laK-Bwc-rWwxt7yhRmNz1rf8wsZax2ql8dqOh1AFSWIS1cD0ZeRHChgDkU69-tePaeavOUVC5ip48tsICnk5lG~7RRnujUsSXj5inu6SFfVTuH9Mfr03qi-dBf1vGcHTUaxsBMI7eccvwmU10wvvrGA7t9Tjk9ojQpoxb7FQVEmEuTQKN0F1v03O7vW2QTgcg837xEQzMhz57COEBR9SBAp6Ra-6m2nIid9K4ddpYDZ3qR9Ff6pOOaggEg__&Key-P', 9090820, 'succeeded', NULL, NULL, '2025-10-25 05:59:05', '2025-10-26 18:46:52'),
(4, 16, '019a2163-e7c0-7c32-a31c-04c6f82cecfc', '4c2ebc13fbb8e8cb565e3205d1502ccc', '1761490082_foam insulation.jpg', NULL, 'model_68fe5004b2736.glb', 'uploads/3dmodels/model_68fe5004b2736.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a2163-e7c0-7c32-a31c-04c6f82cecfc/output/model.glb?Expires=1761756292&Signature=ZyLriYoCuEBsn52mhUdj8KKflVm7InHvJKqMWEP8oIrz~WbcqlCU3EfXjew911T4dyu4YzZGrU1PGnMaWKIObhg15dlfxvcWDfBj~M3o0RARIXmgvXV2cgpTqrCXPu2YVn1fP8wpSzpRylfOvOxnmDIYGoe0aoa1bHVZ0hm-~xIZ8bf0eg--RG7hB9HBfdkcHWvEiwcHWg1iBnpY0ILJGbZNPfRGOpehHq0AtR94KMyj9pGbOtBVImBv34Vd1eiMW77xGSbptAVVXeJtwlLQaCXTrM-1VpubXXNUWe4GORBo-WtnaUpv3XUVCEE~VhehD2fOYSdMkEUb7xjaiWAqWw__&Key-P', 8322188, 'succeeded', NULL, NULL, '2025-10-26 16:39:47', '2025-10-26 18:46:52');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `region_code` varchar(20) NOT NULL,
  `region_name` varchar(100) NOT NULL,
  `province_code` varchar(20) NOT NULL,
  `province_name` varchar(100) NOT NULL,
  `city_code` varchar(20) NOT NULL,
  `city_name` varchar(100) NOT NULL,
  `barangay_code` varchar(20) NOT NULL,
  `barangay_name` varchar(100) NOT NULL,
  `street` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `conversation_id` int(11) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_accepted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`id`, `firstname`, `lastname`, `email`, `phone`, `region_code`, `region_name`, `province_code`, `province_name`, `city_code`, `city_name`, `barangay_code`, `barangay_name`, `street`, `message`, `product_id`, `conversation_id`, `submitted_at`, `is_accepted`) VALUES
(1, 'John', 'Doe', 'client@example.com', '09171234567', '', '', '', '', '', '', '', '', NULL, 'Hi, I’d like to inquire about a new roofing installation.', NULL, NULL, '2025-10-20 07:29:16', 1),
(2, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'ff', NULL, NULL, '2025-10-21 09:09:58', 1),
(3, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'gg', NULL, NULL, '2025-10-21 09:15:19', 1),
(4, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'gg', NULL, NULL, '2025-10-21 09:17:33', 1),
(5, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'gg', 2, NULL, '2025-10-21 09:23:36', 1),
(6, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'jael lumbay', 2, NULL, '2025-10-22 03:14:07', 1),
(7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'hello po', 2, NULL, '2025-10-22 08:21:21', 1),
(8, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'jpjppjpp', 2, NULL, '2025-10-22 08:22:52', 1),
(9, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'uyuy', 2, NULL, '2025-10-22 09:11:49', 0),
(10, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'uyuy', 2, NULL, '2025-10-22 09:11:50', 0),
(11, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'uyuy', 2, NULL, '2025-10-22 09:11:50', 1),
(12, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'ioiouo', 2, NULL, '2025-10-22 09:12:55', 0),
(13, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'ioiouo', 2, NULL, '2025-10-22 09:12:58', 0),
(14, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'ioiouo', 2, NULL, '2025-10-22 09:12:58', 0),
(15, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', '....', 2, NULL, '2025-10-22 09:13:39', 0),
(16, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', '....', 2, NULL, '2025-10-22 09:13:45', 0),
(17, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', '....', 2, NULL, '2025-10-22 09:13:45', 0),
(18, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'hello', 2, NULL, '2025-10-23 07:00:48', 0),
(19, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'hello', 2, NULL, '2025-10-23 07:00:59', 0),
(20, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'hello', 2, NULL, '2025-10-23 07:00:59', 1),
(21, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'ji', 2, NULL, '2025-10-23 07:06:30', 1),
(22, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'ji', 2, NULL, '2025-10-23 07:06:33', 1),
(23, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'ji', 2, NULL, '2025-10-23 07:06:33', 1),
(24, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'hhhiii', 2, 6, '2025-10-23 07:44:44', 1),
(25, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'xxxxx', 2, 6, '2025-10-23 07:46:24', 1),
(26, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'nnnnn', 8, 6, '2025-10-23 07:58:10', 1),
(27, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'hilo', 2, 6, '2025-10-23 13:16:40', 1),
(28, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', '....hi', 2, 6, '2025-10-24 02:10:08', 0);

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
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`reset_id`, `email`, `token`, `expires_at`, `created_at`, `used`) VALUES
(9, 'ajmacaraig18@gmail.com', '526025', '2025-10-12 09:12:42', '2025-10-12 07:07:42', 1),
(14, 'ajmacaraig20@gmail.com', '233195', '2025-10-16 21:06:26', '2025-10-16 19:01:26', 1),
(15, 'ajmacaraig19@gmail.com', '188928', '2025-10-17 20:21:55', '2025-10-17 18:16:55', 1);

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
  `stock_quantity` int(11) NOT NULL DEFAULT 0 CHECK (`stock_quantity` >= 0),
  `unit` varchar(20) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `model_path` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0,
  `meshy_task_id` varchar(255) DEFAULT NULL,
  `model_url` varchar(255) DEFAULT NULL,
  `generated_model_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, `unit`, `image_path`, `model_path`, `created_by`, `created_at`, `updated_at`, `is_archived`, `meshy_task_id`, `model_url`, `generated_model_id`) VALUES
(1, 2, 'Roofing', 'Bubong', 1000.00, 75, 'piece', 'https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.ugc.ph%2Fproduct%2Fduratile%2F&psig=AOvVaw3X_uMdXyDZnuuECXufnzjQ&ust=1757939065451000&source=images&cd=vfe&opi=89978449&ved=0CBUQjRxqFwoTCOCD-L6f2I8DFQAAAAAdAAAAABAL', NULL, 8, '2025-09-14 12:25:05', '2025-09-14 15:43:06', 1, NULL, NULL, NULL),
(2, 1, 'Simple Structure', 'Tall structure', 300.00, 1, 'piece', 'uploads/products/1757905503_wp6786949.jpg', 'uploads/3dmodels/japanese_pagoda_tower.glb', 8, '2025-09-14 13:07:13', '2025-10-26 16:52:57', 0, NULL, NULL, NULL),
(5, 4, 'Tempered Window', 'Water Proof', 2400.00, 25, 'set', 'uploads/products/1757904798_images.jpg', NULL, 8, '2025-09-15 02:53:18', '2025-10-26 17:02:26', 0, NULL, NULL, NULL),
(6, 6, 'Cabinet', 'Water Proof', 600.00, 50, 'set', 'uploads/products/1757905716_1722505054150.jpeg', NULL, 8, '2025-09-15 03:08:36', '2025-10-26 14:57:26', 1, NULL, NULL, NULL),
(7, 5, 'glass', 'heat proof', 500.00, 3, 'set', 'uploads/products/1757983610_10619874-the-light-trails-on-the-modern-building-background-in-shanghai-china-.jpg', NULL, 8, '2025-09-16 00:46:50', '2025-10-26 14:57:19', 1, NULL, NULL, NULL),
(8, 1, 'Bungalow', 'good quality', 10000000.00, 67, 'set', 'uploads/products/1757983690_Infrastructure.jpg', NULL, 8, '2025-09-16 00:48:10', '2025-10-26 14:57:17', 1, NULL, NULL, NULL),
(9, 3, 'box', 'bakal', 800.00, 25, 'sqm', 'uploads/products/1757983757_images.jpg', NULL, 8, '2025-09-16 00:49:17', '2025-10-07 09:32:42', 1, NULL, NULL, NULL),
(10, 7, 'Doors', 'high quality', 10000.00, 100, 'piece', 'uploads/products/1757984677_1750499159122.jpeg', NULL, 8, '2025-09-16 01:04:37', '2025-09-18 07:17:02', 1, NULL, NULL, NULL),
(11, 1, 'Different kinds of construction', 'Limited products, must buy', 5000.00, 64, 'sad', 'uploads/products/1757985178_3124496.jpg', NULL, 8, '2025-09-16 01:12:58', '2025-09-16 07:39:51', 1, NULL, NULL, NULL),
(12, 5, 'Baso', 'sdsad', 400.00, 60, 'KG', NULL, NULL, 8, '2025-09-18 07:07:58', '2025-09-18 07:09:42', 1, NULL, NULL, NULL),
(13, 1, 'Ordinary House', 'Just an ordinary house design', 1500000.00, 5, 'set', 'uploads/products/1758984396_hiraganadakuon.gif', NULL, 8, '2025-09-27 14:46:36', '2025-09-27 17:19:58', 1, NULL, NULL, NULL),
(14, 5, 'Gate', '', 50000.00, 30, 'set', 'uploads/products/1759905769_purok 3.png', NULL, 8, '2025-10-08 06:42:49', '2025-10-26 14:57:13', 1, NULL, NULL, NULL),
(15, 5, 'Gate', '', 50000.00, 10, 'set', 'uploads/products/1759905994_purok 3.png', NULL, 8, '2025-10-08 06:46:34', '2025-10-08 06:48:49', 1, NULL, NULL, NULL),
(16, 5, 'Foam Insulation', '', 850.00, 5, 'piece', 'uploads/products/1761490082_foam insulation.jpg', '../uploads/3dmodels/model_68fe5004b2736.glb', 8, '2025-10-26 14:48:02', '2025-10-26 18:48:00', 0, '019a2163-e7c0-7c32-a31c-04c6f82cecfc', '../uploads/3dmodels/model_68fe5004b2736.glb', 4),
(17, 2, 'Flat Deck', '', 500.00, 19, 'piece', 'uploads/products/1761498892_flat deck.jpg', NULL, 8, '2025-10-26 17:14:52', '2025-10-26 17:19:20', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `id` int(11) NOT NULL,
  `inquiry_id` int(11) NOT NULL,
  `conversation_id` int(11) DEFAULT NULL,
  `related_inquiry_id` int(11) DEFAULT NULL,
  `related_product_id` int(11) DEFAULT NULL,
  `sender` enum('admin','client') NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `replies`
--

INSERT INTO `replies` (`id`, `inquiry_id`, `conversation_id`, `related_inquiry_id`, `related_product_id`, `sender`, `message`, `sent_at`, `is_read`) VALUES
(1, 1, NULL, NULL, NULL, 'client', 'Hello, I’d like to know the cost for a metal roof.', '2025-10-20 07:29:32', 0),
(2, 1, NULL, NULL, NULL, 'admin', 'Hi John! Thanks for reaching out. The price starts at ₱500 per sqm.', '2025-10-20 07:29:32', 1),
(3, 1, NULL, NULL, NULL, 'client', 'Okay great! Can I send my house measurements?', '2025-10-20 07:29:32', 0),
(4, 1, NULL, NULL, NULL, 'admin', 'Sure, please send them over so we can provide a full quotation.', '2025-10-20 07:29:32', 1),
(5, 7, NULL, NULL, NULL, 'admin', 'ok', '2025-10-22 08:34:28', 0),
(6, 11, NULL, NULL, NULL, 'admin', 'hjhj', '2025-10-22 09:13:59', 1),
(7, 11, NULL, NULL, NULL, 'admin', '[pppppppppppppp', '2025-10-22 09:14:09', 1),
(8, 24, 6, 24, 2, 'client', 'hhhiii', '2025-10-23 07:44:44', 0),
(9, 25, 6, 25, 2, 'client', 'xxxxx', '2025-10-23 07:46:24', 0),
(10, 25, NULL, NULL, NULL, 'admin', 'hello', '2025-10-23 07:53:19', 0),
(11, 26, 6, 26, 8, 'client', 'nnnnn', '2025-10-23 07:58:10', 0),
(12, 26, NULL, NULL, NULL, 'admin', 'hi', '2025-10-23 07:58:58', 0),
(13, 26, 6, 26, 8, 'admin', 'ok', '2025-10-23 08:00:30', 1),
(14, 26, 6, 26, 8, 'admin', 'hhh', '2025-10-23 08:00:46', 1),
(15, 27, 6, 27, 2, 'client', 'hilo', '2025-10-23 13:16:40', 0),
(16, 27, 6, 27, 2, 'admin', 'ok', '2025-10-24 02:09:10', 1),
(17, 28, 6, 28, 2, 'client', '....hi', '2025-10-24 02:10:08', 0);

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
(2, 'user', 'Regular user with limited access', '2025-09-10 10:16:40', '2025-09-12 11:32:34'),
(5, 'client', 'Regular customer with access to inquiry features', '2025-10-20 07:25:42', '2025-10-20 07:25:42');

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
(3, 8, 'Ajey', 'Linsangan', 'M', '2025-09-01', '09127312983', 'male', '10', 'Region X (Northern Mindanao)', '1018', 'Camiguin', '101802', 'Guinsiliban', '101802003', 'Cantaan', 'haha', '2025-09-12 11:45:37', '2025-10-08 06:15:57'),
(4, 9, 'haha', 'hah', 'hah', '2025-09-15', '080282', 'female', '17', 'Region IV-B (MIMAROPA)', '1752', 'Oriental Mindoro', '175210', 'Pola', '175210011', 'Malibago', 'haha', '2025-09-15 09:34:45', '2025-09-15 09:34:45'),
(5, 10, 'Alvin', 'Bayabos', 'S', '2025-01-14', '09871123213', 'male', '13', 'National Capital Region (NCR)', '1376', 'Ncr, Fourth District', '137603', 'City Of Muntinlupa', '137603002', 'Bayanan', 'bahay', '2025-09-16 11:56:11', '2025-10-15 06:08:57'),
(9, 15, 'John', 'Doe', NULL, NULL, '09171234567', 'male', NULL, NULL, NULL, NULL, NULL, 'Quezon City', NULL, NULL, NULL, '2025-10-20 07:26:36', '2025-10-20 07:26:36');

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
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `generated_3d_models`
--
ALTER TABLE `generated_3d_models`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_task_id` (`meshy_task_id`),
  ADD UNIQUE KEY `unique_image_hash` (`original_image_hash`),
  ADD KEY `fk_3dmodel_product` (`product_id`),
  ADD KEY `fk_3dmodel_creator` (`created_by`),
  ADD KEY `idx_status` (`generation_status`),
  ADD KEY `idx_image_hash` (`image_hash`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`);

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
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_product_generated_model` (`generated_model_id`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inquiry_id` (`inquiry_id`),
  ADD KEY `conversation_id` (`conversation_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `generated_3d_models`
--
ALTER TABLE `generated_3d_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE SET NULL;

--
-- Constraints for table `generated_3d_models`
--
ALTER TABLE `generated_3d_models`
  ADD CONSTRAINT `fk_3dmodel_creator` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_3dmodel_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL;

--
-- Constraints for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD CONSTRAINT `inquiries_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_ibfk_2` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`);

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
