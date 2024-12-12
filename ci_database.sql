-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 12, 2024 at 06:54 AM
-- Server version: 10.10.2-MariaDB
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ci_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `id` varchar(250) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Table structure for table `page_load_time`
--

DROP TABLE IF EXISTS `page_load_time`;
CREATE TABLE IF NOT EXISTS `page_load_time` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `page_path` text NOT NULL,
  `load_time` float NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `email` varchar(50) NOT NULL,
  `is_email_validated` tinyint(4) NOT NULL DEFAULT 0,
  `email_validation_hash` text NOT NULL,
  `password` varchar(254) NOT NULL,
  `plain_password` text DEFAULT NULL,
  `is_encrypted` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=no,2=yes',
  `account_creation_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `account_update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Table structure for table `user_class_access`
--

DROP TABLE IF EXISTS `user_class_access`;
CREATE TABLE IF NOT EXISTS `user_class_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller` text NOT NULL,
  `method` text NOT NULL,
  `all_allow` tinyint(4) NOT NULL DEFAULT 2 COMMENT '1=no,2=yes',
  `admin_allow` tinyint(4) NOT NULL DEFAULT 2 COMMENT '1=no,2=yes',
  `monitoring_user_allow` tinyint(4) NOT NULL COMMENT '1=no,2=yes	',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
