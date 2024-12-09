-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 09, 2024 at 09:41 PM
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
-- Database: `comics_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `email`, `username`, `password`) VALUES
(1, 'admin1@email.com', 'admin1', '$2y$10$qJuOQULLKmUmw4o4wBz0FObNXf/r41zKUx7Oi70zSKFkFFyr3Ayh6');

-- --------------------------------------------------------

--
-- Table structure for table `comics`
--

CREATE TABLE `comics` (
  `comic_id` int(11) NOT NULL,
  `series` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `picture` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comics`
--

INSERT INTO `comics` (`comic_id`, `series`, `title`, `issue_date`, `picture`, `user_id`) VALUES
(9, 'Ultimate Spider-Man', 'issue 2', '2000-10-04', 'ultimate_spiderman_2.jpg', 4),
(10, 'Ultimate Spider-Man', 'issue 3', '2000-11-01', 'ultimate_spiderman_3.jpg', 4),
(16, 'Ultimate Spider-Man', 'issue 1: Powerless', '2000-09-07', 'ultimate_spiderman_1.jpg', 5),
(17, 'Monster High: New Scaremester', 'Issue 1', '2024-08-07', 'comic_674e4d57f174e8.22701416.jpg', 6),
(24, 'Wolverine', 'issue 1', '2024-09-11', 'comic_674f78abb9b626.50296037.jpg', 5),
(35, 'Punisher', 'issue 1', '2022-03-09', 'comic_6751ce82e0a613.32972267.jpg', 4),
(37, 'Ultimate Spider-Man', 'issue 1: Powerless', '2000-09-06', 'comic_6751d07ed0d233.72121896.jpg', 4),
(38, 'loki', 'issue 1', '2023-06-07', 'comic_6756243d7f71d2.40388143.jpg', 7),
(41, 'Ultimate Spider-Man', 'issue 5', '2001-01-01', 'comic_67575561ae64a4.81129211.webp', 4);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`) VALUES
(4, 'jake', '$2y$10$drOEzLAngzicyUBbJG9whOtXWbe/HJHzKGjigK40J3gv9.Npcx8i2'),
(5, 'spiderman', '$2y$10$c98mFfEjuJz6pEJ/wSKW5OYaDkllJg7LXu6LN13mLAvDAvuSif2Ay'),
(6, 'Emmar', '$2y$10$HASm9M4DDqgGBXbXuKXfYOP9e8VoOISr3U7FvrSd/WnJe3fTIwvPO'),
(7, 'madelyn', '$2y$10$W6wfl3aeZ0c4PA0wk.mZAOT1StJkXH4UIYjrwRfM4u6SLUmZH0Mgm');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `comics`
--
ALTER TABLE `comics`
  ADD PRIMARY KEY (`comic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `comics`
--
ALTER TABLE `comics`
  MODIFY `comic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comics`
--
ALTER TABLE `comics`
  ADD CONSTRAINT `comics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
