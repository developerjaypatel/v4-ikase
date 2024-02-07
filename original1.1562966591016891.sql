-- phpMyAdmin SQL Dump
-- version 4.3.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 15, 2017 at 05:32 PM
-- Server version: 5.5.51-38.2
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `infoacco_developer`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_batch_drop_report`
--

CREATE TABLE IF NOT EXISTS `tbl_batch_drop_report` (
  `report_id` int(11) NOT NULL,
  `report_uuid` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `batch_uuid` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `drip_uuid` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `drop_uuid` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `drop_number` int(11) NOT NULL DEFAULT '0',
  `batch_drop_id` int(11) NOT NULL DEFAULT '0',
  `verified` int(11) NOT NULL DEFAULT '0',
  `attempts` int(11) NOT NULL DEFAULT '0',
  `invalid` int(11) NOT NULL DEFAULT '0',
  `unsubscribed` int(11) NOT NULL DEFAULT '0',
  `payment` int(11) NOT NULL DEFAULT '0',
  `planned` int(11) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL,
  `deleted` enum('Y','N','A') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N'
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `tbl_batch_drop_report`
--

INSERT INTO `tbl_batch_drop_report` (`report_id`, `report_uuid`, `batch_uuid`, `drip_uuid`, `drop_uuid`, `drop_number`, `batch_drop_id`, `verified`, `attempts`, `invalid`, `unsubscribed`, `payment`, `planned`, `customer_id`, `deleted`) VALUES
(1, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR567318720e727', 'DR55e1feb53a21d', 1, 1, 0, 8, 0, 1, 1, 1, 1, 'N'),
(2, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR567318720e727', 'DR55e33050da68b', 1, 2, 0, 0, 0, 0, 0, 0, 1, 'N'),
(3, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR567318720e727', 'DR55e32ad991ce5', 1, 3, 0, 16, 0, 2, 2, 1, 1, 'N'),
(4, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR567318720e727', 'DR560b3bb874ce0', 1, 4, 0, 10, 0, 2, 0, 0, 1, 'N'),
(5, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR56c4d76e424b3', 1, 101, 0, 0, 0, 0, 0, 0, 1, 'N'),
(6, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR56c4d8f84337b', 1, 102, 0, 0, 0, 0, 0, 0, 1, 'N'),
(7, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR56995498d6e25', 1, 103, 0, 0, 0, 0, 0, 0, 1, 'N'),
(8, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR564ba18578b46', 1, 104, 0, 0, 0, 0, 0, 0, 1, 'N'),
(9, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR55e339720b479', 1, 105, 0, 0, 0, 0, 0, 0, 1, 'N'),
(10, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR5626c74e1ee07', 1, 106, 0, 0, 0, 0, 0, 0, 1, 'N'),
(11, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR55e3335278c25', 1, 107, 0, 0, 0, 0, 0, 0, 1, 'N'),
(12, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR569d418da9fde', 1, 108, 0, 0, 0, 0, 0, 0, 1, 'N'),
(13, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR569d4076dcf5e', 1, 109, 0, 0, 0, 0, 0, 0, 1, 'N'),
(14, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR55e3311ff2791', 1, 110, 0, 0, 0, 0, 0, 0, 1, 'N'),
(15, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR55e1feb53a21d', 1, 111, 0, 8, 0, 1, 1, 1, 1, 'N'),
(16, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR56214ea58b5d3', 1, 112, 0, 0, 0, 0, 0, 0, 1, 'N'),
(17, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR55e33226716ce', 1, 113, 0, 0, 0, 0, 0, 0, 1, 'N'),
(18, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR55e33050da68b', 1, 114, 0, 0, 0, 0, 0, 0, 1, 'N'),
(19, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR569d467da2001', 1, 115, 0, 0, 0, 0, 0, 0, 1, 'N'),
(20, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR55e332b27e019', 1, 116, 0, 0, 0, 0, 0, 0, 1, 'N'),
(21, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR55e33478568d0', 1, 117, 0, 0, 0, 0, 0, 0, 1, 'N'),
(22, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR55e32ad991ce5', 1, 118, 0, 16, 0, 2, 2, 1, 1, 'N'),
(23, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR560b3bb874ce0', 1, 119, 0, 10, 0, 2, 0, 0, 1, 'N'),
(24, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR564ba18578b46', 1, 120, 0, 0, 0, 0, 0, 0, 1, 'N'),
(25, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR56995498d6e25', 1, 121, 0, 0, 0, 0, 0, 0, 1, 'N'),
(26, 'RE56c3afbfbae4e', 'DR56c3afbfbae4e', 'DR56c4d89f56770', 'DR55e339720b479', 1, 122, 0, 0, 0, 0, 0, 0, 1, 'N');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_batch_drop_report`
--
ALTER TABLE `tbl_batch_drop_report`
  ADD PRIMARY KEY (`report_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_batch_drop_report`
--
ALTER TABLE `tbl_batch_drop_report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=27;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
