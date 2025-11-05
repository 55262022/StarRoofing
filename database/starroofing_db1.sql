-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2025 at 04:53 PM
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
(7, 'ajmacaraig19@gmail.com', '$2y$10$D.i9PWZIlariiKk2sKfx8OSGVoWTxXCgwJH6VXvJu3D0X3aRtOhqi', 2, 'active', '2025-11-05 14:35:16', '2025-09-12 11:34:43', '2025-11-05 14:35:16'),
(8, 'ajmacaraig20@gmail.com', '$2y$10$0xiOQg8aTUFKO9g/vajF.u5fM.nD9vMHXLqLPLoCia/HEzwR8XqAe', 1, 'active', '2025-11-05 15:48:04', '2025-09-12 11:45:37', '2025-11-05 15:48:04'),
(9, 'ajmacaraig18@gmail.com', '$2y$10$TRYyLzGYJgBEC7JTpo5qD.8IOknPQ/Nhpa04gkhQmrHFe02.P4mLu', 2, 'active', '2025-10-08 06:52:34', '2025-09-15 09:34:45', '2025-10-12 07:29:51'),
(10, '57842022@holycross.edu.ph', '$2y$10$VbwIwOWnPmhDvFsPwccoaOBzujWxl9waRxoJJbhXxMZpWPIR/Mmlu', 2, 'active', NULL, '2025-09-16 11:56:11', '2025-09-16 11:59:57'),
(13, 'admin@gmail.com', '$2y$10$d7Cg4ccEJ1OLypxRhgg3rutDJYaVZwUrCcpzEv0vkIwH0Ddl.wp3a', 1, 'active', '2025-10-30 18:57:59', '2025-10-09 04:09:49', '2025-10-30 18:57:59'),
(15, 'client@example.com', '186474c1f2c2f735a54c2cf82ee8e87f2a5cd30940e280029363fecedfc5328c', NULL, 'active', NULL, '2025-10-20 07:26:25', '2025-10-20 07:26:25'),
(17, 'archieramirez@gmail.com', '$2y$10$Xk81bIAEzFiiBUsddcv3Ne3wtU.lP5tvqDVP53OaZVYxhkuOIZnlO', 3, 'active', '2025-11-04 08:00:37', '2025-11-01 06:25:05', '2025-11-04 08:00:37'),
(18, 'docenajoshua28@gmail.com', '$2y$10$Xk81bIAEzFiiBUsddcv3Ne3wtU.lP5tvqDVP53OaZVYxhkuOIZnlO', 3, 'active', '2025-11-03 07:57:02', '2025-11-01 06:25:05', '2025-11-03 07:57:02'),
(19, 'ajmacaraig1827@gmail.com', '$2y$10$PpXJxHYxsJy0I5HxqTg1Ze5YYZLPhVnxsITXw22J16LBPIqY/Qq8C', 3, 'active', '2025-11-05 09:27:49', '2025-11-01 10:13:20', '2025-11-05 09:27:49'),
(21, '66652022@holycross.edu.ph', '$2y$10$x5WL7KDsmO.c8lclg81WueNHOr78JexTFEePwHClAJA.7GgIW12B6', 3, 'active', NULL, '2025-11-03 07:03:15', '2025-11-03 07:03:15'),
(22, 'jacamile@gmail.com', '$2y$10$K3Sf/Ed2YP97YyOl0IoDteYcgcKN/9zIq.08FyWI5Sc/60SilTz3i', 3, 'active', NULL, '2025-11-03 08:04:46', '2025-11-03 08:04:46');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `account_id`, `product_id`, `color`, `size`, `quantity`, `created_at`, `updated_at`) VALUES
(8, 7, 16, 'Silver', 'Small', 1, '2025-11-04 06:57:21', NULL),
(9, 7, 16, 'Gray', 'Medium', 1, '2025-11-04 06:57:42', NULL);

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
(7, 'doors', 'Combi/Blind & Roll Up Doors', 'Various door types', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(8, 'accessories', 'Accessories', 'Various accessories and miscellaneous items for construction and roofing', '2025-11-01 11:38:48', '2025-11-01 11:38:48');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_inquiries`
--

CREATE TABLE `chatbot_inquiries` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `conversation_id` int(11) DEFAULT NULL,
  `is_converted` tinyint(1) DEFAULT 0,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_inquiries`
--

INSERT INTO `chatbot_inquiries` (`id`, `firstname`, `lastname`, `email`, `message`, `conversation_id`, `is_converted`, `submitted_at`) VALUES
(1, 'Joshua', 'Santos', 'docenajoshua28@gmail.com', 'hello', 7, 1, '2025-10-28 11:16:03'),
(2, 'Joshua', 'Santos', 'docenajoshua28@gmail.com', 'HELLO', 7, 1, '2025-10-28 12:03:57'),
(3, 'Dion', 'Diego', 'markdiego073@gmail.com', 'i want to avail', 8, 1, '2025-10-28 12:09:37'),
(4, 'Dion', 'Diego', 'markdiego073@gmail.com', 'hi', 8, 1, '2025-10-28 12:09:45'),
(5, 'Dion', 'Diego', 'markdiego073@gmail.com', 'hello', 8, 1, '2025-10-28 12:10:08'),
(6, 'AJ', 'Linsangan', 'ajmacaraig1827@gmail.com', 'request', 9, 1, '2025-10-28 12:36:59'),
(7, 'AJ', 'Linsangan', 'ajmacaraig1827@gmail.com', 'hi', 9, 1, '2025-10-28 12:37:19'),
(8, 'ariel', 'macaraig', 'ajeylinsangan@gmail.com', 'shopping]', 10, 1, '2025-10-28 12:44:19'),
(9, 'ariel', 'macaraig', 'ajeylinsangan@gmail.com', 'hello', 10, 1, '2025-10-28 12:45:35'),
(10, 'ariel', 'macaraig', 'ajeylinsangan@gmail.com', 'hi', 10, 1, '2025-10-28 12:52:20'),
(11, 'AJ', 'Macaraig', 'ajeylinsangan@gmail.com', 'hello', 10, 1, '2025-10-28 12:54:36'),
(12, 'Josh', 'Santos', 'ajmacaraig1827@gmail.com', 'testing', 9, 1, '2025-10-28 13:03:45'),
(13, 'Josh', 'Santos', 'ajmacaraig1827@gmail.com', 'hi', 9, 1, '2025-10-28 13:04:25'),
(14, 'aj', 'lin', 'ajmacaraig19@gmail.com', 'hi', 6, 1, '2025-10-28 13:18:40'),
(15, 'aj', 'lin', 'ajmacaraig19@gmail.com', 'hello', 6, 1, '2025-10-29 02:23:08'),
(16, 'aj', 'lin', 'ajmacaraig19@gmail.com', 'hi', 6, 1, '2025-10-29 14:20:29'),
(17, 'aj', 'lin', 'ajmacaraig19@gmail.com', 'okkokokokokokkokoko', 6, 1, '2025-10-30 09:38:30');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `is_accepted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `unread_count` int(11) DEFAULT 0,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `last_message_sender` enum('admin','client') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `email`, `is_accepted`, `created_at`, `updated_at`, `unread_count`, `last_message_at`, `last_message_sender`) VALUES
(6, 'ajmacaraig19@gmail.com', 1, '2025-10-23 07:44:44', '2025-11-03 02:45:42', 0, NULL, NULL),
(7, 'docenajoshua28@gmail.com', 1, '2025-10-28 11:16:03', '2025-10-28 11:16:25', 0, NULL, NULL),
(8, 'markdiego073@gmail.com', 1, '2025-10-28 12:09:37', '2025-10-28 12:36:02', 0, NULL, NULL),
(9, 'ajmacaraig1827@gmail.com', 1, '2025-10-28 12:36:59', '2025-10-28 13:04:53', 0, NULL, NULL),
(10, 'ajeylinsangan@gmail.com', 1, '2025-10-28 12:44:19', '2025-10-28 13:02:52', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(50) NOT NULL,
  `hire_date` date NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `account_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `department`, `hire_date`, `image_path`, `created_at`, `updated_at`, `is_archived`) VALUES
(1, 17, 'Archie', 'Ramirez', 'archieramirez@gmail.com', NULL, '09283762182', 'Logistics and Services', '2025-09-17', 'uploads/employees/68cff607c1937.jpg', '2025-09-21 12:56:39', '2025-11-03 08:06:20', 0),
(2, 18, 'Joshua', 'Docena', 'docenajoshua28@gmail.com', NULL, '09231732198', 'Logistics and Services', '2025-09-27', 'uploads/employees/68d8299b2fef9.png', '2025-09-27 18:14:51', '2025-11-03 08:06:12', 0),
(3, 19, 'Arieljon', 'Linsangan', 'ajmacaraig1827@gmail.com', NULL, '09123721873', 'Logistics and Services', '2025-11-01', NULL, '2025-11-01 10:13:20', '2025-11-04 04:19:40', 0),
(5, 21, 'Jerthel', 'Sanglay', '66652022@holycross.edu.ph', '$2y$10$x5WL7KDsmO.c8lclg81WueNHOr78JexTFEePwHClAJA.7GgIW12B6', '09181727381', 'Administration', '2025-11-03', NULL, '2025-11-03 07:03:15', '2025-11-03 07:04:53', 0),
(6, 22, 'Christian', 'Jacamile', 'jacamile@gmail.com', '$2y$10$K3Sf/Ed2YP97YyOl0IoDteYcgcKN/9zIq.08FyWI5Sc/60SilTz3i', '09127321983', 'Logistics and Services', '2025-11-03', NULL, '2025-11-03 08:04:46', '2025-11-03 08:04:46', 0);

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
(1, NULL, '019a174c-c43f-78ea-9fd2-2b8579746ac2', 'c9560bbf6796ed9b0d0fa6fa0ed46602', 'image-18.png', NULL, 'model_019a174c-c43f-78ea-9fd2-2b8579746ac2_1761328644_68fbbe0492632.glb', 'uploads/3dmodels/model_019a174c-c43f-78ea-9fd2-2b8579746ac2_1761328644_68fbbe0492632.glb', 'uploads/3dmodels/model_019a174c-c43f-78ea-9fd2-2b8579746ac2_1761328644_68fbbe0492632.glb', 6442324, 'succeeded', NULL, NULL, '2025-10-24 17:38:19', '2025-10-28 08:06:54'),
(2, NULL, '019a17e2-bde6-7b37-a231-e243255c5e71', '0a76f5b593e77358329f3a2d6d51e8fd', 'a48fcbacc4b04ca1a099e6ebc3df1b40.jpg', NULL, 'model_68fbe07c37346.glb', 'uploads/3dmodels/model_68fbe07c37346.glb', 'uploads/3dmodels/model_68fbe07c37346.glb', 7210324, 'succeeded', NULL, NULL, '2025-10-24 20:22:07', '2025-10-28 08:06:54'),
(3, NULL, '019a19f2-f895-786a-ad90-d85151ecf360', '7f1a41bb0027657cd3692a6e9191d1da', 'images.jpg', NULL, 'model_68fc67be914b7.glb', 'uploads/3dmodels/model_68fc67be914b7.glb', 'uploads/3dmodels/model_68fc67be914b7.glb', 9090820, 'succeeded', NULL, NULL, '2025-10-25 05:59:05', '2025-10-28 08:06:54'),
(4, 16, '019a2163-e7c0-7c32-a31c-04c6f82cecfc', '4c2ebc13fbb8e8cb565e3205d1502ccc', '1761490082_foam insulation.jpg', NULL, 'model_68fe5004b2736.glb', 'uploads/3dmodels/model_68fe5004b2736.glb', 'uploads/3dmodels/model_68fe5004b2736.glb', 8322188, 'succeeded', NULL, NULL, '2025-10-26 16:39:47', '2025-10-28 08:06:54'),
(5, 17, '019a29b0-b260-7760-a51b-06c4295186f4', 'b8e115f9a41e16af556e41954bd2e4da', '1761498892_flat deck.jpg', NULL, 'model_69006f6fc7373.glb', 'uploads/3dmodels/model_69006f6fc7373.glb', 'uploads/3dmodels/model_69006f6fc7373.glb', 7671728, 'succeeded', NULL, NULL, '2025-10-28 07:20:35', '2025-10-28 08:06:54'),
(6, 18, '019a3f75-8fb2-7f58-a1f3-be1e22b8ab8f', 'c515044d24461e15cdb225089b68df81', '1761997659_J bolt.jpg', NULL, 'model_690601e2c1f1a.glb', 'uploads/3dmodels/model_690601e2c1f1a.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a3f75-8fb2-7f58-a1f3-be1e22b8ab8f/output/model.glb?Expires=1762260572&Signature=aLlyeRW6n2VQ8ZnrLThoNLKean8JVx0dJniakkksgsq1zRYmit8gMy8mez3mQVZ0yH3UpmH8H4M1XINsGyH0ODd0sanpcTGggz6mH5ipodcIX-Bp5W0pLZtIaN0oVYzJo3fbZmc0z6ea4vvLX8Fm7iBxJCfu~J-ZTSrCPcRHiUKMmyqHYwyvhdVSHABVMejGxErTtC0R4xYoXqtlVkcvt~0RNsNhMavpJWmbf0BBlaK1T~HXIAgBmZzU-cJWV1UvsTDACFfs3E3~K89RxVaSzlpiQUGcSVDyj0eEi5NNWsj-t2n5Md3E8c30kXyd8FfYp0dZG~tv3oTuGJ6Mi9N1Ug__&Key-P', 8273536, 'succeeded', NULL, NULL, '2025-11-01 12:47:41', '2025-11-01 12:49:40'),
(7, NULL, '019a476e-1bab-77c8-b25b-8a90e9c54d3f', 'e3233816006af20ef605c292d2d83234', 'lumpia.jpg', NULL, 'model_69080cf145d19.glb', 'uploads/3dmodels/model_69080cf145d19.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a476e-1bab-77c8-b25b-8a90e9c54d3f/output/model.glb?Expires=1762394477&Signature=ozM1rq1fdVF2qw-NTPR4GGv8Mp-R9nxe0o8yNaYlQI1mFZGGtdbB7QRA3DdBa7DDQjzqIjp6j0LLgUS41AdRMQIuHiCvWOKvck2hHmPoINsbhIKhitSGgd8YipvKK9URlaG0fsqItEmauUP8Ma5sjZxc9xD8xyXy9RlZqfjC0ZRDzZH65J~e5vtXaHDaqg~JJhGTCOU99wDl~0v7DPgXS4237dBcUskc7WfMdmOWESaMPPPPU91IkjpRrnEDKdDq-8URY~waYbPURqx~ReziclNt7omW36XIiX59KkYMBzTCJq10CLJvq17e9jfQwlu5RjFZGCD8B8WSiGWj35pJ~w__&Key-P', 7970976, 'succeeded', NULL, NULL, '2025-11-03 01:56:30', '2025-11-03 02:02:29'),
(8, 24, '019a50ef-e19d-7cf2-bca9-992f8b999f34', 'c4e5431c37d5b6d46f32d9f466309c41', '1762291412_690a6ed4ad9ac.jpg', NULL, 'model_690a7b2f32504.glb', 'uploads/3dmodels/model_690a7b2f32504.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a50ef-e19d-7cf2-bca9-992f8b999f34/output/model.glb?Expires=1762553771&Signature=Q4HYmBk~z~Q5fy4kCTk0ufs0jKn39oz9K0JF5D2jnkVf8xTGvvJN2Ojkm4tIQWSUSLkm4DemaQVEfq1ZQzEVmZi5U57NYu6np9iDQ8pFWT6mglhgZbN5zmI4LWjrMfyrTeRsubXpvf011Fi5SrNeE1~5nUee5flVCcXniHbcKzsHjRUg3fC99oBqrzg5fkWe~vke0rxR6U9K9kFfEyyG8hOV7QT5UX8yoZDgACcN8-328lCQFF-baCg5hiOaz1tGFHosL-1O1aUcqvVxRHgeICG6dG1W3nEtQIGsU872GDNOuBkjIi8gewtbvDgTTvIFEWk05n4AqqLURoF6nOBs~g__&Key-P', 7959836, 'succeeded', NULL, NULL, '2025-11-04 22:14:49', '2025-11-04 22:16:16'),
(9, 27, '019a50fa-56de-7dd0-ad29-f82b015e43ce', '808f2cad7e99de155c4a5fb6df5e9a54', '1762293280_690a7620b7884.png', NULL, 'model_690a7e1b615a1.glb', 'uploads/3dmodels/model_690a7e1b615a1.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a50fa-56de-7dd0-ad29-f82b015e43ce/output/model.glb?Expires=1762554522&Signature=ZNsBc4FQIjg2D-SXNAZY81WIAFyyp54pX66b1q~P36V5Af5pdPkmN6UxzNZ26W84wrJjIO5nR953rZUr-jawi9lBv3LvNxVd~5br~Z3MvV9pyeNgQefceWbHI0Z5ha3A7mYa2kdGWL~ap8V5Vkp6aM~GI1ea8anHzzcoB-ERA4RQgOmtTxvz9h7FqUhnSQDRCWKLqQsWtksfhfSRu8jGpTfPoGB6fGPC2jpfqUx-jsFOwQjS2O0t6LioYNpqmqidX8A2X82DXSGG1s6oCVGNBHEwNIjUpYoy6e5at2fe2SISUYQvdV2PMBkNK3KIVwKNkGcl93nDzGB9r9wjXw~uDw__&Key-P', 7990516, 'succeeded', NULL, NULL, '2025-11-04 22:26:15', '2025-11-04 22:28:44'),
(10, 30, '019a5101-371b-7e50-9246-c3d947adb1d1', '821a9204590d13fa53cb3bdaabca5177', '1762293375_690a767f50ad1.png', NULL, 'model_690a7fe3d4790.glb', 'uploads/3dmodels/model_690a7fe3d4790.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a5101-371b-7e50-9246-c3d947adb1d1/output/model.glb?Expires=1762554978&Signature=FdYXjGAwz4tQfvKG1b6DpkRe9gdcvyVWzgLgeKyAk896VqxshWryB9Qtq31qNDrmIMg0B1X3~lxGrHYVQzQ6TUHjsYbZr3Qga8xsWWzEUvxo8Fe87sydkum8abGHEpkAvPTVGrIt3Wheohy~0sLZTXgatsQz~4kHZfktd7sESSy1OMaUAod~EI9mI-pEunhEl9hf2jh2-4xLmq3z0Mmbo7tVk66EMILXsFkRnOvJ35pZg~RpxBCb3UH5U1j75p7qHPQa2Kd0UiQsedDpr-Tfk3b6G8NeWniYn2L75gX7clZdxulLk2AYSPe9IFvY0kEEP9KjUcwy6udp2xBm0owfIg__&Key-P', 8739576, 'succeeded', NULL, NULL, '2025-11-04 22:33:45', '2025-11-04 22:36:21'),
(11, 28, '019a5116-ac1a-7e3d-b322-f7f6f3017467', '73c89729f22f69cc3349599be1b10395', '1762293315_690a7643b6d4d.png', NULL, 'model_690a85b30f1bb.glb', 'uploads/3dmodels/model_690a85b30f1bb.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a5116-ac1a-7e3d-b322-f7f6f3017467/output/model.glb?Expires=1762556327&Signature=EPAf6JDvHPk7mI3VCXK4fSBrErqNymzBWQR~WvVbtiWb-UOTgcSS0ribTJLWSW5bRkJvUh1c4yt73Mdw0GTsfA3v3kJKLftx5Igah9V5I5HuQBLMEYwGkdkkBjC2S9aQOnacKuy9slJ9mQ9NVt-d~qnNUyzkmPVkRbUUcxHMLvdUDI9plgt7T4SwKT~GqPSjLF18Tv-lVoYeOczFzn04Dnq8HaGHvuRzqi5yKsROVNezwgpC14iUPSyKuU8PHDUoak6Mwt2Mw3ceAi8G93T2WXwHW7f~LichURcS9zrcfsVUlW66SCjxbX5xyKo21hfSErBiI-a7RZwFLe8Bb-gd1w__&Key-P', 6622172, 'succeeded', NULL, NULL, '2025-11-04 22:57:11', '2025-11-04 23:01:07'),
(12, 29, '019a511a-921e-7f29-b5d2-9150c8f0c459', '17ceb1c4583c79867448ba7fa1014c3d', '1762293338_690a765a8cafa.png', NULL, 'model_690a8663c46c1.glb', 'uploads/3dmodels/model_690a8663c46c1.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a511a-921e-7f29-b5d2-9150c8f0c459/output/model.glb?Expires=1762556640&Signature=Z9-KcTLzSe7gWjB24hXxWjhkE8p9H9oCNG-cl0e70kXyrCWHoJ02ag86opQMLsDLFv4kz4KqRVcxIY~AfJpsd9knpGbMQX8UjRtQirLQU72W71nlvhK1isFlKse8M2~fybZzsi0H6uYQlbW~zkLxAsWjTMrvC2q~4XW0xYzQP2sGaFf-PW5J0Busv6P0YXGH11gHn6R9n7PIqJJq2Um-MLbHi~XJb1HLfxW9zv4YuRmqJhb4-UiV7t3e6P74P9k6Zu2eVpw7ZF4V7hJUBK~s9xBtGhkXgYjpxp~oppw-9R40L7kMQsTsCqOyPN5936F6pmn~7803WeTq3YazMAaxeQ__&Key-P', 5977068, 'succeeded', NULL, NULL, '2025-11-04 23:01:27', '2025-11-04 23:04:04'),
(13, 23, '019a511e-3126-7bfa-aa30-3de444607c6e', 'e2787deec78724917850a07ae9deac7b', '1762291313_690a6e71aaed8.png', NULL, 'model_690a874443f7e.glb', 'uploads/3dmodels/model_690a874443f7e.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a511e-3126-7bfa-aa30-3de444607c6e/output/model.glb?Expires=1762556864&Signature=F9vJl97jhlmg0WiltlHHaKyU1BEvjQJfmgKjERQ3oCZT4mGKupRDPk09cnw~Jft2r931J8qSonne4XkIdlpqWCYk41JnAa~qqCoDa34c2E4cjztMrlep5WSEAwnik4LgMZ8tt4VZ7ASKdHgTMHAAQOI1mLqovRY3hGSIiJCrPvjgcVmEVtUhaLXCD1mvI~Aq3O3~DDFPm9vUrxVeToeY~ugAuZBmw6wjTUuQqGnvYh-VrD-1ab8Z~a~EI7~TaLGZaIkWpz1rWjirOqMRvvhw1GdHtx1haNOSyiJAdDU56TJ3jQ-qVSKQTojZwhvW64G182KBT2mOJ5lCo4fnEJHNVw__&Key-P', 8373980, 'succeeded', NULL, NULL, '2025-11-04 23:05:24', '2025-11-04 23:07:48'),
(14, 26, '019a5128-8290-7230-9f59-7e700f74ee7d', '9143ad8f425b54b954a7ca8878b39884', '1762293218_690a75e23be5b.png', NULL, 'model_690a89d7b21c9.glb', 'uploads/3dmodels/model_690a89d7b21c9.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a5128-8290-7230-9f59-7e700f74ee7d/output/model.glb?Expires=1762557526&Signature=HfJIGJl~wc85VvM28CdbFbiFkhKMdPWgIX8flXDqu39iO8i9as6~XDjVV1X2p59Cp1rl1m5hoYVuuXsdXnyTaVsTx8OP-kYCWunogmnxGt9b-W~HQoHHlBL2lSG2jVHcVzqTxyQx7zDquHOScmiNDnM-~jm8IFnNJFJebpcwQ90KMybNIAfVaimxlPAOqGzOXgF8c-Kh5UCJT3RT~d6cNjg1LIWxTHRcpRoX~xu2qTdRlQfe-qqP6344n5Hzqb2oryvdqDDPExvIE13Ecc6WIxQNwVu0rnXNCoF~0gehiVzpWCUFKGOadwYY-rYNOGEavZelqyFq7f38jWNTg2L2xg__&Key-P', 7533064, 'succeeded', NULL, NULL, '2025-11-04 23:16:40', '2025-11-04 23:18:48'),
(15, 20, '019a512c-1201-7c0b-bec6-38d5a4b0c18c', '067a2b85554ca73ad3ea8eb54b901982', '1762291228_690a6e1cd85df.png', NULL, 'model_690a8b34ac3e6.glb', 'uploads/3dmodels/model_690a8b34ac3e6.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a512c-1201-7c0b-bec6-38d5a4b0c18c/output/model.glb?Expires=1762557871&Signature=hyA6m~NW7120KRgXn1uno3oC12Np14YFWEfxr4MT4L34rlmJ4v9unXhZgPYY333ZT7OCnW2D45HfK7UEJCFbtvUk1NiPJEDVryb70WnnZ30-RnUXtElQdjmfjWYwIGlyD2~l4W8kb2mB~JbTDKWX8dvm~AULRkjemM~ZJ6X3PfyV6j79zS~XRpCsnQnfuB05lIaiSr-OTaKkm0ptP1H2PyVwXKmi7SPBYg4sL6mD9z3Clu4AOPiwpd6XXSW4lZI-r8Z6qyDh2Ram5QCtGVkCnTiy68-qIBIu69lv~GJPNXf-9EHqLd2Kk~UfB31i7Et0JKCDz~GnUbG6hg8amsL-Qg__&Key-P', 6622724, 'succeeded', NULL, NULL, '2025-11-04 23:20:34', '2025-11-04 23:24:37'),
(16, 19, '019a5131-9136-72e6-9785-0466ad953ebe', 'af8a09f6fc2bca15d18637675d19ebb4', '1762291126_690a6db675104.png', NULL, 'model_690a8c2a10f32.glb', 'uploads/3dmodels/model_690a8c2a10f32.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a5131-9136-72e6-9785-0466ad953ebe/output/model.glb?Expires=1762558118&Signature=mRAmWKgg-w7N1-gT65RibAfkX16rxD0oNsckqxdPOKEwNSl11eh8Vnhr~lOYXS8Kd3soFU-CxfAeKK45ITsWJxM9b2zOyGFIMrrl90yAEMk7Q4QoAda5VLxM6~f0BUbXgy~fR3ydu1EUJXpUdPMh-ZuCPX4rDkD~VFxcpLABWcGGYCy4W6goepgw5N5CogHM-6j2q6WvLtrKqULV-Y2I2VWnqPj1IMTzCgkOBiKXCKWqJ1okB9hlbo39TB8aD~Um3m7dx~cvXn8rej5DDSmW7~KlSe3BPFvx74BwKR-~OdOC9LrC1p2GKowDarIJxxOpITLszVZqre~5egSyHsvFsA__&Key-P', 7156704, 'succeeded', NULL, NULL, '2025-11-04 23:26:34', '2025-11-04 23:28:42'),
(17, 25, '019a5140-5b1d-7f2c-ba1c-c8c96a185ce5', '602ff53785827a8f0cecbbcf997a384f', '1762291456_690a6f005fb3a.jpg', NULL, 'model_690a905d6380a.glb', 'uploads/3dmodels/model_690a905d6380a.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a5140-5b1d-7f2c-ba1c-c8c96a185ce5/output/model.glb?Expires=1762559196&Signature=Y4BiQHt9NRbZqxRr9wF8p180jUCX4R35N~AqryfqE0xXqfx79v1R43NVvub~mCNUERaRNwodWmx0x1GvqhJ9CJgCvqVjfz7N99wVNO~TELbeklhx~eIZ57nTTA3gCAcPFwTG1K7~XqH2pfwo1SMAnBp0jTCo72MzrIpNOzr1UkpRqOlsfbKIg2kQEZtqdjNBZICLL6xHvAUuSu0vwP4ofQKZ5Je9JqY1jn3eyLLlbmFbxeMMJ71eT13m~kV9qTIbL7pw2xWEBSCRU9Ym6yqMi-Gx1jkhKhBdEi5dv~MFjIFdeP3BO-TRJKxeN5uEKAQ4NWU0t8F0eA1ckVR1ljoJsQ__&Key-P', 7536304, 'succeeded', NULL, NULL, '2025-11-04 23:42:43', '2025-11-04 23:46:38'),
(18, 21, '019a54a6-35ed-70fa-a5dd-91e5f59f9187', '03046a1f7a4d55db0bdef3655fda3552', '1762291263_690a6e3fee600.png', NULL, NULL, NULL, NULL, NULL, 'failed', NULL, NULL, '2025-11-05 15:32:50', '2025-11-05 15:34:50'),
(19, 22, '019a54ab-dd91-71df-b5e4-a4c062e53f44', 'fa6dc1fce86e161db5ce1bdf4cea922b', '1762291288_690a6e582f38a.png', NULL, 'model_690b70239317e.glb', 'uploads/3dmodels/model_690b70239317e.glb', 'https://assets.meshy.ai/3b7a3bf4-2114-49b6-b91c-d1ee797186b9/tasks/019a54ab-dd91-71df-b5e4-a4c062e53f44/output/model.glb?Expires=1762616478&Signature=j5C6zLAgd6f6Nk8NKjEtp5NSbo3w4G3DvpIaS6ttItRIw8wwg5Nv5lXgGCDIV0PJ3~RV9qeoWKCKy17-Jv2MrRSHJw2JDO3YR7PsNMK5IQH9WrltyHj1ADh0IKDxTAs~Q2Xffb1SyRrS5aU-RtHdFbFMJwVCfOmHS3qHg00YRVQXPOJSGu7sRgoiCt--dvPzvvC~HAfHG4RmtOMu~U4y7mbcqy3wzFI547e4YIG42C5VoU-MSs0iu8WU~VLfxzbD7WhNdsT87ZHPsdbRWedm~mjAqvIY8GO7PToxtsgaQ2UG0Rt26lSA8d97hPTd35XxdZZwitMearzGoppmZKm4Ew__&Key-P', 8692940, 'succeeded', NULL, NULL, '2025-11-05 15:39:01', '2025-11-05 15:41:24');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
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
  `is_accepted` tinyint(1) DEFAULT 0,
  `source` enum('form','chatbot','phone','email','product') DEFAULT 'form'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`id`, `firstname`, `lastname`, `email`, `phone`, `region_code`, `region_name`, `province_code`, `province_name`, `city_code`, `city_name`, `barangay_code`, `barangay_name`, `street`, `message`, `product_id`, `conversation_id`, `submitted_at`, `is_accepted`, `source`) VALUES
(26, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'nnnnn', 8, 6, '2025-10-23 07:58:10', 1, 'product'),
(27, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', 'hilo', 2, 6, '2025-10-23 13:16:40', 1, 'product'),
(28, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', '....hi', 2, 6, '2025-10-24 02:10:08', 1, 'product'),
(29, 'Joshua', 'Santos', 'docenajoshua28@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'hello', NULL, 7, '2025-10-28 11:16:03', 1, 'chatbot'),
(30, 'Joshua', 'Santos', 'docenajoshua28@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'HELLO', NULL, 7, '2025-10-28 12:03:57', 1, 'chatbot'),
(31, 'Dion', 'Diego', 'markdiego073@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'i want to avail', NULL, 8, '2025-10-28 12:09:37', 1, 'chatbot'),
(32, 'Dion', 'Diego', 'markdiego073@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'hi', NULL, 8, '2025-10-28 12:09:45', 1, 'chatbot'),
(33, 'Dion', 'Diego', 'markdiego073@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'hello', NULL, 8, '2025-10-28 12:10:08', 1, 'chatbot'),
(34, 'AJ', 'Linsangan', 'ajmacaraig1827@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'request', NULL, 9, '2025-10-28 12:36:59', 1, 'chatbot'),
(35, 'AJ', 'Linsangan', 'ajmacaraig1827@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'hi', NULL, 9, '2025-10-28 12:37:19', 1, 'chatbot'),
(36, 'ariel', 'macaraig', 'ajeylinsangan@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'shopping]', NULL, 10, '2025-10-28 12:44:19', 1, 'chatbot'),
(37, 'ariel', 'macaraig', 'ajeylinsangan@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'hello', NULL, 10, '2025-10-28 12:45:35', 1, 'chatbot'),
(38, 'ariel', 'macaraig', 'ajeylinsangan@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'hi', NULL, 10, '2025-10-28 12:52:20', 1, 'chatbot'),
(39, 'AJ', 'Macaraig', 'ajeylinsangan@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'hello', NULL, 10, '2025-10-28 12:54:36', 1, 'chatbot'),
(40, 'Josh', 'Santos', 'ajmacaraig1827@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'testing', NULL, 9, '2025-10-28 13:03:45', 1, 'chatbot'),
(41, 'Josh', 'Santos', 'ajmacaraig1827@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'hi', NULL, 9, '2025-10-28 13:04:25', 1, 'chatbot'),
(42, 'aj', 'lin', 'ajmacaraig19@gmail.com', NULL, '', '', '', '', '', '', '', '', '', 'hi', NULL, 6, '2025-10-28 13:18:40', 1, 'chatbot'),
(43, 'aj', 'lin', 'ajmacaraig19@gmail.com', 'N/A', '', '', '', '', '', '', '', '', '', 'hello', NULL, 6, '2025-10-29 02:23:08', 1, 'chatbot'),
(44, 'aj', 'lin', 'ajmacaraig19@gmail.com', 'N/A', '', '', '', '', '', '', '', '', '', 'hi', NULL, 6, '2025-10-29 14:20:29', 1, 'chatbot'),
(45, 'aj', 'lin', 'ajmacaraig19@gmail.com', 'N/A', '', '', '', '', '', '', '', '', '', 'okkokokokokokkokoko', NULL, 6, '2025-10-30 09:38:30', 1, 'chatbot'),
(46, 'aj', 'lin', 'ajmacaraig19@gmail.com', 'N/A', '', '', '', '', '', '', '', '', '', 'hi', NULL, 6, '2025-10-31 06:02:40', 1, 'chatbot'),
(47, 'ARIELJON', 'LINSANGAN', 'ajmacaraig19@gmail.com', 'N/A', '', '', '', '', '', '', '', '', '', 'uihg', NULL, 6, '2025-11-03 02:13:19', 1, 'chatbot');

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
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `account_id` int(11) NOT NULL,
  `customer_first_name` varchar(100) NOT NULL,
  `customer_last_name` varchar(100) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `address_id` int(11) NOT NULL,
  `delivery_street` text DEFAULT NULL,
  `delivery_barangay` varchar(100) DEFAULT NULL,
  `delivery_city` varchar(100) DEFAULT NULL,
  `delivery_province` varchar(100) DEFAULT NULL,
  `delivery_region` varchar(100) DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 150.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cod','gcash','paymaya','card','grab_pay','bank') NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `delivery_proof` varchar(255) DEFAULT NULL,
  `order_status` enum('pending','confirmed','to_ship','delivered','cancelled') DEFAULT 'pending',
  `assigned_employee_id` int(11) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_number`, `account_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `address_id`, `delivery_street`, `delivery_barangay`, `delivery_city`, `delivery_province`, `delivery_region`, `delivery_notes`, `product_id`, `product_name`, `product_price`, `quantity`, `subtotal`, `delivery_fee`, `total_amount`, `payment_method`, `payment_status`, `payment_intent_id`, `payment_proof`, `delivery_proof`, `order_status`, `assigned_employee_id`, `assigned_at`, `expected_delivery_date`, `created_at`, `updated_at`, `confirmed_at`, `delivered_at`) VALUES
(9, 'ORD202511020001', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 6, '', 'La Fuente', 'Santa Rosa', 'Nueva Ecija', 'Central Luzon', '', 18, 'J bolt', 50.00, 5, 250.00, 150.00, 400.00, 'cod', 'paid', NULL, NULL, 'delivery_proof_9_1762074793.jpg', 'delivered', 3, NULL, NULL, '2025-11-02 08:14:18', '2025-11-02 09:13:13', '2025-11-02 08:14:47', '2025-11-02 09:13:13'),
(10, 'ORD202511020002', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 6, '', 'La Fuente', 'Santa Rosa', 'Nueva Ecija', 'Central Luzon', '', 17, 'Flat Deck', 500.00, 1, 500.00, 150.00, 650.00, 'cod', 'pending', NULL, NULL, NULL, '', 1, NULL, NULL, '2025-11-02 09:20:19', '2025-11-03 06:34:14', '2025-11-02 14:24:11', NULL),
(11, 'ORD202511020003', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 6, '', 'La Fuente', 'Santa Rosa', 'Nueva Ecija', 'Central Luzon', '', 17, 'Flat Deck', 500.00, 1, 500.00, 150.00, 650.00, 'cod', 'pending', NULL, NULL, NULL, '', 2, NULL, NULL, '2025-11-02 09:21:05', '2025-11-03 06:34:06', '2025-11-02 09:46:16', NULL),
(13, 'ORD202511020005', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 7, '', 'Chanarian', 'Basco', 'Batanes', 'Cagayan Valley', '', 2, 'Simple Structure', 300.00, 1, 300.00, 150.00, 450.00, 'cod', 'paid', NULL, NULL, 'delivery_proof_13_1762244477_ec89ea2516b6d10e.jpg', 'delivered', 3, '2025-11-04 04:19:51', NULL, '2025-11-02 09:21:50', '2025-11-04 08:21:17', '2025-11-02 09:44:21', '2025-11-04 08:21:17'),
(14, 'ORD202511020006', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 1, 'bahay', 'Uguis', 'Nueva Era', 'Ilocos Norte', 'Ilocos Region', '', 17, 'Flat Deck', 500.00, 1, 500.00, 150.00, 650.00, 'cod', 'pending', NULL, NULL, NULL, '', 1, NULL, NULL, '2025-11-02 14:31:05', '2025-11-03 02:24:53', '2025-11-03 02:24:09', NULL),
(15, 'ORD202511030001', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 7, '', 'Chanarian', 'Basco', 'Batanes', 'Cagayan Valley', 'lkjn', 17, 'Flat Deck', 500.00, 1, 500.00, 150.00, 650.00, 'cod', 'paid', NULL, NULL, 'delivery_proof_15_1762136585.jpg', 'delivered', 1, NULL, NULL, '2025-11-03 02:18:55', '2025-11-03 02:23:05', '2025-11-03 02:21:56', '2025-11-03 02:23:05'),
(16, 'ORD202511030002', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 6, '', 'La Fuente', 'Santa Rosa', 'Nueva Ecija', 'Central Luzon', '', 17, 'Flat Deck', 500.00, 1, 500.00, 150.00, 650.00, 'cod', 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, '2025-11-03 11:18:32', '2025-11-03 11:18:32', NULL, NULL),
(17, 'ORD202511030003', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 6, '', 'La Fuente', 'Santa Rosa', 'Nueva Ecija', 'Central Luzon', '', 17, 'Flat Deck', 500.00, 1, 500.00, 150.00, 650.00, 'cod', 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, '2025-11-03 11:20:04', '2025-11-03 11:20:04', NULL, NULL),
(18, 'ORD202511030004', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 6, '', 'La Fuente', 'Santa Rosa', 'Nueva Ecija', 'Central Luzon', '', 17, 'Flat Deck', 500.00, 1, 500.00, 150.00, 650.00, 'cod', 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, '2025-11-03 11:22:01', '2025-11-03 11:22:01', NULL, NULL),
(19, 'ORD202511030005', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 6, '', 'La Fuente', 'Santa Rosa', 'Nueva Ecija', 'Central Luzon', '', 16, 'Foam Insulation', 850.00, 1, 850.00, 150.00, 1000.00, 'cod', 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, '2025-11-03 11:22:24', '2025-11-03 11:22:24', NULL, NULL),
(20, 'ORD202511030006', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 1, 'bahay', 'Uguis', 'Nueva Era', 'Ilocos Norte', 'Ilocos Region', '', 5, 'Tempered Window', 2400.00, 1, 2400.00, 150.00, 2550.00, 'cod', 'pending', NULL, NULL, NULL, 'confirmed', NULL, NULL, NULL, '2025-11-03 11:22:46', '2025-11-05 09:24:40', '2025-11-05 09:24:40', NULL),
(21, 'ORD202511030007', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 7, '', 'Chanarian', 'Basco', 'Batanes', 'Cagayan Valley', '', 5, 'Tempered Window', 2400.00, 1, 2400.00, 150.00, 2550.00, 'cod', 'pending', NULL, NULL, NULL, 'cancelled', NULL, NULL, NULL, '2025-11-03 11:25:05', '2025-11-05 00:03:18', NULL, NULL),
(22, 'ORD202511030008', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 6, '', 'La Fuente', 'Santa Rosa', 'Nueva Ecija', 'Central Luzon', '', 17, 'Flat Deck', 500.00, 1, 500.00, 150.00, 650.00, 'cod', 'paid', NULL, NULL, 'delivery_proof_22_1762335514_df92d0e1e78a01dd.jpg', 'delivered', 3, '2025-11-05 00:09:11', NULL, '2025-11-03 15:43:30', '2025-11-05 09:38:34', '2025-11-05 00:02:35', '2025-11-05 09:38:34'),
(23, 'ORD202511040001', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 6, '', 'La Fuente', 'Santa Rosa', 'Nueva Ecija', 'Central Luzon', '', 16, 'Foam Insulation', 850.00, 1, 850.00, 150.00, 1000.00, 'cod', 'paid', NULL, NULL, 'delivery_proof_23_1762335486_ee8cd5a2e0028fc1.jpg', 'delivered', 3, '2025-11-04 09:13:43', NULL, '2025-11-04 06:50:10', '2025-11-05 09:38:06', '2025-11-04 09:08:34', '2025-11-05 09:38:06'),
(24, 'ORD202511040002', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 6, '', 'La Fuente', 'Santa Rosa', 'Nueva Ecija', 'Central Luzon', '', 16, 'Foam Insulation', 850.00, 1, 850.00, 150.00, 1000.00, 'cod', 'paid', NULL, NULL, 'delivery_proof_24_1762244276_d81ae29bad5322a7.jpg', 'delivered', 1, '2025-11-04 07:54:41', NULL, '2025-11-04 06:55:42', '2025-11-04 08:17:56', '2025-11-04 07:54:28', '2025-11-04 08:17:56'),
(25, 'ORD202511040003', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 1, 'bahay', 'Uguis', 'Nueva Era', 'Ilocos Norte', 'Ilocos Region', '', 17, 'Flat Deck', 500.00, 10, 5000.00, 150.00, 5150.00, 'cod', 'paid', NULL, NULL, 'delivery_proof_25_1762244263_357e99c20410fcc9.jpg', 'delivered', 1, '2025-11-04 07:58:58', NULL, '2025-11-04 07:58:16', '2025-11-04 08:17:43', '2025-11-04 07:58:36', '2025-11-04 08:17:43'),
(26, 'ORD202511040004', 7, 'aj', 'lin', 'ajmacaraig19@gmail.com', '091287382173721', 6, '', 'La Fuente', 'Santa Rosa', 'Nueva Ecija', 'Central Luzon', '', 17, 'Flat Deck', 500.00, 1, 500.00, 150.00, 650.00, 'cod', 'paid', NULL, NULL, 'delivery_proof_26_1762335374_140a487f2b3273bf.jpg', 'delivered', 3, '2025-11-04 09:10:18', '2025-11-14', '2025-11-04 09:07:00', '2025-11-05 09:36:14', '2025-11-04 09:07:55', '2025-11-05 09:36:14');

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','to_ship','delivered','cancelled') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `notes`, `created_by`, `created_at`) VALUES
(37, 9, 'pending', 'Order placed', NULL, '2025-11-02 08:14:18'),
(38, 9, 'confirmed', 'Status updated from Pending to Confirmed', 8, '2025-11-02 08:14:47'),
(39, 9, '', 'Status updated from Confirmed to Processing', 8, '2025-11-02 08:17:27'),
(40, 9, '', 'Order assigned to employee: Arieljon Linsangan', 8, '2025-11-02 08:46:00'),
(41, 9, '', 'Order shipped and assigned to delivery employee: Arieljon Linsangan', 8, '2025-11-02 08:59:01'),
(42, 9, 'delivered', 'Order delivered with proof of delivery', 19, '2025-11-02 09:13:13'),
(43, 10, 'pending', 'Order placed', NULL, '2025-11-02 09:20:19'),
(44, 11, 'pending', 'Order placed', NULL, '2025-11-02 09:21:05'),
(46, 13, 'pending', 'Order placed', NULL, '2025-11-02 09:21:50'),
(47, 13, 'confirmed', 'Status updated from Pending to Confirmed', 8, '2025-11-02 09:44:21'),
(50, 11, 'confirmed', 'Status updated from Pending to Confirmed by admin', 8, '2025-11-02 09:46:16'),
(51, 11, '', 'Status updated from Confirmed to Processing by admin', 8, '2025-11-02 09:46:20'),
(54, 10, 'confirmed', 'Status updated from Pending to Confirmed by admin', 8, '2025-11-02 14:24:11'),
(55, 10, '', 'Status updated from Confirmed to Processing by admin', 8, '2025-11-02 14:24:27'),
(56, 14, 'pending', 'Order placed', NULL, '2025-11-02 14:31:05'),
(57, 15, 'pending', 'Order placed', NULL, '2025-11-03 02:18:55'),
(58, 15, 'confirmed', 'Status updated from Pending to Confirmed by admin', 8, '2025-11-03 02:21:56'),
(59, 15, '', 'Status updated from Confirmed to Processing by admin', 8, '2025-11-03 02:22:24'),
(60, 15, '', 'Order shipped and assigned to delivery employee: archie ramirez | Delivery Instructions: sdf', 8, '2025-11-03 02:22:32'),
(61, 15, 'delivered', 'Order delivered with proof of delivery', 17, '2025-11-03 02:23:05'),
(62, 14, 'confirmed', 'Status updated from Pending to Confirmed by admin', 8, '2025-11-03 02:24:09'),
(63, 14, '', 'Status updated from Confirmed to Processing by admin', 8, '2025-11-03 02:24:14'),
(64, 14, '', 'Order shipped and assigned to delivery employee: archie ramirez | Delivery Instructions: Archie, palagay nalang sa harap ng bahay', 8, '2025-11-03 02:24:53'),
(65, 11, '', 'Order shipped and assigned to delivery employee: Joshua Docena', 8, '2025-11-03 06:34:06'),
(66, 10, '', 'Order shipped and assigned to delivery employee: archie ramirez', 8, '2025-11-03 06:34:14'),
(67, 13, 'to_ship', 'Order ready to ship. Assigned to delivery employee: Joshua Docena', 8, '2025-11-03 08:00:49'),
(68, 16, 'pending', 'Order placed', NULL, '2025-11-03 11:18:32'),
(69, 17, 'pending', 'Order placed', NULL, '2025-11-03 11:20:04'),
(70, 18, 'pending', 'Order placed', NULL, '2025-11-03 11:22:01'),
(71, 19, 'pending', 'Order placed', NULL, '2025-11-03 11:22:24'),
(72, 20, 'pending', 'Order placed', NULL, '2025-11-03 11:22:46'),
(73, 21, 'pending', 'Order placed', NULL, '2025-11-03 11:25:05'),
(74, 22, 'pending', 'Order placed', NULL, '2025-11-03 15:43:30'),
(75, 13, 'to_ship', 'Order ready to ship. Assigned to delivery employee: Arieljon Linsangan', 8, '2025-11-04 04:19:51'),
(76, 23, 'pending', 'Order placed', NULL, '2025-11-04 06:50:10'),
(77, 24, 'pending', 'Order placed', NULL, '2025-11-04 06:55:42'),
(78, 24, 'confirmed', 'Status updated from Pending to Confirmed by admin', 8, '2025-11-04 07:54:28'),
(79, 24, 'to_ship', 'Order ready to ship. Assigned to delivery employee: Archie Ramirez | Delivery Instructions: werwre', 8, '2025-11-04 07:54:41'),
(80, 25, 'pending', 'Order placed', NULL, '2025-11-04 07:58:16'),
(81, 25, 'confirmed', 'Status updated from Pending to Confirmed by admin', 8, '2025-11-04 07:58:36'),
(82, 25, 'to_ship', 'Order ready to ship. Assigned to delivery employee: Archie Ramirez | Delivery Instructions: hhhhhhhaaaaaaaaa', 8, '2025-11-04 07:58:58'),
(83, 25, 'delivered', 'Order delivered by Archie Ramirez with proof of delivery uploaded.', 17, '2025-11-04 08:17:43'),
(84, 24, 'delivered', 'Order delivered by Archie Ramirez with proof of delivery uploaded.', 17, '2025-11-04 08:17:56'),
(85, 13, 'delivered', 'Order delivered by Arieljon Linsangan with proof of delivery uploaded.', 19, '2025-11-04 08:21:17'),
(86, 26, 'pending', 'Order placed', NULL, '2025-11-04 09:07:00'),
(87, 26, 'confirmed', 'Status updated from Pending to Confirmed by admin', 8, '2025-11-04 09:07:55'),
(88, 23, 'confirmed', 'Status updated from Pending to Confirmed by admin', 8, '2025-11-04 09:08:34'),
(89, 26, 'to_ship', 'Order ready to ship. Assigned to delivery employee: Arieljon Linsangan', 8, '2025-11-04 09:10:18'),
(90, 23, 'to_ship', 'Order ready to ship. Assigned to delivery employee: Archie Ramirez', 8, '2025-11-04 09:12:40'),
(91, 23, 'to_ship', 'Order ready to ship. Assigned to delivery employee: Arieljon Linsangan', 8, '2025-11-04 09:13:44'),
(92, 22, 'confirmed', 'Order confirmed by admin', 8, '2025-11-05 00:02:35'),
(93, 21, 'cancelled', 'Order declined/cancelled by admin', 8, '2025-11-05 00:03:18'),
(94, 22, 'to_ship', 'Order ready to ship. Assigned to delivery employee: Arieljon Linsangan', 8, '2025-11-05 00:09:11'),
(95, 26, 'to_ship', 'Expected delivery date set to: November 14, 2025', 8, '2025-11-05 00:12:05'),
(96, 20, 'confirmed', 'Order confirmed by admin', 8, '2025-11-05 09:24:40'),
(97, 26, 'delivered', 'Order delivered by Arieljon Linsangan with proof of delivery uploaded.', 19, '2025-11-05 09:36:14'),
(98, 23, 'delivered', 'Order delivered by Arieljon Linsangan with proof of delivery uploaded.', 19, '2025-11-05 09:38:06'),
(99, 22, 'delivered', 'Order delivered by Arieljon Linsangan with proof of delivery uploaded.', 19, '2025-11-05 09:38:34');

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
(16, 'ajmacaraig19@gmail.com', '697282', '2025-11-04 08:30:58', '2025-11-04 07:25:58', 1);

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
(2, 1, 'Simple Structure', 'Tall structure', 300.00, 100, 'piece', 'uploads/products/1757905503_wp6786949.jpg', 'uploads/3dmodels/japanese_pagoda_tower.glb', 8, '2025-09-14 13:07:13', '2025-11-04 07:30:04', 0, NULL, NULL, NULL),
(5, 4, 'Tempered Window', 'Water Proof', 2400.00, 23, 'set', 'uploads/products/1757904798_images.jpg', NULL, 8, '2025-09-15 02:53:18', '2025-11-03 11:25:05', 0, NULL, NULL, NULL),
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
(16, 5, 'Foam Insulation', '', 850.00, 2, 'piece', 'uploads/products/1761490082_foam insulation.jpg', 'uploads/3dmodels/model_68fe5004b2736.glb', 8, '2025-10-26 14:48:02', '2025-11-04 06:55:42', 0, '019a2163-e7c0-7c32-a31c-04c6f82cecfc', 'uploads/3dmodels/model_68fe5004b2736.glb', 4),
(17, 2, 'Flat Deck', '', 500.00, 89, 'piece', 'uploads/products/1761498892_flat deck.jpg', 'uploads/3dmodels/model_69006f6fc7373.glb', 8, '2025-10-26 17:14:52', '2025-11-04 09:07:00', 0, '019a29b0-b260-7760-a51b-06c4295186f4', 'uploads/3dmodels/model_69006f6fc7373.glb', 5),
(18, 8, 'J bolt', '', 50.00, 100, 'piece', 'uploads/products/1761997659_J bolt.jpg', 'uploads/3dmodels/model_690601e2c1f1a.glb', 8, '2025-11-01 11:47:39', '2025-11-04 07:29:41', 0, '019a3f75-8fb2-7f58-a1f3-be1e22b8ab8f', 'uploads/3dmodels/model_690601e2c1f1a.glb', 6),
(19, 2, 'Alpha Twin Rib', '', 666.00, 200, 'm', 'uploads/products/1762291126_690a6db675104.png', 'uploads/3dmodels/model_690a8c2a10f32.glb', 8, '2025-11-04 21:18:46', '2025-11-04 23:28:42', 0, '019a5131-9136-72e6-9785-0466ad953ebe', 'uploads/3dmodels/model_690a8c2a10f32.glb', 16),
(20, 2, 'Alpha Corr', '', 666.00, 200, 'm', 'uploads/products/1762291228_690a6e1cd85df.png', 'uploads/3dmodels/model_690a8b34ac3e6.glb', 8, '2025-11-04 21:20:28', '2025-11-04 23:24:37', 0, '019a512c-1201-7c0b-bec6-38d5a4b0c18c', 'uploads/3dmodels/model_690a8b34ac3e6.glb', 15),
(21, 2, 'Alpha Milti Rib', '', 666.00, 200, 'm', 'uploads/products/1762291263_690a6e3fee600.png', NULL, 8, '2025-11-04 21:21:03', '2025-11-05 15:32:50', 0, '019a54a6-35ed-70fa-a5dd-91e5f59f9187', NULL, NULL),
(22, 2, 'Alpha Rib', '', 666.00, 200, 'm', 'uploads/products/1762291288_690a6e582f38a.png', 'uploads/3dmodels/model_690b70239317e.glb', 8, '2025-11-04 21:21:28', '2025-11-05 15:41:24', 0, '019a54ab-dd91-71df-b5e4-a4c062e53f44', 'uploads/3dmodels/model_690b70239317e.glb', 19),
(23, 2, 'Alpha Web Deck', '', 666.00, 200, 'm', 'uploads/products/1762291313_690a6e71aaed8.png', 'uploads/3dmodels/model_690a874443f7e.glb', 8, '2025-11-04 21:21:53', '2025-11-04 23:07:48', 0, '019a511e-3126-7bfa-aa30-3de444607c6e', 'uploads/3dmodels/model_690a874443f7e.glb', 13),
(24, 8, 'Blind Rivets', '', 100.00, 200, 'pair', 'uploads/products/1762291412_690a6ed4ad9ac.jpg', 'uploads/3dmodels/model_690a7b2f32504.glb', 8, '2025-11-04 21:23:32', '2025-11-04 22:16:16', 0, '019a50ef-e19d-7cf2-bca9-992f8b999f34', 'uploads/3dmodels/model_690a7b2f32504.glb', 8),
(25, 8, 'C purlins', '', 400.00, 200, 'm', 'uploads/products/1762291456_690a6f005fb3a.jpg', 'uploads/3dmodels/model_690a905d6380a.glb', 8, '2025-11-04 21:24:06', '2025-11-04 23:46:38', 0, '019a5140-5b1d-7f2c-ba1c-c8c96a185ce5', 'uploads/3dmodels/model_690a905d6380a.glb', 17),
(26, 2, 'Stone Coated Tile', '', 400.00, 222, 'm', 'uploads/products/1762293218_690a75e23be5b.png', 'uploads/3dmodels/model_690a89d7b21c9.glb', 8, '2025-11-04 21:53:38', '2025-11-04 23:18:48', 0, '019a5128-8290-7230-9f59-7e700f74ee7d', 'uploads/3dmodels/model_690a89d7b21c9.glb', 14),
(27, 4, 'Casement Window', '', 600.00, 222, 'piece', 'uploads/products/1762293280_690a7620b7884.png', 'uploads/3dmodels/model_690a7e1b615a1.glb', 8, '2025-11-04 21:54:40', '2025-11-04 22:28:44', 0, '019a50fa-56de-7dd0-ad29-f82b015e43ce', 'uploads/3dmodels/model_690a7e1b615a1.glb', 9),
(28, 4, 'Single Hung Window', '', 700.00, 222, 'piece', 'uploads/products/1762293315_690a7643b6d4d.png', 'uploads/3dmodels/model_690a85b30f1bb.glb', 8, '2025-11-04 21:55:15', '2025-11-04 23:01:07', 0, '019a5116-ac1a-7e3d-b322-f7f6f3017467', 'uploads/3dmodels/model_690a85b30f1bb.glb', 11),
(29, 4, 'Sliding Window', '', 400.00, 111, 'piece', 'uploads/products/1762293338_690a765a8cafa.png', 'uploads/3dmodels/model_690a8663c46c1.glb', 8, '2025-11-04 21:55:38', '2025-11-04 23:04:04', 0, '019a511a-921e-7f29-b5d2-9150c8f0c459', 'uploads/3dmodels/model_690a8663c46c1.glb', 12),
(30, 4, 'Twin Double Hung Window', '', 800.00, 211, 'piece', 'uploads/products/1762293375_690a767f50ad1.png', 'uploads/3dmodels/model_690a7fe3d4790.glb', 8, '2025-11-04 21:56:15', '2025-11-04 22:36:21', 0, '019a5101-371b-7e50-9246-c3d947adb1d1', 'uploads/3dmodels/model_690a7fe3d4790.glb', 10);

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
  `is_read` tinyint(1) DEFAULT 0,
  `status` enum('sent','delivered','read') DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `replies`
--

INSERT INTO `replies` (`id`, `inquiry_id`, `conversation_id`, `related_inquiry_id`, `related_product_id`, `sender`, `message`, `sent_at`, `is_read`, `status`) VALUES
(1, 1, NULL, NULL, NULL, 'client', 'Hello, I’d like to know the cost for a metal roof.', '2025-10-20 07:29:32', 0, 'sent'),
(2, 1, NULL, NULL, NULL, 'admin', 'Hi John! Thanks for reaching out. The price starts at ₱500 per sqm.', '2025-10-20 07:29:32', 1, 'sent'),
(3, 1, NULL, NULL, NULL, 'client', 'Okay great! Can I send my house measurements?', '2025-10-20 07:29:32', 0, 'sent'),
(4, 1, NULL, NULL, NULL, 'admin', 'Sure, please send them over so we can provide a full quotation.', '2025-10-20 07:29:32', 1, 'sent'),
(5, 7, NULL, NULL, NULL, 'admin', 'ok', '2025-10-22 08:34:28', 0, 'sent'),
(6, 11, NULL, NULL, NULL, 'admin', 'hjhj', '2025-10-22 09:13:59', 1, 'sent'),
(7, 11, NULL, NULL, NULL, 'admin', '[pppppppppppppp', '2025-10-22 09:14:09', 1, 'sent'),
(8, 24, 6, 24, 2, 'client', 'hhhiii', '2025-10-23 07:44:44', 1, 'sent'),
(9, 25, 6, 25, 2, 'client', 'xxxxx', '2025-10-23 07:46:24', 1, 'sent'),
(10, 25, NULL, NULL, NULL, 'admin', 'hello', '2025-10-23 07:53:19', 0, 'sent'),
(11, 26, 6, 26, 8, 'client', 'nnnnn', '2025-10-23 07:58:10', 1, 'sent'),
(12, 26, NULL, NULL, NULL, 'admin', 'hi', '2025-10-23 07:58:58', 0, 'sent'),
(13, 26, 6, 26, 8, 'admin', 'ok', '2025-10-23 08:00:30', 1, 'sent'),
(14, 26, 6, 26, 8, 'admin', 'hhh', '2025-10-23 08:00:46', 1, 'sent'),
(15, 27, 6, 27, 2, 'client', 'hilo', '2025-10-23 13:16:40', 1, 'sent'),
(16, 27, 6, 27, 2, 'admin', 'ok', '2025-10-24 02:09:10', 1, 'sent'),
(17, 28, 6, 28, 2, 'client', '....hi', '2025-10-24 02:10:08', 1, 'sent'),
(18, 28, 6, 28, 2, 'admin', 'hello', '2025-10-28 04:13:34', 1, 'sent'),
(19, 29, 7, 29, 0, 'admin', 'hi', '2025-10-28 11:16:32', 0, 'sent'),
(20, 29, 7, 29, 0, 'admin', 'who', '2025-10-28 11:17:41', 0, 'sent'),
(21, 30, 7, 30, 0, 'admin', 'hi', '2025-10-28 12:04:29', 0, 'sent'),
(22, 31, 8, 31, 0, 'admin', 'sure', '2025-10-28 12:10:29', 0, 'sent'),
(23, 33, 8, 33, 0, 'admin', 'hi', '2025-10-28 12:36:02', 0, 'sent'),
(24, 35, 9, 35, 0, 'admin', 'yo', '2025-10-28 12:43:17', 1, 'sent'),
(25, 36, 10, 36, 0, 'admin', 'hello', '2025-10-28 12:44:51', 0, 'sent'),
(26, 36, 10, 36, 0, 'admin', 'hii', '2025-10-28 12:52:44', 0, 'sent'),
(27, 39, 10, 39, 0, 'admin', 'hellooo', '2025-10-28 12:54:58', 0, 'sent'),
(28, 36, 10, 36, 0, 'admin', 'testing', '2025-10-28 13:02:52', 0, 'sent'),
(29, 41, 9, 41, 0, 'admin', 'hello', '2025-10-28 13:04:53', 1, 'sent'),
(30, 26, 6, NULL, NULL, 'client', 'testing', '2025-10-28 13:14:59', 1, 'sent'),
(31, 26, 6, NULL, NULL, 'client', 'hello', '2025-10-28 13:16:36', 1, 'sent'),
(32, 42, 6, 42, 0, 'admin', '...j', '2025-10-28 13:19:18', 1, 'sent'),
(33, 26, 6, NULL, NULL, 'client', 'client reply', '2025-10-28 13:19:52', 1, 'sent'),
(34, 26, 6, 26, 8, 'admin', 'admin', '2025-10-28 13:20:39', 1, 'sent'),
(35, 26, 6, NULL, NULL, 'client', '...ok', '2025-10-28 13:21:25', 1, 'sent'),
(36, 26, 6, NULL, NULL, 'client', 'testing', '2025-10-28 13:30:45', 1, 'sent'),
(37, 26, 6, NULL, NULL, 'client', 'mochi mochi', '2025-10-28 13:59:29', 1, 'sent'),
(38, 26, 6, 26, 8, 'admin', 'testing', '2025-10-28 13:59:36', 1, 'sent'),
(39, 26, 6, NULL, NULL, 'client', 'kumain ka na ba?', '2025-10-28 13:59:58', 1, 'sent'),
(40, 26, 6, 26, 8, 'admin', 'yes po', '2025-10-28 14:00:07', 1, 'sent'),
(41, 26, 6, NULL, NULL, 'client', 'eatwell', '2025-10-28 14:05:17', 1, 'sent'),
(42, 26, 6, 26, 8, 'admin', 'takecare', '2025-10-28 14:05:24', 1, 'sent'),
(43, 26, 6, NULL, NULL, 'client', 'hi', '2025-10-29 02:21:54', 1, 'sent'),
(44, 26, 6, 26, 8, 'admin', 'hello', '2025-10-29 02:22:04', 1, 'sent'),
(45, 26, 6, NULL, NULL, 'client', 'Hello po, how are you', '2025-10-29 03:00:45', 1, 'sent'),
(46, 43, 6, 43, 0, 'admin', 'okay lang naman', '2025-10-29 03:00:53', 1, 'sent'),
(47, 26, 6, NULL, NULL, 'client', 'ahh sige sige', '2025-10-29 03:01:02', 1, 'sent'),
(48, 43, 6, 43, 0, 'admin', 'bili na kayo', '2025-10-29 03:01:08', 1, 'sent'),
(49, 45, 6, 45, 0, 'admin', 'hi', '2025-10-31 04:21:14', 1, 'sent'),
(50, 26, 6, NULL, NULL, 'client', 'hello', '2025-10-31 04:21:21', 1, 'sent'),
(51, 46, 6, 46, NULL, 'client', 'hi', '2025-10-31 06:02:40', 1, 'sent'),
(52, 47, 6, 47, NULL, 'client', 'uihg', '2025-11-03 02:13:19', 1, 'sent'),
(53, 47, 6, 47, 0, 'admin', 'hello4', '2025-11-03 02:45:42', 1, 'sent');

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
(3, 'employee', 'Delivery and logistics personnel', '2025-10-31 16:35:20', '2025-10-31 16:38:08');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `address_label` varchar(50) NOT NULL,
  `street` text DEFAULT NULL,
  `barangay_code` varchar(10) DEFAULT NULL,
  `barangay_name` varchar(100) DEFAULT NULL,
  `city_code` varchar(10) DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL,
  `province_code` varchar(10) DEFAULT NULL,
  `province_name` varchar(100) DEFAULT NULL,
  `region_code` varchar(10) DEFAULT NULL,
  `region_name` varchar(100) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `account_id`, `address_label`, `street`, `barangay_code`, `barangay_name`, `city_code`, `city_name`, `province_code`, `province_name`, `region_code`, `region_name`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 7, 'Home', 'bahay', '124711015', 'Uguis', '124711', 'Nueva Era', '1247', 'Ilocos Norte', '12', 'Ilocos Region', 0, '2025-10-30 06:10:42', '2025-10-30 06:25:07'),
(2, 8, 'Home', 'haha', '101802003', 'Cantaan', '101802', 'Guinsiliban', '1018', 'Camiguin', '10', 'Region X (Northern Mindanao)', 1, '2025-10-30 06:10:42', '2025-10-30 06:10:42'),
(3, 9, 'Home', 'haha', '175210011', 'Malibago', '175210', 'Pola', '1752', 'Oriental Mindoro', '17', 'Region IV-B (MIMAROPA)', 1, '2025-10-30 06:10:42', '2025-10-30 06:10:42'),
(4, 10, 'Home', 'bahay', '137603002', 'Bayanan', '137603', 'City Of Muntinlupa', '1376', 'Ncr, Fourth District', '13', 'National Capital Region (NCR)', 1, '2025-10-30 06:10:42', '2025-10-30 06:10:42'),
(5, 15, 'Home', NULL, NULL, NULL, NULL, 'Quezon City', NULL, NULL, NULL, NULL, 1, '2025-10-30 06:10:42', '2025-10-30 06:10:42'),
(6, 7, 'Work', '', NULL, 'La Fuente', NULL, 'Santa Rosa', NULL, 'Nueva Ecija', NULL, 'Central Luzon', 1, '2025-10-30 06:24:47', '2025-11-03 02:15:09'),
(7, 7, 'office', '', NULL, 'Chanarian', NULL, 'Basco', NULL, 'Batanes', NULL, 'Cagayan Valley', 0, '2025-10-30 07:14:11', '2025-11-03 02:15:09');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `account_id`, `first_name`, `last_name`, `middle_name`, `birthdate`, `contact_number`, `gender`, `created_at`, `updated_at`) VALUES
(2, 7, 'aj', 'lin', 'mac', '2025-09-02', '091287382173721', 'male', '2025-09-12 11:34:43', '2025-09-12 11:43:27'),
(3, 8, 'Ajey', 'Linsangan', 'M', '2025-09-01', '09127312983', 'male', '2025-09-12 11:45:37', '2025-10-08 06:15:57'),
(4, 9, 'haha', 'hah', 'hah', '2025-09-15', '080282', 'female', '2025-09-15 09:34:45', '2025-09-15 09:34:45'),
(5, 10, 'Alvin', 'Bayabos', 'S', '2025-01-14', '09871123213', 'male', '2025-09-16 11:56:11', '2025-10-15 06:08:57'),
(9, 15, 'John', 'Doe', NULL, NULL, '09171234567', 'male', '2025-10-20 07:26:36', '2025-10-20 07:26:36');

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
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_cart_lookup` (`account_id`,`product_id`,`size`,`color`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_code` (`category_code`);

--
-- Indexes for table `chatbot_inquiries`
--
ALTER TABLE `chatbot_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_converted` (`is_converted`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_conversations_accepted` (`is_accepted`,`updated_at`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `account_id` (`account_id`);

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
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `idx_inquiries_accepted_submitted` (`is_accepted`,`submitted_at`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_account_id` (`account_id`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `assigned_employee_id` (`assigned_employee_id`),
  ADD KEY `idx_payment_intent` (`payment_intent_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`);

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
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `idx_replies_inquiry_conversation` (`inquiry_id`,`conversation_id`,`sent_at`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_account_id` (`account_id`),
  ADD KEY `idx_default` (`account_id`,`is_default`),
  ADD KEY `idx_account_default` (`account_id`,`is_default`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `chatbot_inquiries`
--
ALTER TABLE `chatbot_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `generated_3d_models`
--
ALTER TABLE `generated_3d_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `chatbot_inquiries`
--
ALTER TABLE `chatbot_inquiries`
  ADD CONSTRAINT `chatbot_inquiries_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`address_id`) REFERENCES `user_addresses` (`id`),
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

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
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
