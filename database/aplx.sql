-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 14, 2025 at 06:57 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aplx`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE IF NOT EXISTS `brands` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `logo_url` varchar(500) NOT NULL,
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `logo_url`, `sort_order`) VALUES
(1, 'Acme Inc', 'https://dummyimage.com/140x50/cccccc/000&text=Acme', 1),
(2, 'Globex', 'https://dummyimage.com/140x50/cccccc/000&text=Globex', 2),
(3, 'Soylent', 'https://dummyimage.com/140x50/cccccc/000&text=Soylent', 3),
(4, 'Initech', 'https://dummyimage.com/140x50/cccccc/000&text=Initech', 4);

-- --------------------------------------------------------

--
-- Table structure for table `call_requests`
--

DROP TABLE IF EXISTS `call_requests`;
CREATE TABLE IF NOT EXISTS `call_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `phone` varchar(60) NOT NULL,
  `message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `portfolios`
--

DROP TABLE IF EXISTS `portfolios`;
CREATE TABLE IF NOT EXISTS `portfolios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `category` varchar(100) DEFAULT 'Logistics',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `portfolios`
--

INSERT INTO `portfolios` (`id`, `title`, `image_url`, `category`, `created_at`) VALUES
(1, 'E-commerce Delivery Network', 'https://picsum.photos/600/400?random=11', 'Delivery', '2025-09-14 05:52:30'),
(2, 'Cold Chain Project', 'https://picsum.photos/600/400?random=12', 'Cold Chain', '2025-09-14 05:52:30'),
(3, 'Warehouse Automation', 'https://picsum.photos/600/400?random=13', 'Warehousing', '2025-09-14 05:52:30');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `excerpt` text,
  `image_url` varchar(500) NOT NULL,
  `published_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `excerpt`, `image_url`, `published_at`) VALUES
(1, 'How to Optimize Your Supply Chain', 'Practical steps to improve logistics efficiency.', 'https://picsum.photos/600/400?random=21', '2025-09-14 11:22:30'),
(2, 'Choosing the Right Freight Option', 'Air, sea, or road? Here is how to decide.', 'https://picsum.photos/600/400?random=22', '2025-09-14 11:22:30'),
(3, 'Warehouse KPIs You Should Track', 'Measure what matters to improve throughput.', 'https://picsum.photos/600/400?random=23', '2025-09-14 11:22:30');

-- --------------------------------------------------------

--
-- Table structure for table `pricing_plans`
--

DROP TABLE IF EXISTS `pricing_plans`;
CREATE TABLE IF NOT EXISTS `pricing_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `period` varchar(30) NOT NULL DEFAULT 'mo',
  `features` text,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pricing_plans`
--

INSERT INTO `pricing_plans` (`id`, `name`, `price`, `period`, `features`, `is_featured`) VALUES
(1, 'Starter', 49.00, 'mo', 'Up to 100 shipments/month\nStandard support', 0),
(2, 'Business', 149.00, 'mo', 'Up to 1,000 shipments/month\nPriority support\nTracking API', 1),
(3, 'Enterprise', 399.00, 'mo', 'Unlimited shipments\nDedicated manager\nCustom SLAs', 0);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `summary` text,
  `icon_class` varchar(100) DEFAULT 'fa fa-truck',
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `title`, `summary`, `icon_class`, `sort_order`) VALUES
(1, 'Freight Shipping', 'Domestic and international freight solutions.', 'fa fa-truck-fast', 1),
(2, 'Warehousing', 'Secure, scalable storage with inventory management.', 'fa fa-warehouse', 2),
(3, 'Last-Mile Delivery', 'Fast urban delivery to your customers.', 'fa fa-location-dot', 3);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `key` varchar(191) NOT NULL,
  `value` text,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `author_name` varchar(120) NOT NULL,
  `author_role` varchar(120) DEFAULT NULL,
  `content` text NOT NULL,
  `avatar_url` varchar(500) DEFAULT 'https://i.pravatar.cc/100',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `author_name`, `author_role`, `content`, `avatar_url`) VALUES
(1, 'Jane Smith', 'E-commerce Manager', 'Logistip improved our delivery times by 30%!', 'https://i.pravatar.cc/100?img=5'),
(2, 'John Doe', 'Operations Lead', 'Reliable and professional service across regions.', 'https://i.pravatar.cc/100?img=12');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
