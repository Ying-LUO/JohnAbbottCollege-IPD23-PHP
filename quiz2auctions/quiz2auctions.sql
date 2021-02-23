-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3333
-- Generation Time: Feb 23, 2021 at 07:07 PM
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
-- Database: `quiz2auctions`
--

-- --------------------------------------------------------

--
-- Table structure for table `auctions`
--

CREATE TABLE `auctions` (
  `id` int(11) NOT NULL,
  `itemDesc` varchar(200) NOT NULL,
  `sellerEmail` varchar(320) NOT NULL,
  `lastBid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `lastBidderEmail` varchar(320) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `auctions`
--

INSERT INTO `auctions` (`id`, `itemDesc`, `sellerEmail`, `lastBid`, `lastBidderEmail`) VALUES
(1, 'test auction test auction test auction', 'test@auction.com', '121.00', 'shinld@hfd.com'),
(2, 'New Test Auction', 'testnew@newauction.com', '10.00', 'test@dab.com'),
(3, 'another auction', 'another@tesdt.com', '0.00', ''),
(4, 'fdsfcds', 'dsad@gfdvf.com', '0.00', ''),
(5, 'tes cfjdso', 'vmdb@hop.com', '0.00', ''),
(6, 'vfdvf', 'vc@gfa.com', '0.00', ''),
(7, 'fdp fnfwdpo ncds', 'fhid@ifd.com', '100.00', 'fhi@bids.com'),
(8, 's c p cps csa', 'aaa@mmm.com', '0.00', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auctions`
--
ALTER TABLE `auctions`
  ADD PRIMARY KEY (`id`);

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
