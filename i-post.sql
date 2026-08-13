-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 13, 2026 at 07:33 PM
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
-- Database: `i-post`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookmarks`
--

INSERT INTO `bookmarks` (`id`, `post_id`, `user_id`, `created_at`) VALUES
(11, 6, 2, '2026-08-09 18:06:52'),
(12, 4, 2, '2026-08-09 18:06:54'),
(13, 6, 4, '2026-08-11 00:27:30'),
(14, 11, 5, '2026-08-11 01:19:36'),
(15, 14, 1, '2026-08-13 16:35:02'),
(16, 15, 1, '2026-08-13 16:35:03');

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 2, 'hello', 1, '2026-08-11 05:42:02'),
(2, 2, 1, 'hi', 1, '2026-08-11 05:42:57'),
(3, 1, 2, 'sdad', 1, '2026-08-13 15:29:41'),
(4, 1, 2, 'sdada', 1, '2026-08-13 15:29:43'),
(5, 1, 2, 'adad', 1, '2026-08-13 15:29:46'),
(7, 1, 2, 'asdada', 1, '2026-08-13 16:39:30'),
(8, 2, 1, 'ulol', 1, '2026-08-13 17:12:44'),
(9, 2, 5, 'panget', 0, '2026-08-13 17:19:18'),
(10, 2, 5, 'sadadad', 0, '2026-08-13 17:19:20'),
(11, 2, 5, 'dsada', 0, '2026-08-13 17:19:22'),
(12, 2, 5, 'asdsadsad', 0, '2026-08-13 17:19:27'),
(13, 2, 5, 'ssdsdsad', 0, '2026-08-13 17:19:28');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `parent_comment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comment_txt` text NOT NULL,
  `comment_img` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `parent_comment_id`, `comment_txt`, `comment_img`, `created_at`) VALUES
(1, 2, 1, NULL, 'sdadadad', NULL, '2026-08-09 10:03:51'),
(2, 6, 2, NULL, 'haha', NULL, '2026-08-09 12:01:36'),
(3, 5, 2, NULL, 'muka kang', NULL, '2026-08-09 17:57:31'),
(4, 11, 1, NULL, 'mukhang butu yung dalawang yan ah', NULL, '2026-08-11 01:13:58'),
(5, 11, 5, NULL, 'sadsad', NULL, '2026-08-11 01:18:47'),
(6, 15, 1, NULL, 'sdadad', NULL, '2026-08-13 16:12:46'),
(7, 14, 1, NULL, 'sdadad', NULL, '2026-08-13 17:23:51'),
(8, 14, 1, NULL, 'sdadad', NULL, '2026-08-13 17:23:52'),
(9, 14, 1, NULL, 'sdadad', NULL, '2026-08-13 17:23:53');

-- --------------------------------------------------------

--
-- Table structure for table `comment_likes`
--

CREATE TABLE `comment_likes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `comment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comment_likes`
--

INSERT INTO `comment_likes` (`id`, `comment_id`, `user_id`, `created_at`) VALUES
(4, 4, 1, '2026-08-13 16:33:10'),
(5, 6, 1, '2026-08-13 16:49:22');

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `friend_user_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(20) DEFAULT 'accepted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `friends`
--

INSERT INTO `friends` (`id`, `user_id`, `friend_user_id`, `status`, `created_at`) VALUES
(3, 1, 2, 'accepted', '2026-08-09 17:53:37'),
(4, 1, 3, 'pending', '2026-08-09 17:53:40'),
(5, 4, 3, 'pending', '2026-08-11 00:27:12'),
(7, 4, 2, 'accepted', '2026-08-11 00:27:15'),
(8, 5, 2, 'accepted', '2026-08-11 01:19:26'),
(10, 1, 4, 'pending', '2026-08-13 15:30:29'),
(11, 1, 5, 'pending', '2026-08-13 16:04:21'),
(12, 2, 3, 'pending', '2026-08-13 16:05:58');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `actor_id`, `post_id`, `comment_id`, `type`, `is_read`, `created_at`) VALUES
(1, 5, 1, 2, NULL, 'friend_request', 0, '2026-08-13 16:04:21'),
(2, 4, 2, 3, NULL, 'friend_accept', 0, '2026-08-13 16:04:49'),
(3, 3, 2, 9, NULL, 'friend_request', 0, '2026-08-13 16:05:58'),
(4, 2, 1, 15, NULL, 'comment', 1, '2026-08-13 16:12:46'),
(5, 2, 1, 14, NULL, 'comment', 0, '2026-08-13 17:23:51'),
(6, 2, 1, 14, NULL, 'comment', 0, '2026-08-13 17:23:52'),
(7, 2, 1, 14, NULL, 'comment', 0, '2026-08-13 17:23:53');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `post_txt` text DEFAULT NULL,
  `post_img` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `post_txt`, `post_img`, `created_at`) VALUES
(1, 1, 'Hello', NULL, '2026-08-09 09:55:11'),
(2, 1, 'dad', NULL, '2026-08-09 09:55:42'),
(3, 1, 'mama mo', NULL, '2026-08-09 10:05:33'),
(4, 1, 'ssdad', NULL, '2026-08-09 10:05:41'),
(5, 1, 'dsadada', NULL, '2026-08-09 10:23:23'),
(6, 1, 'helo', 'img/uploads/f5cbecf6ccfebb2dfa10b0aacb8b8590.png', '2026-08-09 10:43:30'),
(9, 4, 'idk', NULL, '2026-08-11 00:27:05'),
(10, 4, 'pppp', 'img/uploads/1385e7c4bc8edca1c35e65d72ed3cbd9.png', '2026-08-11 00:28:03'),
(11, 4, 'kupfal', 'img/uploads/443dbd08724156a8019dc04f8f5fdf32.jpg', '2026-08-11 00:29:07'),
(13, 1, 'Panger', 'img/uploads/354d87d0030f5f20108c04265f2b1025.jpg', '2026-08-11 05:34:13'),
(14, 2, 'adasdasdad', NULL, '2026-08-13 16:04:43'),
(15, 2, 'dadad', NULL, '2026-08-13 16:04:46');

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `post_likes`
--

INSERT INTO `post_likes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
(2, 5, 1, '2026-08-09 10:39:07'),
(3, 6, 1, '2026-08-09 10:58:22'),
(4, 6, 2, '2026-08-09 12:01:00'),
(5, 4, 1, '2026-08-09 14:43:44'),
(6, 4, 2, '2026-08-09 18:06:58'),
(7, 2, 2, '2026-08-09 18:07:01'),
(8, 3, 2, '2026-08-09 18:15:13'),
(9, 9, 4, '2026-08-11 00:27:07'),
(10, 11, 4, '2026-08-11 00:29:13'),
(11, 2, 1, '2026-08-11 02:32:51'),
(12, 13, 2, '2026-08-13 15:31:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT 'default_avatar.png',
  `cover_photo` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password_hash`, `bio`, `profile_picture`, `cover_photo`, `location`, `birth_date`, `is_private`, `created_at`, `updated_at`) VALUES
(1, 'Peejay Galang', 'Peejay@gmail.com', '$2y$10$mkluL8WCC0xYOozfWCCyPeKxaHQb6vbDojdoXcMyOfP4KKmZ1xbfG', 'eto ako ngayon nag iisa', 'uploads/profile_1_1786414972.jpg', 'uploads/cover_1_1786639779.png', 'Brgy Pitipiwpiw', NULL, 0, '2026-08-09 05:40:59', '2026-08-13 16:55:39'),
(2, 'Peejay Galang', 'peejaygalang145@gmail.com', '$2y$10$4YdpWsiXqaBI2H2JtHa2RO324vCD4vSb2Zr1Ke87UwduZp9h8eyS6', NULL, 'default_avatar.png', NULL, NULL, NULL, 0, '2026-08-09 05:49:32', '2026-08-09 06:01:37'),
(3, 'Peejay Galang', 'Pijji@gmail.com', '$2y$10$nPPPbXg5qpsQ6sBFru/feevxjhXJLLA.z/Ih0CGn0jXoHjzY0.87a', NULL, 'default_avatar.png', NULL, NULL, NULL, 0, '2026-08-09 05:51:50', '2026-08-09 06:01:37'),
(4, 'rte', 'dfsdff@gmail.com', '$2y$10$pX4FwFhKoE9dazB0tqMY5uWC.4PMrKK46Slaur3emlTnVNvKB1zni', NULL, 'default_avatar.png', NULL, NULL, NULL, 0, '2026-08-11 00:25:44', '2026-08-11 00:25:44'),
(5, 'Rexel Alimurung', 'Rexel@gmail.com', '$2y$10$X.ORXCzgZFizNAAtvEhzCuNzKBcXJh9.Jj/bXRMVLGNBI.fP0A712', NULL, 'default_avatar.png', NULL, NULL, NULL, 0, '2026-08-11 01:16:50', '2026-08-11 01:16:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bookmark` (`post_id`,`user_id`),
  ADD KEY `fk_bookmarks_user` (`user_id`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_chats_receiver` (`receiver_id`),
  ADD KEY `idx_chats_sender_receiver` (`sender_id`,`receiver_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_comments_user` (`user_id`),
  ADD KEY `fk_comments_parent` (`parent_comment_id`),
  ADD KEY `idx_comments_post_id` (`post_id`);

--
-- Indexes for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_comment_like` (`comment_id`,`user_id`),
  ADD KEY `fk_comment_likes_user` (`user_id`);

--
-- Indexes for table `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_friendship` (`user_id`,`friend_user_id`),
  ADD KEY `fk_friends_friend` (`friend_user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_actor` (`actor_id`),
  ADD KEY `fk_notifications_post` (`post_id`),
  ADD KEY `fk_notifications_comment` (`comment_id`),
  ADD KEY `idx_notifications_user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_posts_user_id` (`user_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_post_like` (`post_id`,`user_id`),
  ADD KEY `fk_post_likes_user` (`user_id`),
  ADD KEY `idx_post_likes_post_id` (`post_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `comment_likes`
--
ALTER TABLE `comment_likes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `friends`
--
ALTER TABLE `friends`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `fk_bookmarks_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bookmarks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `fk_chats_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chats_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD CONSTRAINT `fk_comment_likes_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comment_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friends`
--
ALTER TABLE `friends`
  ADD CONSTRAINT `fk_friends_friend` FOREIGN KEY (`friend_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_friends_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notifications_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notifications_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `fk_post_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_post_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
