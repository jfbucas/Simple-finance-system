-- phpMyAdmin SQL Dump
-- version 3.3.7deb6
-- http://www.phpmyadmin.net
--
-- Host: mysql
-- Generation Time: Oct 13, 2011 at 04:55 PM
-- Server version: 5.1.49
-- PHP Version: 5.3.3-7+squeeze3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `fullname`, `shortname`) VALUES
(1, 'System Administrator', 'sysadmin'),
(2, 'Registrar', 'registrar'),
(5, 'User', 'user'),
(3, 'Finance Officer', 'finofficer'),
(4, 'Member of Finance Office', 'finmember');

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `password`, `fullname`, `email`, `priv`, `phone`) VALUES
('admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrator', 'sysadmin@company.org', '4', '');

--
-- Dumping data for table `users-roles`
--

INSERT INTO `users-roles` (`username`, `role`) VALUES
('admin', 1);


--
-- Dumping data for table `users-sections`
--

INSERT INTO `users-sections` (`username`, `section_id`) VALUES
('admin', 2);

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`id`, `enabled`, `name`, `superapprover`, `headof`, `delegate`, `receptionist`, `address1`, `address2`, `city`, `province`, `country`, `p_code`) VALUES
(1, 'Y', '---Select Section---', '', '', '', '', '', '', '', '', '', ''),
(2, 'Y', 'Test-Section', 'superapprover@company.org', 'headof@company.org', 'delegate@company.org', 'receptionist@company.org', 'address1', 'address2', 'City', 'Province', 'Country', 'PostCode');

--
-- Dumping data for table `section_seq`
--

INSERT INTO `section_seq` (`id`) VALUES
(3);

--
-- Dumping data for table `po_seq`
--

INSERT INTO `po_seq` (`id`) VALUES
(1);

--
-- Dumping data for table `line_items_seq`
--

INSERT INTO `line_items_seq` (`id`) VALUES
(1);

--
-- Dumping data for table `tr_seq`
--

INSERT INTO `tr_seq` (`id`) VALUES
(1);

--
-- Dumping data for table `tr_items_seq`
--

INSERT INTO `tr_items_seq` (`id`) VALUES
(1);

--
-- Dumping data for table `er_seq`
--

INSERT INTO `er_seq` (`id`) VALUES
(1);

--
-- Dumping data for table `er_items_seq`
--

INSERT INTO `er_items_seq` (`id`) VALUES
(1);


--
-- Dumping data for table `vendor_seq_seq`
--

INSERT INTO `vendor_seq` (`id`) VALUES
(1);


--
-- Dumping data for table `vendor_categories`
--

INSERT INTO `vendor_categories` (`id`, `name`) VALUES
('0', '---Select Category---'),
('1', 'Catering'),
('2', 'Electrical');

