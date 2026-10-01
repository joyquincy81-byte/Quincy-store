-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2026 at 06:37 PM
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
-- Database: `ecommerce_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_id` int(11) DEFAULT NULL,
  `type` enum('shipping','billing','business') DEFAULT 'shipping',
  `recipient_name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_line` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Cameroon',
  `postal_code` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `created_at`) VALUES
(1, NULL, 'user.register', 'users', 2, NULL, '{\"email\":\"joyquincy81@gmail.com\",\"role\":\"customer\"}', '::1', '2026-09-02 09:51:54'),
(2, NULL, 'user.register', 'users', 5, NULL, '{\"email\":\"queen@gmail.com\",\"role_id\":5}', '::1', '2026-09-04 15:50:46'),
(3, 8, 'user.register', 'users', 8, NULL, '{\"email\":\"customer@gmail.com\",\"role\":\"customer\"}', '::1', '2026-09-07 19:25:08'),
(4, NULL, 'user.register', 'users', 11, NULL, '{\"email\":\"supplier@gmail.com\",\"role\":\"supplier\"}', '::1', '2026-09-07 20:43:24'),
(5, NULL, 'user.register', 'users', 12, NULL, '{\"email\":\"supplier@gmail.com\",\"role\":\"supplier\"}', '::1', '2026-09-08 10:41:56'),
(6, 13, 'user.register', 'users', 13, NULL, '{\"email\":\"supplier@gmail.com\",\"role\":\"supplier\"}', '::1', '2026-09-08 10:48:37'),
(7, 14, 'user.register', 'users', 14, NULL, '{\"email\":\"joysupplier@gmail.com\",\"role\":\"supplier\"}', '::1', '2026-09-09 13:35:21'),
(8, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '::1', '2026-09-09 14:20:25'),
(9, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '::1', '2026-09-10 14:39:18'),
(10, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '::1', '2026-09-10 14:40:40'),
(11, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '::1', '2026-09-10 14:43:33'),
(12, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-10 14:43:34'),
(13, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '::1', '2026-09-10 14:43:34'),
(14, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '::1', '2026-09-10 14:43:34'),
(15, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '::1', '2026-09-10 14:43:35'),
(16, 27, 'user.login', 'users', 27, NULL, '{\"email\":\"logistics@quincystore.test\",\"role\":\"logistics\"}', '::1', '2026-09-10 14:43:35'),
(17, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '::1', '2026-09-10 14:48:17'),
(18, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-10 14:53:58'),
(19, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-10 14:58:19'),
(20, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '::1', '2026-09-10 15:00:41'),
(21, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '::1', '2026-09-10 15:21:38'),
(22, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-10 15:38:43'),
(23, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-10 15:39:03'),
(24, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-10 15:45:30'),
(25, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-10 15:47:00'),
(26, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-10 16:08:05'),
(27, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '::1', '2026-09-10 16:22:10'),
(28, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-10 16:40:19'),
(29, 27, 'user.login', 'users', 27, NULL, '{\"email\":\"logistics@quincystore.test\",\"role\":\"logistics\"}', '::1', '2026-09-10 16:44:12'),
(30, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '::1', '2026-09-10 16:46:08'),
(31, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '::1', '2026-09-10 16:48:18'),
(32, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '::1', '2026-09-10 16:54:57'),
(33, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '::1', '2026-09-10 17:07:42'),
(34, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-10 17:13:00'),
(35, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '::1', '2026-09-10 17:16:38'),
(36, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-12 09:30:31'),
(37, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-12 09:31:58'),
(38, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-12 09:45:05'),
(39, 27, 'user.login', 'users', 27, NULL, '{\"email\":\"logistics@quincystore.test\",\"role\":\"logistics\"}', '127.0.0.1', '2026-09-12 10:04:13'),
(40, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-12 10:07:15'),
(41, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '127.0.0.1', '2026-09-12 10:13:43'),
(42, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-12 10:15:27'),
(43, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '127.0.0.1', '2026-09-12 10:16:01'),
(44, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '::1', '2026-09-12 10:28:23'),
(45, 27, 'user.login', 'users', 27, NULL, '{\"email\":\"logistics@quincystore.test\",\"role\":\"logistics\"}', '::1', '2026-09-12 10:31:32'),
(46, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '::1', '2026-09-12 10:40:49'),
(47, 27, 'user.login', 'users', 27, NULL, '{\"email\":\"logistics@quincystore.test\",\"role\":\"logistics\"}', '::1', '2026-09-12 11:49:18'),
(48, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '::1', '2026-09-12 11:50:29'),
(49, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-12 11:53:24'),
(50, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '::1', '2026-09-12 11:54:46'),
(51, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '::1', '2026-09-12 11:56:16'),
(52, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '127.0.0.1', '2026-09-12 12:09:13'),
(53, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '127.0.0.1', '2026-09-12 12:09:14'),
(54, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '127.0.0.1', '2026-09-12 12:09:15'),
(55, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '127.0.0.1', '2026-09-12 12:09:16'),
(56, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-12 12:09:16'),
(57, 27, 'user.login', 'users', 27, NULL, '{\"email\":\"logistics@quincystore.test\",\"role\":\"logistics\"}', '127.0.0.1', '2026-09-12 12:09:17'),
(58, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-12 12:10:50'),
(59, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-12 12:10:51'),
(60, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-12 12:10:52'),
(61, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-12 12:10:52'),
(62, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-12 12:10:53'),
(63, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '127.0.0.1', '2026-09-12 12:10:54'),
(64, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '127.0.0.1', '2026-09-12 12:10:54'),
(65, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '127.0.0.1', '2026-09-12 12:10:55'),
(66, 27, 'user.login', 'users', 27, NULL, '{\"email\":\"logistics@quincystore.test\",\"role\":\"logistics\"}', '127.0.0.1', '2026-09-12 12:10:56'),
(67, 28, 'user.register', 'users', 28, NULL, '{\"email\":\"wiz@gmail.com\",\"role\":\"customer\"}', '::1', '2026-09-14 14:53:32'),
(68, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '127.0.0.1', '2026-09-14 15:52:29'),
(69, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '127.0.0.1', '2026-09-14 15:52:30'),
(70, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '127.0.0.1', '2026-09-14 15:52:30'),
(71, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '127.0.0.1', '2026-09-14 15:52:31'),
(72, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-14 15:52:32'),
(73, 27, 'user.login', 'users', 27, NULL, '{\"email\":\"logistics@quincystore.test\",\"role\":\"logistics\"}', '127.0.0.1', '2026-09-14 15:52:32'),
(74, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-14 15:53:36'),
(75, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '127.0.0.1', '2026-09-14 15:53:37'),
(76, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '127.0.0.1', '2026-09-14 15:53:39'),
(77, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '127.0.0.1', '2026-09-14 15:53:41'),
(78, 27, 'user.login', 'users', 27, NULL, '{\"email\":\"logistics@quincystore.test\",\"role\":\"logistics\"}', '127.0.0.1', '2026-09-14 15:53:42'),
(79, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '127.0.0.1', '2026-09-14 15:54:37'),
(80, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '127.0.0.1', '2026-09-14 15:54:37'),
(81, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '127.0.0.1', '2026-09-14 15:55:00'),
(82, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '127.0.0.1', '2026-09-14 15:55:01'),
(83, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '127.0.0.1', '2026-09-14 15:55:23'),
(84, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '127.0.0.1', '2026-09-14 15:55:25'),
(85, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '127.0.0.1', '2026-09-14 15:55:27'),
(86, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '127.0.0.1', '2026-09-14 15:55:28'),
(87, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-14 15:55:29'),
(88, 27, 'user.login', 'users', 27, NULL, '{\"email\":\"logistics@quincystore.test\",\"role\":\"logistics\"}', '127.0.0.1', '2026-09-14 15:55:31'),
(89, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '127.0.0.1', '2026-09-14 16:10:24'),
(90, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-14 17:24:11'),
(91, 15, 'user.login', 'users', 15, NULL, '{\"email\":\"admin@quincystore.test\",\"role\":\"admin\"}', '::1', '2026-09-15 17:06:25'),
(92, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-15 17:16:06'),
(93, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '::1', '2026-09-15 17:27:20'),
(94, 23, 'user.login', 'users', 23, NULL, '{\"email\":\"supplier@quincystore.test\",\"role\":\"supplier\"}', '::1', '2026-09-15 17:31:03'),
(95, 24, 'user.login', 'users', 24, NULL, '{\"email\":\"manufacturer@quincystore.test\",\"role\":\"manufacturer\"}', '::1', '2026-09-15 17:37:26'),
(96, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '::1', '2026-09-15 17:41:40'),
(97, 25, 'user.login', 'users', 25, NULL, '{\"email\":\"buyer@quincystore.test\",\"role\":\"business_buyer\"}', '::1', '2026-09-15 19:48:57'),
(98, 26, 'user.login', 'users', 26, NULL, '{\"email\":\"customer@quincystore.test\",\"role\":\"customer\"}', '::1', '2026-09-15 19:52:25');

-- --------------------------------------------------------

--
-- Table structure for table `businesses`
--

CREATE TABLE `businesses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_name` varchar(150) NOT NULL,
  `business_type` enum('supplier','manufacturer','buyer','distributor','retailer') NOT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `tax_number` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Cameroon',
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `businesses`
--

INSERT INTO `businesses` (`id`, `user_id`, `business_name`, `business_type`, `registration_number`, `tax_number`, `description`, `address`, `city`, `country`, `verification_status`, `created_at`, `updated_at`) VALUES
(5, 13, 'queen supplier123\'s Business', 'supplier', NULL, NULL, NULL, NULL, NULL, 'Cameroon', 'pending', '2026-09-08 10:48:37', '2026-09-08 10:48:37'),
(6, 14, 'joy supplier\'s Business', 'supplier', NULL, NULL, NULL, NULL, NULL, 'Cameroon', 'pending', '2026-09-09 13:35:21', '2026-09-09 13:35:21'),
(7, 23, 'Quincy Supplier\'s Business', 'supplier', NULL, NULL, NULL, NULL, NULL, 'Cameroon', 'pending', '2026-09-10 15:41:10', '2026-09-10 15:41:10'),
(8, 25, 'Quincy Buyer\'s Business', 'buyer', NULL, NULL, NULL, NULL, NULL, 'Cameroon', 'pending', '2026-09-10 16:34:54', '2026-09-10 16:34:54'),
(9, 24, 'Quincy Manufacturing', 'manufacturer', NULL, NULL, 'Quincy Store manufacturer test business', NULL, 'Douala', 'Cameroon', 'verified', '2026-09-14 16:49:24', '2026-09-14 16:49:24');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('active','converted','abandoned') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 26, 'active', '2026-09-14 16:10:24', '2026-09-14 16:10:24');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(2, NULL, 'Groceries', 'Everyday food and household provisions.', NULL, 'active', '2026-09-04 16:13:07', '2026-09-04 16:13:07'),
(3, NULL, 'Beverages', 'Coffee, tea, juice, and refreshing drinks.', NULL, 'active', '2026-09-04 16:13:07', '2026-09-04 16:13:07'),
(4, NULL, 'Electronics', 'Practical electronics for work and home.', NULL, 'active', '2026-09-04 16:13:07', '2026-09-04 16:13:07'),
(5, NULL, 'Office Supplies', 'Furniture and supplies for productive workspaces.', NULL, 'active', '2026-09-04 16:13:07', '2026-09-04 16:13:07');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reserved_quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) DEFAULT 10,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `variant_id`, `quantity`, `reserved_quantity`, `reorder_level`, `updated_at`) VALUES
(5, 8, NULL, 100, 0, 10, '2026-09-14 17:57:53'),
(6, 9, NULL, 10, 0, 10, '2026-09-15 17:21:12');

-- --------------------------------------------------------

--
-- Table structure for table `manufacturers`
--

CREATE TABLE `manufacturers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_id` int(11) NOT NULL,
  `manufacturer_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Cameroon',
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manufacturers`
--

INSERT INTO `manufacturers` (`id`, `user_id`, `business_id`, `manufacturer_name`, `description`, `address`, `city`, `country`, `verification_status`, `status`, `created_at`, `updated_at`) VALUES
(1, 24, 9, 'Quincy Manufacturing', 'Quincy Store manufacturer test profile', NULL, 'Douala', 'Cameroon', 'verified', 'active', '2026-09-14 16:49:24', '2026-09-14 16:49:24');

-- --------------------------------------------------------

--
-- Table structure for table `negotiations`
--

CREATE TABLE `negotiations` (
  `id` int(11) NOT NULL,
  `quotation_request_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `status` enum('ongoing','agreed','cancelled') DEFAULT 'ongoing',
  `final_price` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `negotiation_messages`
--

CREATE TABLE `negotiation_messages` (
  `id` int(11) NOT NULL,
  `negotiation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `proposed_price` decimal(15,2) DEFAULT NULL,
  `proposed_quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` enum('Pending','Paid','Failed') NOT NULL DEFAULT 'Pending',
  `order_status` enum('Pending','Processing','Out for Delivery','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_method` enum('MTN_MOMO','ORANGE_MONEY','OTHER') NOT NULL,
  `transaction_reference` varchar(150) NOT NULL,
  `provider_reference` varchar(150) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'XAF',
  `status` enum('pending','successful','failed','cancelled','refunded') DEFAULT 'pending',
  `payment_response` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `description` text DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `retail_price` decimal(12,2) NOT NULL,
  `wholesale_price` decimal(12,2) DEFAULT NULL,
  `minimum_order_quantity` int(11) DEFAULT 1,
  `unit` varchar(50) DEFAULT 'piece',
  `weight` decimal(10,2) DEFAULT NULL,
  `country_of_origin` varchar(100) DEFAULT 'Cameroon',
  `status` enum('draft','active','out_of_stock','discontinued') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `supplier_id`, `manufacturer_id`, `category_id`, `name`, `slug`, `description`, `sku`, `retail_price`, `wholesale_price`, `minimum_order_quantity`, `unit`, `weight`, `country_of_origin`, `status`, `created_at`, `updated_at`) VALUES
(8, 11, NULL, 3, 'juice', 'juice', 'quality natural juice', NULL, 1000.00, 800.00, 1, '12piece', 0.45, 'Cameroon', 'active', '2026-09-14 17:57:53', '2026-09-14 17:57:53'),
(9, 11, NULL, 4, 'USB', 'usb', NULL, NULL, 2000.00, 1000.00, 1, '10', 0.50, 'Cameroon', 'active', '2026-09-15 17:21:12', '2026-09-15 17:21:12');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `wholesale_price` decimal(12,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(15,2) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('active','inactive','expired') DEFAULT 'inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotion_products`
--

CREATE TABLE `promotion_products` (
  `id` int(11) NOT NULL,
  `promotion_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` int(11) NOT NULL,
  `quotation_request_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `requested_price` decimal(12,2) DEFAULT NULL,
  `offered_price` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_requests`
--

CREATE TABLE `quotation_requests` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `status` enum('pending','responded','accepted','rejected','expired') DEFAULT 'pending',
  `delivery_location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `action` varchar(50) NOT NULL DEFAULT 'login',
  `attempts` int(11) NOT NULL DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `ip_address`, `action`, `attempts`, `last_attempt`, `created_at`) VALUES
(23, '::1', 'login', 3, '2026-09-15 20:24:24', '2026-09-15 20:22:17');

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','processed','failed') DEFAULT 'pending',
  `refund_reference` varchar(150) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Platform administrator with full access', '2026-09-02 03:40:32', '2026-09-02 03:40:32'),
(2, 'supplier', 'Supplier who sells products on the platform', '2026-09-02 03:40:32', '2026-09-02 03:40:32'),
(3, 'manufacturer', 'Manufacturer who produces and sells products', '2026-09-02 03:40:32', '2026-09-02 03:40:32'),
(4, 'business_buyer', 'Business buyer for bulk B2B purchasing', '2026-09-02 03:40:32', '2026-09-02 03:40:32'),
(5, 'customer', 'Regular retail customer for B2C purchases', '2026-09-02 03:40:32', '2026-09-02 03:40:32'),
(6, 'logistics', 'Logistics and delivery staff', '2026-09-02 03:40:32', '2026-09-02 03:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `updated_at`) VALUES
(1, 'site_name', 'Quincy Store', '2026-09-02 03:40:32'),
(2, 'site_email', 'info@quincystore.cm', '2026-09-02 03:40:32'),
(3, 'site_phone', '+237 699 000 001', '2026-09-02 03:40:32'),
(4, 'site_address', 'Douala, Cameroon', '2026-09-02 03:40:32'),
(5, 'currency', 'XAF', '2026-09-02 03:40:32'),
(6, 'currency_symbol', 'FCFA', '2026-09-02 03:40:32'),
(7, 'maintenance_mode', '0', '2026-09-02 03:40:32'),
(8, 'smtp_host', 'smtp.mailtrap.io', '2026-09-02 03:40:32'),
(9, 'smtp_port', '587', '2026-09-02 03:40:32'),
(10, 'smtp_username', '', '2026-09-02 03:40:32'),
(11, 'smtp_password', '', '2026-09-02 03:40:32'),
(12, 'smtp_from_email', 'noreply@quincystore.cm', '2026-09-02 03:40:32'),
(13, 'smtp_from_name', 'Quincy Store', '2026-09-02 03:40:32'),
(14, 'shipping_fee', '2000', '2026-09-02 03:40:32'),
(15, 'free_shipping_min', '50000', '2026-09-02 03:40:32'),
(16, 'quotation_expiry_days', '7', '2026-09-02 03:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `carrier` varchar(150) DEFAULT NULL,
  `tracking_number` varchar(150) DEFAULT NULL,
  `shipping_method` varchar(100) DEFAULT NULL,
  `shipping_cost` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','picked_up','in_transit','out_for_delivery','delivered','returned') DEFAULT 'pending',
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipment_tracking`
--

CREATE TABLE `shipment_tracking` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `status` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_id` int(11) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Cameroon',
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `rating` decimal(2,1) DEFAULT 0.0,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `user_id`, `business_id`, `supplier_name`, `description`, `address`, `city`, `country`, `verification_status`, `rating`, `status`, `created_at`, `updated_at`) VALUES
(9, 13, 5, 'queen supplier123', NULL, NULL, NULL, 'Cameroon', 'pending', 0.0, 'active', '2026-09-08 10:48:37', '2026-09-08 10:48:37'),
(10, 14, 6, 'joy supplier', NULL, NULL, NULL, 'Cameroon', 'pending', 0.0, 'active', '2026-09-09 13:35:21', '2026-09-09 13:35:21'),
(11, 23, 7, 'Quincy Supplier', NULL, NULL, NULL, 'Cameroon', 'pending', 0.0, 'active', '2026-09-10 15:41:10', '2026-09-10 15:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('open','pending','resolved','closed') DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended','pending') DEFAULT 'pending',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `first_name`, `last_name`, `email`, `phone`, `password`, `profile_image`, `status`, `email_verified_at`, `last_login`, `created_at`, `updated_at`) VALUES
(8, 5, 'queen', 'customer', 'customer@gmail.com', '652526735', '$2y$12$gHApO1H1UFU8u.MwxKl8HuAmA0eylFYcjX/oqx9BO4ocS3wNzqDQe', NULL, 'active', NULL, '2026-09-07 19:25:08', '2026-09-07 19:25:08', '2026-09-07 19:25:08'),
(13, 2, 'queen', 'supplier123', 'supplier@gmail.com', '6345637766', '$2y$12$BrJ7fou9jvMDvBty/PamEuImMp7R.2LhP1LC5M9BI.uOY1ycMsgOm', NULL, 'pending', NULL, NULL, '2026-09-08 10:48:37', '2026-09-08 10:48:37'),
(14, 2, 'joy', 'supplier', 'joysupplier@gmail.com', '6345637766', '$2y$12$rx63oV8N/Uh9NGYEy6gX/.vZogrf89xxJMFSzOcqdeI.MuKl.gI.q', NULL, 'pending', NULL, NULL, '2026-09-09 13:35:21', '2026-09-09 13:35:21'),
(15, 1, 'Quincy', 'Admin', 'admin@quincystore.test', '670000000', '$2y$10$zZy6COqHGHz6ZfQjhWYA5ezSTnJvG1is6Jg0M2kn0I1vbD0wMeS7m', NULL, 'active', NULL, '2026-09-15 17:06:25', '2026-09-09 14:05:00', '2026-09-15 17:06:25'),
(22, 1, 'Quincy1', 'Admin1', 'admin1@quincystore.test', '6900000011', '$2y$10$LYXNer8M2RMDjkO99PJTHOO/ppguRsF0.dt/rfd1f91itScMKSkU6', NULL, 'active', NULL, NULL, '2026-09-09 14:49:57', '2026-09-09 14:49:57'),
(23, 2, 'Quincy', 'Supplier', 'supplier@quincystore.test', '690000002', '$2y$10$CGl7ZSlQaGCZ2KP66f8BwenddeerGMMBCOrfP9VtsvRqZSUDCa7yC', NULL, 'active', NULL, '2026-09-15 17:31:03', '2026-09-09 14:49:57', '2026-09-15 17:31:03'),
(24, 3, 'Quincy', 'Manufacturer', 'manufacturer@quincystore.test', '690000003', '$2y$10$YwPoeAzaEo89.gXxYx6cQusmxyAjmy2cJfAxDv91f5j/Uoq6Cbll2', NULL, 'active', NULL, '2026-09-15 17:37:26', '2026-09-09 14:49:57', '2026-09-15 17:37:26'),
(25, 4, 'Quincy', 'Buyer', 'buyer@quincystore.test', '690000004', '$2y$10$1XSbuyAULsBgL32ifk7LyO8/l1m0gfyljypTMH5gKMUb6BV8xMT16', NULL, 'active', NULL, '2026-09-15 19:48:56', '2026-09-09 14:49:57', '2026-09-15 19:48:56'),
(26, 5, 'Quincy', 'Customer', 'customer@quincystore.test', '690000005', '$2y$10$IzeX8XG5jl9dQgJPiUgAwOUMc/4sa1djZP6PRyH1gN6ZICpmhPW3C', NULL, 'active', NULL, '2026-09-15 19:52:25', '2026-09-09 14:49:57', '2026-09-15 19:52:25'),
(27, 6, 'Quincy', 'Logistics', 'logistics@quincystore.test', '690000006', '$2y$10$L/I2pLVU/vLfzWWnxhH6T.OnFevf0jVHEMIbl3.K4tL2Qm68lVse.', NULL, 'active', NULL, '2026-09-14 15:55:31', '2026-09-09 14:49:57', '2026-09-14 15:55:31'),
(28, 5, 'wiz', 'kizz', 'wiz@gmail.com', '678879279', '$2y$12$OzJB1kNkzlIN/u2hRqouMeE3nQz1EFl7YY6WTZGADIMVLZUWijuwa', NULL, 'active', NULL, '2026-09-14 14:53:33', '2026-09-14 14:53:32', '2026-09-14 14:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `business_id` (`business_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `businesses`
--
ALTER TABLE `businesses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `manufacturers`
--
ALTER TABLE `manufacturers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `business_id` (`business_id`);

--
-- Indexes for table `negotiations`
--
ALTER TABLE `negotiations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_request_id` (`quotation_request_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `negotiation_messages`
--
ALTER TABLE `negotiation_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `negotiation_id` (`negotiation_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_status` (`order_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pr_token` (`token`),
  ADD KEY `idx_pr_email` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_reference` (`transaction_reference`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `manufacturer_id` (`manufacturer_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promotion_products`
--
ALTER TABLE `promotion_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_promotion_product` (`promotion_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_request_id` (`quotation_request_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `quotation_requests`
--
ALTER TABLE `quotation_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rate_limit_ip_action` (`ip_address`,`action`),
  ADD KEY `idx_rate_limit_ip` (`ip_address`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `refund_reference` (`refund_reference`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_settings_key` (`key`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_number` (`tracking_number`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `shipment_tracking`
--
ALTER TABLE `shipment_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipment_id` (`shipment_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `business_id` (`business_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `businesses`
--
ALTER TABLE `businesses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `manufacturers`
--
ALTER TABLE `manufacturers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `negotiations`
--
ALTER TABLE `negotiations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `negotiation_messages`
--
ALTER TABLE `negotiation_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotion_products`
--
ALTER TABLE `promotion_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotation_requests`
--
ALTER TABLE `quotation_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipment_tracking`
--
ALTER TABLE `shipment_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `addresses_ibfk_2` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `businesses`
--
ALTER TABLE `businesses`
  ADD CONSTRAINT `businesses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `manufacturers`
--
ALTER TABLE `manufacturers`
  ADD CONSTRAINT `manufacturers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `manufacturers_ibfk_2` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `negotiations`
--
ALTER TABLE `negotiations`
  ADD CONSTRAINT `negotiations_ibfk_1` FOREIGN KEY (`quotation_request_id`) REFERENCES `quotation_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `negotiations_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `negotiations_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `negotiation_messages`
--
ALTER TABLE `negotiation_messages`
  ADD CONSTRAINT `negotiation_messages_ibfk_1` FOREIGN KEY (`negotiation_id`) REFERENCES `negotiations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `negotiation_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `promotion_products`
--
ALTER TABLE `promotion_products`
  ADD CONSTRAINT `promotion_products_ibfk_1` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `promotion_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_ibfk_1` FOREIGN KEY (`quotation_request_id`) REFERENCES `quotation_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quotation_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quotation_requests`
--
ALTER TABLE `quotation_requests`
  ADD CONSTRAINT `quotation_requests_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quotation_requests_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `refunds_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shipment_tracking`
--
ALTER TABLE `shipment_tracking`
  ADD CONSTRAINT `shipment_tracking_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
