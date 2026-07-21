-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260412.9edf12e957
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 21, 2026 at 03:12 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clip`
--

-- --------------------------------------------------------

--
-- Table structure for table `boards`
--

CREATE TABLE `boards` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `boards`
--

INSERT INTO `boards` (`id`, `user_id`, `name`, `sort_order`, `created_at`, `updated_at`) VALUES
(36, 1, '絵師向けの使用例１', 0, '2026-07-13 15:48:42', '2026-07-14 13:36:49'),
(44, 1, 'テスト１', 0, '2026-07-14 09:06:38', '2026-07-14 11:53:51'),
(54, 1, '２', 0, '2026-07-14 10:46:59', '2026-07-14 10:46:59'),
(55, 1, '３', 0, '2026-07-14 10:47:02', '2026-07-14 10:47:02'),
(56, 1, '４', 0, '2026-07-14 10:47:05', '2026-07-14 10:47:05'),
(66, 1, 'さかな', 0, '2026-07-14 13:46:48', '2026-07-14 13:46:48');

-- --------------------------------------------------------

--
-- Table structure for table `board_tags`
--

CREATE TABLE `board_tags` (
  `board_id` int NOT NULL,
  `tag_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `board_tags`
--

INSERT INTO `board_tags` (`board_id`, `tag_id`) VALUES
(36, 3);

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE `cards` (
  `id` int NOT NULL,
  `board_id` int NOT NULL,
  `type` enum('image','url','text') NOT NULL,
  `pos_x` int NOT NULL DEFAULT '0',
  `pos_y` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `content` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cards`
--

INSERT INTO `cards` (`id`, `board_id`, `type`, `pos_x`, `pos_y`, `created_at`, `content`) VALUES
(111, 36, 'url', 190, 431, '2026-07-13 16:04:40', NULL),
(126, 36, 'text', 214, 132, '2026-07-13 16:14:14', '作業中に聞く配信'),
(127, 36, 'url', 168, 174, '2026-07-13 16:15:07', NULL),
(142, 44, 'url', 145, 68, '2026-07-14 09:10:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `card_images`
--

CREATE TABLE `card_images` (
  `id` int NOT NULL,
  `card_id` int NOT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `card_urls`
--

CREATE TABLE `card_urls` (
  `id` int NOT NULL,
  `card_id` int NOT NULL,
  `url` varchar(2083) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `thumbnail_url` varchar(2083) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `card_urls`
--

INSERT INTO `card_urls` (`id`, `card_id`, `url`, `title`, `thumbnail_url`) VALUES
(8, 111, 'https://x.com/nyum_serori', 'にゅむ＠c108_2日目西ひ-01ab (@nyum_serori) on X', 'https://pbs.twimg.com/profile_images/1886273472743247872/gJoAe6nN_200x200.jpg'),
(14, 127, 'https://www.youtube.com/live/kA4IybS21Kc?si=MRTL4BLQtgKnnmKG', '【#しぐれういスプリング2026】安価で春服考えようぜ', 'https://i.ytimg.com/vi/kA4IybS21Kc/maxresdefault.jpg'),
(18, 142, 'https://youtu.be/mhWYv-Difv8?si=HmcMiz0KXIE7CeN0', 'どうしてもオタクを風呂に入れたいコラボ銭湯【学マス×極楽湯】', 'https://i.ytimg.com/vi/mhWYv-Difv8/maxresdefault.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#888888',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `user_id`, `name`, `color`, `created_at`) VALUES
(3, 1, 'キャラデザ', '#E05555', '2026-07-16 06:10:39'),
(5, 1, '７月', '#9955E0', '2026-07-21 00:43:52'),
(6, 1, 'ノルムー', '#5588E0', '2026-07-21 00:44:12'),
(7, 1, 'ゼンゼロ', '#55A855', '2026-07-21 00:44:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `password`) VALUES
(1, '$2y$10$r0bdTwXCgfDXgTXTNQFm7ehY9Q0jgWR/dgxLBod4L8BD5gcVyYpcq');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `boards`
--
ALTER TABLE `boards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `board_tags`
--
ALTER TABLE `board_tags`
  ADD PRIMARY KEY (`board_id`,`tag_id`);

--
-- Indexes for table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `board_id` (`board_id`);

--
-- Indexes for table `card_images`
--
ALTER TABLE `card_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `card_id` (`card_id`);

--
-- Indexes for table `card_urls`
--
ALTER TABLE `card_urls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `card_id` (`card_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `boards`
--
ALTER TABLE `boards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT for table `card_images`
--
ALTER TABLE `card_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `card_urls`
--
ALTER TABLE `card_urls`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `boards`
--
ALTER TABLE `boards`
  ADD CONSTRAINT `boards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cards`
--
ALTER TABLE `cards`
  ADD CONSTRAINT `cards_ibfk_1` FOREIGN KEY (`board_id`) REFERENCES `boards` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `card_images`
--
ALTER TABLE `card_images`
  ADD CONSTRAINT `card_images_ibfk_1` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `card_urls`
--
ALTER TABLE `card_urls`
  ADD CONSTRAINT `card_urls_ibfk_1` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
