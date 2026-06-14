-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2026 at 11:20 AM
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
-- Database: `sales`
--

-- --------------------------------------------------------

--
-- Table structure for table `backup_logs`
--

CREATE TABLE `backup_logs` (
  `backup_id` int(11) NOT NULL,
  `backup_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `backup_type` enum('auto','manual') NOT NULL DEFAULT 'manual',
  `backup_file` varchar(255) DEFAULT NULL,
  `status` enum('success','failed') NOT NULL DEFAULT 'success',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `backup_logs`
--

INSERT INTO `backup_logs` (`backup_id`, `backup_date`, `backup_type`, `backup_file`, `status`, `notes`) VALUES
(1, '2026-03-31 23:00:00', 'auto', 'backup_2026_04_01.sql', 'success', 'Nightly backup'),
(2, '2026-04-01 23:00:00', 'auto', 'backup_2026_04_02.sql', 'success', 'Nightly backup'),
(3, '2026-04-02 23:00:00', 'auto', 'backup_2026_04_03.sql', 'success', 'Nightly backup'),
(4, '2026-04-03 23:00:00', 'auto', 'backup_2026_04_04.sql', 'success', 'Nightly backup'),
(5, '2026-04-04 23:00:00', 'auto', 'backup_2026_04_05.sql', 'success', 'Nightly backup'),
(6, '2026-04-05 06:30:00', 'manual', 'backup_pre_update.sql', 'success', 'Before POS update'),
(7, '2026-04-05 08:00:00', 'manual', 'backup_after_update.sql', 'success', 'After POS update'),
(8, '2026-04-05 23:00:00', 'auto', 'backup_2026_04_06.sql', 'success', 'Nightly backup'),
(9, '2026-04-06 23:00:00', 'auto', 'backup_2026_04_07.sql', 'failed', 'Disk space low'),
(10, '2026-04-07 23:00:00', 'auto', 'backup_2026_04_08.sql', 'success', 'Disk cleaned');

-- --------------------------------------------------------

--
-- Table structure for table `cash_float`
--

CREATE TABLE `cash_float` (
  `float_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `opening_balance` decimal(10,2) NOT NULL,
  `closing_balance` decimal(10,2) DEFAULT NULL,
  `total_sales` decimal(10,2) DEFAULT NULL,
  `expected_cash` decimal(10,2) DEFAULT NULL,
  `actual_cash` decimal(10,2) DEFAULT NULL,
  `difference` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cash_float`
--

INSERT INTO `cash_float` (`float_id`, `user_id`, `date`, `opening_balance`, `closing_balance`, `total_sales`, `expected_cash`, `actual_cash`, `difference`, `notes`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 4, '2026-04-02', 20000.00, NULL, NULL, NULL, NULL, NULL, 'same', NULL, NULL, '2026-04-02 14:47:19'),
(2, 4, '2026-04-04', 200000.00, NULL, NULL, NULL, NULL, NULL, 'Cash Float', NULL, NULL, '2026-04-04 15:04:31'),
(3, 4, '2026-04-05', 200000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-05 09:51:27'),
(4, 4, '2026-04-29', 70000.00, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, '2026-04-29 19:33:49'),
(5, 4, '2026-05-24', 200000.00, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, '2026-05-24 19:38:53');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(200) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `is_active`, `created_at`) VALUES
(1, 'Locks & Handles', 1, '2026-04-05 06:00:00'),
(2, 'Padlocks', 1, '2026-04-05 06:00:00'),
(3, 'Door Accessories', 1, '2026-04-05 06:00:00'),
(4, 'Cabinet Hardware', 1, '2026-04-05 06:00:00'),
(5, 'Chains', 1, '2026-04-05 06:00:00'),
(6, 'Hinges', 1, '2026-04-05 06:00:00'),
(7, 'Fasteners', 1, '2026-04-05 06:00:00'),
(8, 'Security Alarms', 1, '2026-04-05 06:00:00'),
(9, 'Tools', 1, '2026-04-05 06:00:00'),
(10, 'Construction Supplies', 1, '2026-04-05 06:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE `collection` (
  `transaction_id` int(11) NOT NULL,
  `date` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `invoice` varchar(100) NOT NULL,
  `amount` varchar(100) NOT NULL,
  `remarks` varchar(100) NOT NULL,
  `balance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `collection`
--

INSERT INTO `collection` (`transaction_id`, `date`, `name`, `invoice`, `amount`, `remarks`, `balance`) VALUES
(1, '04/03/26', 'Hafie', 'RS-22290005', '30000', 'Cash', 0),
(2, '04/04/26', 'Kato John', 'RS-23330224', '29795', 'Cash', 0),
(3, '04/04/26', 'Amina N', 'RS-23330225', '52000', 'Mobile Money', 0),
(4, '04/05/26', 'Peter O', 'RS-23330226', '88000', 'Cash', 0),
(5, '04/05/26', 'Rose K', 'RS-23330227', '45000', 'Cash', 5000),
(6, '04/05/26', 'David M', 'RS-23330228', '120000', 'Bank Transfer', 0),
(7, '04/06/26', 'Nabirye S', 'RS-23330229', '60000', 'Cash', 0),
(8, '04/06/26', 'Okello R', 'RS-23330230', '35000', 'Mobile Money', 0),
(9, '04/06/26', 'Winnie A', 'RS-23330231', '78000', 'Cash', 0),
(10, '04/07/26', 'Isaac T', 'RS-23330232', '91000', 'Cash', 0);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `membership_number` varchar(100) DEFAULT NULL,
  `prod_name` varchar(550) DEFAULT NULL,
  `expected_date` varchar(500) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `customer_name`, `address`, `contact`, `membership_number`, `prod_name`, `expected_date`, `note`, `is_active`, `created_at`) VALUES
(15, 'hafie', 'Nansana', '0705447791', '20000', 'Hammer', '2026-04-05', 'DAILY', 1, '2026-04-05 05:58:34');

-- --------------------------------------------------------

--
-- Table structure for table `financial_accounts`
--

CREATE TABLE `financial_accounts` (
  `account_id` int(11) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` enum('cash','bank','wallet') NOT NULL DEFAULT 'bank',
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `funds_owner` enum('business','customer') NOT NULL DEFAULT 'business',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `liabilities`
--

CREATE TABLE `liabilities` (
  `liability_id` int(11) NOT NULL,
  `liability_name` varchar(150) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `liability_type` enum('payable','expense','loan','other') NOT NULL DEFAULT 'payable',
  `is_long_term` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('open','settled') NOT NULL DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_code` varchar(200) NOT NULL,
  `gen_name` varchar(200) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `unit_type` enum('piece','meter','liter') NOT NULL DEFAULT 'piece',
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `o_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `profit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `supplier` varchar(100) DEFAULT NULL,
  `onhand_qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty_sold` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_stock_level` decimal(10,2) NOT NULL DEFAULT 2.00,
  `expiry_date` varchar(500) DEFAULT NULL,
  `date_arrival` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_dead_stock` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_code`, `gen_name`, `product_name`, `category_id`, `unit_type`, `cost`, `o_price`, `price`, `profit`, `supplier`, `onhand_qty`, `qty`, `qty_sold`, `min_stock_level`, `expiry_date`, `date_arrival`, `description`, `is_active`, `is_dead_stock`, `created_at`) VALUES
(58, '', 'kenyos black', 'kenyos black', 1, 'piece', 33558.00, 49350.00, 49350.00, 15792.00, 'Kampala Main Supplier', 56.00, 56.00, 21.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - kenyos black', 1, 0, '2026-04-02 14:14:50'),
(59, '', 'tony', 'tony', 1, 'piece', 20201.76, 28058.00, 28058.00, 7856.24, 'Kampala Main Supplier', 29.00, 29.00, 8.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - tony', 1, 0, '2026-04-02 14:14:50'),
(60, '', 'sab', 'sab', 1, 'piece', 40010.75, 61555.00, 61555.00, 21544.25, 'Kampala Main Supplier', 63.00, 63.00, 35.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - sab', 1, 0, '2026-04-02 14:14:50'),
(61, '', 'lak b', 'lak b', 1, 'piece', 23817.00, 35025.00, 35025.00, 11208.00, 'Kampala Main Supplier', 51.00, 51.00, 18.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - lak b', 1, 0, '2026-04-02 14:14:50'),
(62, '', 'GDN', 'GDN', 1, 'piece', 18104.40, 25145.00, 25145.00, 7040.60, 'Kampala Main Supplier', 20.00, 20.00, 40.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - GDN', 1, 0, '2026-04-02 14:14:50'),
(63, '', 'lak s', 'lak s', 1, 'piece', 18349.92, 25486.00, 25486.00, 7136.08, 'Kampala Main Supplier', 33.00, 33.00, 8.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - lak s', 1, 0, '2026-04-02 14:14:50'),
(64, '', 'soukuw', 'soukuw', 1, 'piece', 16884.72, 23451.00, 23451.00, 6566.28, 'Kampala Main Supplier', 69.00, 69.00, 43.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - soukuw', 1, 0, '2026-04-02 14:14:50'),
(65, '', 'parker s', 'parker s', 1, 'piece', 17658.72, 24526.00, 24526.00, 6867.28, 'Kampala Main Supplier', 18.00, 18.00, 11.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - parker s', 1, 0, '2026-04-02 14:14:50'),
(66, '', 'parker b', 'parker b', 1, 'piece', 47167.90, 72566.00, 72566.00, 25398.10, 'Kampala Main Supplier', 43.00, 43.00, 21.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - parker b', 1, 0, '2026-04-02 14:14:50'),
(67, '', 'bovos b', 'bovos b', 1, 'piece', 20505.40, 30155.00, 30155.00, 9649.60, 'Kampala Main Supplier', 76.00, 76.00, 21.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - bovos b', 1, 0, '2026-04-02 14:14:50'),
(68, '', 'bovos s', 'bovos s', 1, 'piece', 17319.60, 24055.00, 24055.00, 6735.40, 'Kampala Main Supplier', 73.00, 73.00, 13.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - bovos s', 1, 0, '2026-04-02 14:14:50'),
(69, '', 'GJS', 'GJS', 1, 'piece', 20573.40, 30255.00, 30255.00, 9681.60, 'Kampala Main Supplier', 106.00, 106.00, 20.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - GJS', 1, 0, '2026-04-02 14:14:50'),
(70, '', 'pam', 'pam', 1, 'piece', 16102.80, 22365.00, 22365.00, 6262.20, 'Kampala Main Supplier', 77.00, 77.00, 27.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - pam', 1, 0, '2026-04-02 14:14:50'),
(71, '', '155(20)', '155(20)', 1, 'piece', 18351.36, 25488.00, 25488.00, 7136.64, 'Kampala Main Supplier', 31.00, 30.00, 40.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - 155(20)', 1, 0, '2026-04-02 14:14:50'),
(72, '', '155(30)', '155(30)', 1, 'piece', 18351.36, 25488.00, 25488.00, 7136.64, 'Kampala Main Supplier', 102.00, 102.00, 30.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - 155(30)', 1, 0, '2026-04-02 14:14:50'),
(73, '', '153(20)', '153(20)', 1, 'piece', 18351.36, 25488.00, 25488.00, 7136.64, 'Kampala Main Supplier', 81.00, 80.00, 9.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - 153(20)', 1, 0, '2026-04-02 14:14:50'),
(74, '', '153(30)', '153(30)', 1, 'piece', 18351.36, 25488.00, 25488.00, 7136.64, 'Kampala Main Supplier', 41.00, 41.00, 19.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - 153(30)', 1, 0, '2026-04-02 14:14:50'),
(75, '', 'KALE 201', 'KALE 201', 1, 'piece', 18351.36, 25488.00, 25488.00, 7136.64, 'Kampala Main Supplier', 63.00, 63.00, 44.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - KALE 201', 1, 0, '2026-04-02 14:14:50'),
(76, '', 'KALE 201 N1', 'KALE 201 N1', 1, 'piece', 35910.55, 55247.00, 55247.00, 19336.45, 'Kampala Main Supplier', 45.00, 45.00, 24.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - KALE 201 N1', 1, 0, '2026-04-02 14:14:50'),
(77, '', 'Screws', 'Screws', 1, 'piece', 36045.75, 55455.00, 55455.00, 19409.25, 'Kampala Main Supplier', 98.00, 98.00, 17.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - Screws', 1, 0, '2026-04-02 14:14:50'),
(78, '', 'Parta', 'Parta', 1, 'piece', 35894.30, 55222.00, 55222.00, 19327.70, 'Kampala Main Supplier', 70.00, 70.00, 18.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - Parta', 1, 0, '2026-04-02 14:14:50'),
(79, '', 'ELEPHANT LOCK', 'ELEPHANT LOCK', 1, 'piece', 1510.50, 2014.00, 2014.00, 503.50, 'Kampala Main Supplier', 46.00, 45.00, 49.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - ELEPHANT LOCK', 1, 0, '2026-04-02 14:14:50'),
(80, '', 'DRAWER 808', 'DRAWER 808', 1, 'piece', 1591.50, 2122.00, 2122.00, 530.50, 'Kampala Main Supplier', 88.00, 88.00, 19.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - DRAWER 808', 1, 0, '2026-04-02 14:14:50'),
(81, '', 'WOHU 32', 'WOHU 32', 1, 'piece', 1610.25, 2147.00, 2147.00, 536.75, 'Kampala Main Supplier', 17.00, 17.00, 31.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - WOHU 32', 1, 0, '2026-04-02 14:14:50'),
(82, '', 'WOHU 38', 'WOHU 38', 1, 'piece', 1826.25, 2435.00, 2435.00, 608.75, 'Kampala Main Supplier', 52.00, 52.00, 32.00, 2.00, '2027-12-31', '2026-04-05', 'Standard stock item - WOHU 38', 1, 0, '2026-04-02 14:14:50'),
(83, 'TRICIRCLE 262', 'TRICIRCLE 262', 'TRICIRCLE 262', 1, 'piece', 3391.50, 4522.00, 4522.00, 1130.50, 'Kampala Main Supplier', 53.00, 53.00, 31.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(84, 'TRICIRCLE 263', 'TRICIRCLE 263', 'TRICIRCLE 263', 1, 'piece', 4875.00, 6500.00, 6500.00, 1625.00, 'Kampala Main Supplier', 42.00, 42.00, 8.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(85, 'TRICIRCLE 264', 'TRICIRCLE 264', 'TRICIRCLE 264', 1, 'piece', 7155.75, 9541.00, 9541.00, 2385.25, 'Kampala Main Supplier', 49.00, 49.00, 41.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(86, 'TRICIRCLE 265', 'TRICIRCLE 265', 'TRICIRCLE 265', 1, 'piece', 12853.44, 17852.00, 17852.00, 4998.56, 'Kampala Main Supplier', 54.00, 54.00, 7.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(87, 'TRICIRCLE 266', 'TRICIRCLE 266', 'TRICIRCLE 266', 1, 'piece', 18564.48, 25784.00, 25784.00, 7219.52, 'Kampala Main Supplier', 105.00, 105.00, 35.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(88, 'WOHU 15000', 'WOHU 15000', 'WOHU 15000', 1, 'piece', 12960.00, 18000.00, 18000.00, 5040.00, 'Kampala Main Supplier', 26.00, 26.00, 5.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(89, 'WOHU 3500', 'WOHU 3500', 'WOHU 3500', 1, 'piece', 1725.00, 2300.00, 2300.00, 575.00, 'Kampala Main Supplier', 62.00, 62.00, 34.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(90, 'WOHU 2500', 'WOHU 2500', 'WOHU 2500', 1, 'piece', 975.00, 1300.00, 1300.00, 325.00, 'Kampala Main Supplier', 51.00, 51.00, 20.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(91, 'CASA 2500', 'CASA 2500', 'CASA 2500', 1, 'piece', 975.00, 1300.00, 1300.00, 325.00, 'Kampala Main Supplier', 35.00, 35.00, 23.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(92, 'CASA 3500', 'CASA 3500', 'CASA 3500', 1, 'piece', 1725.00, 2300.00, 2300.00, 575.00, 'Kampala Main Supplier', 19.00, 19.00, 21.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(93, 'CASA 5000', 'CASA 5000', 'CASA 5000', 1, 'piece', 1725.00, 2300.00, 2300.00, 575.00, 'Kampala Main Supplier', 51.00, 51.00, 7.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(94, 'BAGLOCK', 'BAGLOCK', 'BAGLOCK', 2, 'piece', 4437.75, 5917.00, 5917.00, 1479.25, 'Kampala Main Supplier', 20.00, 20.00, 50.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(95, 'UNION', 'UNION', 'UNION', 1, 'piece', 27200.00, 40000.00, 40000.00, 12800.00, 'Kampala Main Supplier', 61.00, 61.00, 46.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(96, 'MUSCLE COW', 'MUSCLE COW', 'MUSCLE COW', 1, 'piece', 0.00, 0.00, 0.00, 0.00, 'Kampala Main Supplier', 69.00, 69.00, 30.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(97, 'MINDY (30K)', 'MINDY (30K)', 'MINDY (30K)', 1, 'piece', 14400.00, 20000.00, 20000.00, 5600.00, 'Kampala Main Supplier', 84.00, 84.00, 14.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(98, 'MINDY (25K)', 'MINDY (25K)', 'MINDY (25K)', 1, 'piece', 12240.00, 17000.00, 17000.00, 4760.00, 'Kampala Main Supplier', 83.00, 83.00, 9.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(99, 'YETI', 'YETI', 'YETI', 1, 'piece', 5250.00, 7000.00, 7000.00, 1750.00, 'Kampala Main Supplier', 37.00, 37.00, 8.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(100, 'STURDY', 'STURDY', 'STURDY', 1, 'piece', 7200.00, 10000.00, 10000.00, 2800.00, 'Kampala Main Supplier', 64.00, 64.00, 29.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(101, 'STELAR', 'STELAR', 'STELAR', 1, 'piece', 8280.00, 11500.00, 11500.00, 3220.00, 'Kampala Main Supplier', 93.00, 93.00, 39.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(102, 'ALARM BIG', 'ALARM BIG', 'ALARM BIG', 8, 'piece', 19440.00, 27000.00, 27000.00, 7560.00, 'Kampala Main Supplier', 106.00, 106.00, 41.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(103, 'ALARM SMALL', 'ALARM SMALL', 'ALARM SMALL', 8, 'piece', 10080.00, 14000.00, 14000.00, 3920.00, 'Kampala Main Supplier', 89.00, 89.00, 44.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(104, 'SANTIAO', 'SANTIAO', 'SANTIAO', 1, 'piece', 6999.75, 9333.00, 9333.00, 2333.25, 'Kampala Main Supplier', 62.00, 62.00, 18.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(105, 'BUCKLER RD', 'BUCKLER RD', 'BUCKLER RD', 1, 'piece', 15840.00, 22000.00, 22000.00, 6160.00, 'Kampala Main Supplier', 87.00, 87.00, 8.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(106, 'CASA 4K', 'CASA 4K', 'CASA 4K', 1, 'piece', 2493.75, 3325.00, 3325.00, 831.25, 'Kampala Main Supplier', 10.00, 10.00, 47.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(107, 'TUAWAN', 'TUAWAN', 'TUAWAN', 1, 'piece', 12960.00, 18000.00, 18000.00, 5040.00, 'Kampala Main Supplier', 28.00, 28.00, 23.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(108, 'ABSASIN', 'ABSASIN', 'ABSASIN', 1, 'piece', 10080.00, 14000.00, 14000.00, 3920.00, 'Kampala Main Supplier', 45.00, 45.00, 37.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(109, 'CASA 70', 'CASA 70', 'CASA 70', 1, 'piece', 10080.00, 14000.00, 14000.00, 3920.00, 'Kampala Main Supplier', 25.00, 25.00, 46.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(110, 'BUCKLER SQ', 'BUCKLER SQ', 'BUCKLER SQ', 1, 'piece', 15840.00, 22000.00, 22000.00, 6160.00, 'Kampala Main Supplier', 79.00, 79.00, 54.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(111, 'FF', 'FF', 'FF', 1, 'piece', 8640.00, 12000.00, 12000.00, 3360.00, 'Kampala Main Supplier', 93.00, 93.00, 15.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(112, 'DONGYA', 'DONGYA', 'DONGYA', 1, 'piece', 15840.00, 22000.00, 22000.00, 6160.00, 'Kampala Main Supplier', 67.00, 67.00, 16.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(113, 'DONGYA RD', 'DONGYA RD', 'DONGYA RD', 1, 'piece', 9000.00, 12500.00, 12500.00, 3500.00, 'Kampala Main Supplier', 53.00, 53.00, 29.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(114, 'MINDY WHITE', 'MINDY WHITE', 'MINDY WHITE', 1, 'piece', 12240.00, 17000.00, 17000.00, 4760.00, 'Kampala Main Supplier', 21.00, 21.00, 9.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(115, 'RICHDOOR', 'RICHDOOR', 'RICHDOOR', 1, 'piece', 8640.00, 12000.00, 12000.00, 3360.00, 'Kampala Main Supplier', 26.00, 26.00, 31.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(116, 'BAO GONG', 'BAO GONG', 'BAO GONG', 1, 'piece', 15840.00, 22000.00, 22000.00, 6160.00, 'Kampala Main Supplier', 23.00, 23.00, 10.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(117, 'HONG ZHENG', 'HONG ZHENG', 'HONG ZHENG', 1, 'piece', 19440.00, 27000.00, 27000.00, 7560.00, 'Kampala Main Supplier', 25.00, 25.00, 26.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(118, 'CHAIN LOCK', 'CHAIN LOCK', 'CHAIN LOCK', 5, 'piece', 6789.75, 9053.00, 9053.00, 2263.25, 'Kampala Main Supplier', 54.00, 54.00, 12.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(119, 'BICYCLE LOCK S', 'BICYCLE LOCK S', 'BICYCLE LOCK S', 2, 'piece', 3975.00, 5300.00, 5300.00, 1325.00, 'Kampala Main Supplier', 53.00, 53.00, 51.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(120, 'DOG CHAIN', 'DOG CHAIN', 'DOG CHAIN', 5, 'piece', 3867.00, 5156.00, 5156.00, 1289.00, 'Kampala Main Supplier', 70.00, 70.00, 16.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(121, 'CHAIN 20K', 'CHAIN 20K', 'CHAIN 20K', 5, 'piece', 13060.08, 18139.00, 18139.00, 5078.92, 'Kampala Main Supplier', 44.00, 44.00, 6.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(122, 'CHAIN 15K', 'CHAIN 15K', 'CHAIN 15K', 5, 'piece', 8740.08, 12139.00, 12139.00, 3398.92, 'Kampala Main Supplier', 22.00, 22.00, 30.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(123, 'HOUBA LOCK', 'HOUBA LOCK', 'HOUBA LOCK', 2, 'piece', 20510.84, 30163.00, 30163.00, 9652.16, 'Kampala Main Supplier', 31.00, 31.00, 34.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(124, 'KALE HANDLES', 'KALE HANDLES', 'KALE HANDLES', 1, 'piece', 18174.24, 25242.00, 25242.00, 7067.76, 'Kampala Main Supplier', 49.00, 49.00, 26.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(125, 'BLACK HANDLE 40K', 'BLACK HANDLE 40K', 'BLACK HANDLE 40K', 1, 'piece', 23800.00, 35000.00, 35000.00, 11200.00, 'Kampala Main Supplier', 66.00, 66.00, 54.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(126, 'BLACK HANDLE 35K', 'BLACK HANDLE 35K', 'BLACK HANDLE 35K', 1, 'piece', 20596.52, 30289.00, 30289.00, 9692.48, 'Kampala Main Supplier', 77.00, 77.00, 53.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(127, 'BLACK HANDLE 30K', 'BLACK HANDLE 30K', 'BLACK HANDLE 30K', 1, 'piece', 18180.00, 25250.00, 25250.00, 7070.00, 'Kampala Main Supplier', 36.00, 35.00, 28.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(128, 'BLACK HANDLE 20K', 'BLACK HANDLE 20K', 'BLACK HANDLE 20K', 1, 'piece', 10800.00, 15000.00, 15000.00, 4200.00, 'Kampala Main Supplier', 42.00, 42.00, 22.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(129, 'SLIDING HANDLE MODERN', 'SLIDING HANDLE MODERN', 'SLIDING HANDLE MODERN', 1, 'piece', 29896.20, 43965.00, 43965.00, 14068.80, 'Kampala Main Supplier', 50.00, 50.00, 16.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(130, '45K', '45K', '45K', 1, 'piece', 20095.92, 27911.00, 27911.00, 7815.08, 'Kampala Main Supplier', 53.00, 53.00, 27.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(131, '30K', '30K', '30K', 1, 'piece', 18080.64, 25112.00, 25112.00, 7031.36, 'Kampala Main Supplier', 101.00, 101.00, 16.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(132, 'HOE HANDLES', 'HOE HANDLES', 'HOE HANDLES', 1, 'piece', 909.75, 1213.00, 1213.00, 303.25, 'Kampala Main Supplier', 62.00, 62.00, 23.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(133, 'SILVER HANDLE 25K', 'SILVER HANDLE 25K', 'SILVER HANDLE 25K', 1, 'piece', 10847.52, 15066.00, 15066.00, 4218.48, 'Kampala Main Supplier', 78.00, 78.00, 50.00, 2.00, '2027-12-31', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50'),
(134, 'Hima Cement', 'Cement', 'PLATE HANDLE   ', 9, 'piece', 489.75, 653.00, 653.00, 163.25, 'Kampala Hardware Mart', 88.00, 88.00, 52.00, 2.00, '2027-07-22', '2026-04-05', NULL, 1, 0, '2026-04-02 14:14:50');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `current_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_stock_level` decimal(10,2) NOT NULL DEFAULT 2.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_dead_stock` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `variant_name`, `price`, `cost`, `current_stock`, `min_stock_level`, `is_active`, `is_dead_stock`, `created_at`) VALUES
(1, 58, 'Standard', 49350.00, 33558.00, 30.00, 2.00, 1, 0, '2026-04-05 05:00:00'),
(2, 58, 'Premium', 52000.00, 36000.00, 20.00, 2.00, 1, 0, '2026-04-05 05:00:00'),
(3, 59, 'Standard', 28058.00, 20201.76, 23.00, 2.00, 1, 0, '2026-04-05 05:00:00'),
(4, 60, 'Standard', 61555.00, 40010.75, 15.00, 2.00, 1, 0, '2026-04-05 05:00:00'),
(5, 61, 'Standard', 35025.00, 23817.00, 18.00, 2.00, 1, 0, '2026-04-05 05:00:00'),
(6, 62, 'Standard', 25145.00, 18104.40, 14.00, 2.00, 1, 0, '2026-04-05 05:00:00'),
(7, 63, 'Standard', 25486.00, 18349.92, 3.00, 2.00, 1, 0, '2026-04-05 05:00:00'),
(8, 64, 'Standard', 23451.00, 16884.72, 20.00, 2.00, 1, 0, '2026-04-05 05:00:00'),
(9, 65, 'Standard', 24526.00, 17658.72, 12.00, 2.00, 1, 0, '2026-04-05 05:00:00'),
(10, 66, 'Standard', 72566.00, 47167.90, 10.00, 2.00, 1, 0, '2026-04-05 05:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `transaction_id` int(11) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `date` varchar(100) NOT NULL,
  `suplier` varchar(100) NOT NULL,
  `remarks` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`transaction_id`, `invoice_number`, `date`, `suplier`, `remarks`) VALUES
(1, 'PI-UG-0001', '04/01/26', 'Kampala Hardware Mart', 'Monthly restock'),
(2, 'PI-UG-0002', '04/01/26', 'Nile Distributors Ltd', 'Chains and hinges'),
(3, 'PI-UG-0003', '04/02/26', 'Mukono Steel & Locks', 'Locks bulk order'),
(4, 'PI-UG-0004', '04/02/26', 'Mbale Hardware Hub', 'Handles shipment'),
(5, 'PI-UG-0005', '04/03/26', 'Mbarara Builders Supply', 'Tools reorder'),
(6, 'PI-UG-0006', '04/03/26', 'Entebbe Tools Depot', 'Fasteners & tools'),
(7, 'PI-UG-0007', '04/04/26', 'Masaka Timber & Metal', 'Cabinet hardware'),
(8, 'PI-UG-0008', '04/04/26', 'Arua Building Stores', 'Construction stock'),
(9, 'PI-UG-0009', '04/05/26', 'Fort Portal Suppliers', 'Alarm systems'),
(10, 'PI-UG-0010', '04/05/26', 'Gulu Trade Center', 'Door accessories');

-- --------------------------------------------------------

--
-- Table structure for table `purchases_item`
--

CREATE TABLE `purchases_item` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL,
  `cost` varchar(100) NOT NULL,
  `invoice` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `purchases_item`
--

INSERT INTO `purchases_item` (`id`, `name`, `qty`, `cost`, `invoice`) VALUES
(1, 'kenyos black', 20, '33558', 'PI-UG-0001'),
(2, 'lak b', 15, '23817', 'PI-UG-0001'),
(3, 'Chain 20K', 25, '13060', 'PI-UG-0002'),
(4, 'Black Handle 30K', 30, '18180', 'PI-UG-0003'),
(5, 'Screws', 100, '36045', 'PI-UG-0004'),
(6, 'Drawer 808', 40, '1591', 'PI-UG-0005'),
(7, 'WOHU 32', 30, '1610', 'PI-UG-0006'),
(8, 'ELEPHANT LOCK', 35, '1510', 'PI-UG-0007'),
(9, 'ALARM BIG', 10, '19440', 'PI-UG-0009'),
(10, 'BICYCLE LOCK S', 25, '3975', 'PI-UG-0010');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `po_id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expected_delivery` date DEFAULT NULL,
  `status` enum('pending','received','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`po_id`, `po_number`, `supplier_id`, `created_by`, `order_date`, `expected_delivery`, `status`, `total_amount`, `notes`) VALUES
(1, 'PO-UG-2026-001', 1, 1, '2026-04-01 06:00:00', '2026-04-05', 'received', 450000.00, 'Restock locks & handles'),
(2, 'PO-UG-2026-002', 2, 1, '2026-04-01 07:00:00', '2026-04-06', 'received', 320000.00, 'Chains shipment'),
(3, 'PO-UG-2026-003', 3, 1, '2026-04-02 08:00:00', '2026-04-08', 'received', 280000.00, 'Tools reorder'),
(4, 'PO-UG-2026-004', 4, 1, '2026-04-02 09:00:00', '2026-04-09', 'pending', 190000.00, 'Door accessories'),
(5, 'PO-UG-2026-005', 5, 1, '2026-04-03 06:30:00', '2026-04-07', 'received', 500000.00, 'Locks bulk'),
(6, 'PO-UG-2026-006', 6, 1, '2026-04-03 10:10:00', '2026-04-10', 'pending', 210000.00, 'Handles'),
(7, 'PO-UG-2026-007', 7, 1, '2026-04-04 05:50:00', '2026-04-12', 'pending', 150000.00, 'Cabinet hardware'),
(8, 'PO-UG-2026-008', 8, 1, '2026-04-04 07:15:00', '2026-04-12', 'received', 220000.00, 'Tools & fasteners'),
(9, 'PO-UG-2026-009', 9, 1, '2026-04-05 06:05:00', '2026-04-13', 'pending', 300000.00, 'Construction items'),
(10, 'PO-UG-2026-010', 10, 1, '2026-04-05 08:45:00', '2026-04-14', 'received', 260000.00, 'Alarm systems');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `poi_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`poi_id`, `po_id`, `product_id`, `variant_id`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 58, 1, 10.00, 49350.00, 493500.00),
(2, 1, 59, 3, 5.00, 28058.00, 140290.00),
(3, 2, 121, NULL, 20.00, 18139.00, 362780.00),
(4, 3, 77, NULL, 30.00, 55455.00, 1663650.00),
(5, 4, 118, NULL, 15.00, 9053.00, 135795.00),
(6, 5, 66, 10, 8.00, 72566.00, 580528.00),
(7, 5, 65, 9, 12.00, 24526.00, 294312.00),
(8, 6, 80, NULL, 25.00, 2122.00, 53050.00),
(9, 6, 81, NULL, 20.00, 2147.00, 42940.00),
(10, 7, 134, NULL, 40.00, 653.00, 26120.00),
(11, 8, 132, NULL, 50.00, 1213.00, 60650.00),
(12, 8, 89, NULL, 30.00, 2300.00, 69000.00),
(13, 9, 95, NULL, 12.00, 40000.00, 480000.00),
(14, 10, 102, NULL, 6.00, 27000.00, 162000.00),
(15, 10, 103, NULL, 8.00, 14000.00, 112000.00);

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `return_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`return_id`, `sale_id`, `invoice_number`, `product_id`, `variant_id`, `quantity`, `refund_amount`, `reason`, `approved_by`, `return_date`) VALUES
(1, 142, 'RS-22290005', 73, NULL, 1.00, 25488.00, 'Wrong size', 1, '2026-04-03 07:20:00'),
(2, 143, 'RS-23330224', 127, NULL, 1.00, 25250.00, 'Customer changed mind', 1, '2026-04-05 09:10:00'),
(3, 144, 'RS-23330224', 127, NULL, 1.00, 25250.00, 'Damaged packaging', 1, '2026-04-05 12:40:00'),
(4, 142, 'RS-22290005', 71, NULL, 1.00, 25488.00, 'Faulty lock', 1, '2026-04-04 06:10:00'),
(5, 143, 'RS-23330224', 80, NULL, 2.00, 4244.00, 'Scratched item', 1, '2026-04-06 08:00:00'),
(6, 143, 'RS-23330224', 79, NULL, 1.00, 2014.00, 'Wrong colour', 1, '2026-04-06 11:20:00'),
(7, 144, 'RS-23330224', 81, NULL, 1.00, 2147.00, 'Packaging torn', 1, '2026-04-06 13:05:00'),
(8, 142, 'RS-22290005', 65, NULL, 1.00, 24526.00, 'Loose handle', 1, '2026-04-07 05:45:00'),
(9, 142, 'RS-22290005', 64, NULL, 1.00, 23451.00, 'Defective', 1, '2026-04-07 07:30:00'),
(10, 143, 'RS-23330224', 63, NULL, 1.00, 25486.00, 'Size mismatch', 1, '2026-04-07 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `transaction_id` int(11) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `cashier` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `sale_type` enum('counter','delivery') NOT NULL DEFAULT 'counter',
  `delivery_address` text DEFAULT NULL,
  `delivery_status` enum('pending','dispatched','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `date` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `debt_status` enum('good','doubtful','bad') NOT NULL DEFAULT 'good',
  `amount` varchar(100) NOT NULL,
  `profit` varchar(100) NOT NULL,
  `due_date` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `balance` varchar(100) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `vat_amount` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`transaction_id`, `invoice_number`, `cashier`, `user_id`, `customer_id`, `customer_name`, `customer_phone`, `sale_type`, `delivery_address`, `delivery_status`, `date`, `type`, `debt_status`, `amount`, `profit`, `due_date`, `name`, `balance`, `subtotal`, `vat_amount`, `total_amount`, `amount_paid`, `change_amount`, `created_at`) VALUES
(142, 'RS-22290005', 'Jaawu', 4, NULL, NULL, '0705447791', 'counter', '', 'delivered', '04/02/26', 'cash', 'good', '30075.84', '7136.64', '50000', 'Hassan Harman', '', 25488.00, 4587.84, 30075.84, 50000.00, 19924.16, '2026-04-02 18:04:54'),
(143, 'RS-23330224', 'Jaawu', 4, NULL, NULL, '0705447791', 'counter', '', 'delivered', '04/04/26', 'cash', 'good', '29795', '7070', '50000', 'Hafie', '', 25250.00, 4545.00, 29795.00, 50000.00, 20205.00, '2026-04-04 15:05:24'),
(144, 'RS-23330224', 'Jaawu', 4, NULL, NULL, '0705447791', 'counter', '', 'delivered', '04/04/26', 'cash', 'good', '29795', '7070', '50000', 'Hafie', '', 25250.00, 4545.00, 29795.00, 50000.00, 20205.00, '2026-04-04 15:15:56'),
(145, 'RS-30002608', 'Admin', 0, NULL, NULL, '', 'counter', '', 'delivered', '04/05/26', 'cash', 'good', '484234', '7136.08', '30000', 'summa', '', 25486.00, 458748.00, 484234.00, 30000.00, -454234.00, '2026-04-05 09:12:47'),
(146, 'RS-32023278', 'Jaawu', 4, NULL, NULL, '0705447791', 'counter', '', 'delivered', '04/05/26', 'cash', 'good', '5326574', '78496.88', '6000000', 'Muzammil', '', 280346.00, 5046228.00, 5326574.00, 6000000.00, 673426.00, '2026-04-05 09:55:03'),
(147, 'RS-930', 'Admin', 0, NULL, NULL, '0705447791', 'counter', 'GAYAZA', 'delivered', '04/29/26', 'cash', 'good', '533102', '7856.24', '3', 'Hassan Harman', '', 28058.00, 505044.00, 533102.00, 3.00, -533099.00, '2026-04-29 19:20:57'),
(148, 'RS-03335035', 'Jaawu', 4, NULL, NULL, '', 'counter', '', 'delivered', '04/29/26', 'cash', 'good', '38266', '503.5', '40000', '', '', 2014.00, 36252.00, 38266.00, 40000.00, 1734.00, '2026-04-29 20:46:48');

-- --------------------------------------------------------

--
-- Table structure for table `sales_order`
--

CREATE TABLE `sales_order` (
  `transaction_id` int(11) NOT NULL,
  `invoice` varchar(100) NOT NULL,
  `product` varchar(100) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `profit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_price_at_sale` decimal(10,2) DEFAULT NULL,
  `product_code` varchar(150) NOT NULL,
  `gen_name` varchar(200) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `sales_order`
--

INSERT INTO `sales_order` (`transaction_id`, `invoice`, `product`, `product_id`, `variant_id`, `qty`, `unit_price`, `amount`, `profit`, `cost_price_at_sale`, `product_code`, `gen_name`, `name`, `price`, `discount`, `date`) VALUES
(315, 'RS-22290005', '73', 73, NULL, 1.00, 25488.00, 25488.00, 7136.64, 18351.36, '', NULL, '153(20)', 25488.00, 0.00, '04/02/26'),
(316, 'RS-23330224', '127', 127, NULL, 1.00, 25250.00, 25250.00, 7070.00, 18180.00, '', NULL, 'BLACK HANDLE 30K', 25250.00, 0.00, '04/04/26'),
(317, 'RS-30002608', '63', 63, 7, 1.00, 25486.00, 25486.00, 7136.08, 18349.92, '', 'lak s', 'lak s - Standard', 25486.00, 0.00, '04/05/26'),
(318, 'RS-32023278', '63', 63, 7, 11.00, 25486.00, 280346.00, 78496.88, 18349.92, '', 'lak s', 'lak s - Standard', 25486.00, 0.00, '04/05/26'),
(319, 'RS-52300', '59', 59, 3, 1.00, 28058.00, 28058.00, 7856.24, 20201.76, '', 'tony', 'tony - Standard', 28058.00, 0.00, '04/29/26'),
(320, 'RS-930', '59', 59, 3, 1.00, 28058.00, 28058.00, 7856.24, 20201.76, '', 'tony', 'tony - Standard', 28058.00, 0.00, '04/29/26'),
(323, 'RS-03335035', '79', 79, NULL, 1.00, 2014.00, 2014.00, 503.50, 1510.50, '', 'ELEPHANT LOCK', 'ELEPHANT LOCK', 2014.00, 0.00, '04/29/26'),
(324, 'RS-26340824', '71', 71, NULL, 1.00, 25488.00, 25488.00, 7136.64, 18351.36, '', '155(20)', '155(20)', 25488.00, 0.00, '04/29/26');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('agency_customer_funds_excluded', '0', '2026-05-25 11:58:56'),
('company_address', 'Nakasero, Kampala, Uganda', '2026-04-05 06:20:00'),
('company_email', 'info@erechardware.ug', '2026-04-05 06:20:00'),
('company_name', 'EREC Hardware & POS', '2026-04-05 06:20:00'),
('company_phone', '+256 705 447 791', '2026-04-05 06:20:00'),
('currency', 'UGX', '2026-04-05 06:20:00'),
('gold_price_per_gram_24k', '0', '2026-05-25 11:58:56'),
('low_stock_threshold', '2', '2026-04-05 06:20:00'),
('printed_by_label', 'Served By', '2026-04-05 06:20:00'),
('receipt_footer', 'Thank you for shopping — Katonda asimye', '2026-04-05 06:20:00'),
('support_phone', '+256 782 334 455', '2026-04-05 06:20:00'),
('tax_inclusive', '0', '2026-04-05 06:20:00'),
('timezone', 'Africa/Kampala', '2026-04-05 06:20:00'),
('vat_rate', '18', '2026-04-05 06:20:00'),
('zakat_anniversary_date', '', '2026-05-25 11:58:56');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `movement_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `movement_type` enum('purchase','sale','return','adjustment','supplier_return') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `previous_stock` decimal(10,2) DEFAULT NULL,
  `new_stock` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`movement_id`, `product_id`, `variant_id`, `movement_type`, `quantity`, `reference_id`, `reference_type`, `previous_stock`, `new_stock`, `notes`, `created_by`, `created_at`) VALUES
(1, 73, NULL, 'sale', 1.00, 142, 'sale', 81.00, 80.00, 'Invoice RS-22290005', 4, '2026-04-02 18:04:54'),
(2, 127, NULL, 'sale', 1.00, 143, 'sale', 36.00, 35.00, 'Invoice RS-23330224', 4, '2026-04-04 15:05:24'),
(3, 127, NULL, 'sale', 1.00, 144, 'sale', 36.00, 35.00, 'Invoice RS-23330224', 4, '2026-04-04 15:15:56'),
(4, 63, 7, 'sale', 1.00, 145, 'sale', 15.00, 14.00, 'Invoice RS-30002608', 0, '2026-04-05 09:12:47'),
(5, 63, 7, 'sale', 11.00, 146, 'sale', 14.00, 3.00, 'Invoice RS-32023278', 4, '2026-04-05 09:55:03'),
(6, 59, 3, 'sale', 1.00, 147, 'sale', 24.00, 23.00, 'Invoice RS-930', 0, '2026-04-29 19:20:57'),
(7, 79, NULL, 'sale', 1.00, 148, 'sale', 46.00, 45.00, 'Invoice RS-03335035', 4, '2026-04-29 20:46:48');

-- --------------------------------------------------------

--
-- Table structure for table `supliers`
--

CREATE TABLE `supliers` (
  `suplier_id` int(11) NOT NULL,
  `suplier_name` varchar(100) NOT NULL,
  `suplier_address` varchar(255) DEFAULT NULL,
  `suplier_contact` varchar(50) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `category_supplied` varchar(200) DEFAULT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `performance_rating` int(11) NOT NULL DEFAULT 3,
  `note` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `supliers`
--

INSERT INTO `supliers` (`suplier_id`, `suplier_name`, `suplier_address`, `suplier_contact`, `contact_person`, `email`, `category_supplied`, `min_order_amount`, `performance_rating`, `note`, `is_active`, `created_at`) VALUES
(1, 'Kampala Hardware Mart', 'Nakasero Rd, Kampala', '0772123456', 'Joseph Kato', 'sales@kampalahardware.ug', 'Locks, Handles', 500000.00, 5, 'Fast deliveries in Kampala', 1, '2026-04-01 07:00:00'),
(2, 'Nile Distributors Ltd', 'Jinja Rd, Kampala', '0703554433', 'Mary Namusoke', 'info@niledist.ug', 'Chains, Hinges', 350000.00, 4, 'Reliable wholesale prices', 1, '2026-04-01 07:10:00'),
(3, 'Mbarara Builders Supply', 'High St, Mbarara', '0782334455', 'Paul Tumusiime', 'mbarara@builders.ug', 'Tools, Fasteners', 250000.00, 4, 'Upcountry delivery weekly', 1, '2026-04-01 07:20:00'),
(4, 'Gulu Trade Center', 'Market Rd, Gulu', '0755443322', 'Akello Grace', 'gulu@tradecenter.ug', 'Door Accessories', 200000.00, 3, 'Northern region coverage', 1, '2026-04-01 07:30:00'),
(5, 'Mukono Steel & Locks', 'Seeta, Mukono', '0701223344', 'David Ssemanda', 'orders@mukonosteel.ug', 'Locks, Padlocks', 400000.00, 5, 'Best prices on locks', 1, '2026-04-01 07:40:00'),
(6, 'Mbale Hardware Hub', 'Republic St, Mbale', '0788123456', 'Sarah Wekesa', 'mbale@hardwarehub.ug', 'Handles, Hinges', 180000.00, 4, 'Good after?sales support', 1, '2026-04-01 07:50:00'),
(7, 'Masaka Timber & Metal', 'Buddu Rd, Masaka', '0777778899', 'Yusuf Kizito', 'masaka@timbermetal.ug', 'Cabinet Hardware', 150000.00, 3, 'Small bulk orders ok', 1, '2026-04-01 08:00:00'),
(8, 'Entebbe Tools Depot', 'Airport Rd, Entebbe', '0755332211', 'Janet Achen', 'orders@entebbedepot.ug', 'Tools, Fasteners', 220000.00, 4, 'Fast orders for tools', 1, '2026-04-01 08:10:00'),
(9, 'Arua Building Stores', 'Ediofe Rd, Arua', '0789011223', 'Robert Drani', 'arua@buildingstores.ug', 'Construction Supplies', 300000.00, 3, 'Upcountry bulk supply', 1, '2026-04-01 08:20:00'),
(10, 'Fort Portal Suppliers', 'Bwamba Rd, Fort Portal', '0709112233', 'Phiona K. A', 'fp@suppliers.ug', 'Security Alarms', 260000.00, 4, 'Electronics verified', 1, '2026-04-01 08:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_returns`
--

CREATE TABLE `supplier_returns` (
  `return_id` int(11) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `return_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `supplier_returns`
--

INSERT INTO `supplier_returns` (`return_id`, `po_id`, `supplier_id`, `product_id`, `variant_id`, `quantity`, `reason`, `return_date`) VALUES
(1, 1, 1, 58, 1, 2.00, 'Bent lock body', '2026-04-06 07:00:00'),
(2, 2, 2, 121, NULL, 3.00, 'Rust on chain', '2026-04-06 08:10:00'),
(3, 3, 3, 77, NULL, 5.00, 'Wrong size screws', '2026-04-07 06:00:00'),
(4, 4, 4, 118, NULL, 2.00, 'Damaged carton', '2026-04-07 07:20:00'),
(5, 5, 5, 66, 10, 1.00, 'Broken handle', '2026-04-08 05:30:00'),
(6, 6, 6, 81, NULL, 4.00, 'Loose fit', '2026-04-08 06:15:00'),
(7, 7, 7, 134, NULL, 6.00, 'Scratched plates', '2026-04-09 11:00:00'),
(8, 8, 8, 89, NULL, 3.00, 'Packaging damaged', '2026-04-09 12:00:00'),
(9, 9, 9, 95, NULL, 1.00, 'Wrong model', '2026-04-10 08:00:00'),
(10, 10, 10, 102, NULL, 1.00, 'Faulty alarm unit', '2026-04-10 10:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `password_hash`, `name`, `position`, `is_active`, `created_at`) VALUES
(1, 'admin', 'admin', '$2y$10$n/Q1Bq6fCZxnpRkhh8tdj.O5BcaEF29LiiSOvQFxh88rfRaigsgDK', 'Admin', 'admin', 1, '2026-04-02 12:08:03'),
(2, 'cashier', 'cashier', NULL, 'Cashier Hafie', 'Cashier', 1, '2026-04-02 12:08:03'),
(3, 'admin', 'admin123', NULL, 'Administrator', 'admin', 1, '2026-04-02 12:08:03'),
(4, 'jaawu@gmail.com', '12345678', '$2y$10$s5icxt1xJ3lrEQ7E58Dz/umITkXVgDe9dwqbam3BEvAyza6gSzWKi', 'Jaawu', 'cashier', 1, '2026-04-02 14:42:17'),
(5, 'hafie', 'hafie123', '$2y$10$.XRp7jnvoADcRmmBVMzbWO6ohjbF3dggYFEzGjssw2YoHog4gZXTq', 'Hafie', 'stock_manager', 1, '2026-05-24 19:56:00');

-- --------------------------------------------------------

--
-- Table structure for table `zakat_runs`
--

CREATE TABLE `zakat_runs` (
  `run_id` int(11) NOT NULL,
  `run_date` date NOT NULL,
  `gold_price_per_gram` decimal(12,2) NOT NULL DEFAULT 0.00,
  `nisab_threshold` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_assets` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_liabilities` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_zakat_pool` decimal(12,2) NOT NULL DEFAULT 0.00,
  `zakat_due` decimal(12,2) NOT NULL DEFAULT 0.00,
  `calculation_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `zakat_runs`
--

INSERT INTO `zakat_runs` (`run_id`, `run_date`, `gold_price_per_gram`, `nisab_threshold`, `total_assets`, `total_liabilities`, `net_zakat_pool`, `zakat_due`, `calculation_notes`, `created_at`) VALUES
(1, '2026-05-25', 0.00, 0.00, 57717481.58, 850000.00, 56867481.58, 1421687.04, 'Manual run from Zakat page.', '2026-05-25 11:59:37'),
(2, '2026-06-12', 0.00, 0.00, 57717481.58, 850000.00, 56867481.58, 1421687.04, 'Manual run from Zakat page.', '2026-06-12 02:37:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD PRIMARY KEY (`backup_id`);

--
-- Indexes for table `cash_float`
--
ALTER TABLE `cash_float`
  ADD PRIMARY KEY (`float_id`),
  ADD UNIQUE KEY `uk_cash_float_user_date` (`user_id`,`date`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `uk_categories_name` (`category_name`);

--
-- Indexes for table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `financial_accounts`
--
ALTER TABLE `financial_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD KEY `idx_financial_accounts_type` (`account_type`),
  ADD KEY `idx_financial_accounts_owner` (`funds_owner`);

--
-- Indexes for table `liabilities`
--
ALTER TABLE `liabilities`
  ADD PRIMARY KEY (`liability_id`),
  ADD KEY `idx_liabilities_due` (`due_date`),
  ADD KEY `idx_liabilities_status` (`status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `idx_product_variants_product_id` (`product_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `purchases_item`
--
ALTER TABLE `purchases_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`po_id`),
  ADD UNIQUE KEY `uk_purchase_orders_po_number` (`po_number`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`poi_id`),
  ADD KEY `idx_poi_po_id` (`po_id`),
  ADD KEY `idx_poi_product_id` (`product_id`),
  ADD KEY `idx_poi_variant_id` (`variant_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `sales_order`
--
ALTER TABLE `sales_order`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `idx_stock_movements_product_id` (`product_id`),
  ADD KEY `idx_stock_movements_variant_id` (`variant_id`);

--
-- Indexes for table `supliers`
--
ALTER TABLE `supliers`
  ADD PRIMARY KEY (`suplier_id`);

--
-- Indexes for table `supplier_returns`
--
ALTER TABLE `supplier_returns`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zakat_runs`
--
ALTER TABLE `zakat_runs`
  ADD PRIMARY KEY (`run_id`),
  ADD KEY `idx_zakat_runs_date` (`run_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `backup_logs`
--
ALTER TABLE `backup_logs`
  MODIFY `backup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cash_float`
--
ALTER TABLE `cash_float`
  MODIFY `float_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `collection`
--
ALTER TABLE `collection`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `financial_accounts`
--
ALTER TABLE `financial_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `liabilities`
--
ALTER TABLE `liabilities`
  MODIFY `liability_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `purchases_item`
--
ALTER TABLE `purchases_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `po_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `poi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `sales_order`
--
ALTER TABLE `sales_order`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=325;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `supliers`
--
ALTER TABLE `supliers`
  MODIFY `suplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `supplier_returns`
--
ALTER TABLE `supplier_returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `zakat_runs`
--
ALTER TABLE `zakat_runs`
  MODIFY `run_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
