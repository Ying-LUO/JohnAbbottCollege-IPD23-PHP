-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3333
-- Generation Time: Feb 09, 2021 at 07:34 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quiz1auctions`
--

-- --------------------------------------------------------

--
-- Table structure for table `auctions`
--

CREATE TABLE `auctions` (
  `id` int(11) NOT NULL,
  `itemDescription` varchar(1000) NOT NULL,
  `itemImagePath` varchar(200) NOT NULL,
  `sellersName` varchar(100) NOT NULL,
  `sellersEmail` varchar(320) NOT NULL,
  `lastBidPrice` decimal(10,2) NOT NULL,
  `lastBidderName` varchar(50) DEFAULT NULL,
  `lastBidderEmail` varchar(320) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `auctions`
--

INSERT INTO `auctions` (`id`, `itemDescription`, `itemImagePath`, `sellersName`, `sellersEmail`, `lastBidPrice`, `lastBidderName`, `lastBidderEmail`) VALUES
(3, '<p>dfsGAFHTRHTGRJNRH</p>', 'uploads/images.png', 'dcdsfe-34', 'sda@gdsf.com', '1324.00', NULL, NULL),
(5, '<p>fsdfsdgdsgsd</p>', 'uploads/download.jpg', 'dcdsfe-34', 'scopic78@gmail.com', '2343.00', NULL, NULL),
(6, '<p>fdsfdfds</p>', 'uploads/test.jpg', 'dcdsfe-34', 'scopic78@gmail.com', '234324.00', NULL, NULL),
(8, '<p>cdfsfda</p>', 'uploads/test.png', 'dcdsfe-34', 'scopic78@gmail.com', '121.00', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auctions`
--
ALTER TABLE `auctions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `itemImagePath` (`itemImagePath`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auctions`
--
ALTER TABLE `auctions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
