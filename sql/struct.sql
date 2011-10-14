-- phpMyAdmin SQL Dump
-- version 3.3.7deb6
-- http://www.phpmyadmin.net
--
-- Host: mysql
-- Generation Time: Oct 13, 2011 at 04:54 PM
-- Server version: 5.1.49
-- PHP Version: 5.3.3-7+squeeze3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `simple-finance-system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attached_document`
--

CREATE TABLE IF NOT EXISTS `attached_document` (
  `category` enum('PO','TR','ER') NOT NULL,
  `draft_number` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(150) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `tag1` int(11) NOT NULL DEFAULT '0',
  `tag2` int(11) NOT NULL DEFAULT '0',
  `tag3` int(11) NOT NULL DEFAULT '0',
  `tag4` int(11) NOT NULL DEFAULT '0',
  `tag5` int(11) NOT NULL DEFAULT '0',
  `tag6` int(11) NOT NULL DEFAULT '0',
  `tag7` int(11) NOT NULL DEFAULT '0',
  `tag8` int(11) NOT NULL DEFAULT '0',
  `comments` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `config_seq`
--

CREATE TABLE IF NOT EXISTS `config_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE IF NOT EXISTS `equipment` (
  `tag` int(11) NOT NULL DEFAULT '0',
  `make` varchar(30) NOT NULL DEFAULT '',
  `model` varchar(30) NOT NULL DEFAULT '',
  `serial` varchar(30) NOT NULL DEFAULT '',
  `vendor` int(11) NOT NULL DEFAULT '0',
  `price` decimal(9,2) NOT NULL DEFAULT '0.00',
  `warranty` date DEFAULT NULL,
  `descrip` text,
  `equip_type` int(11) DEFAULT NULL,
  PRIMARY KEY (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_type`
--

CREATE TABLE IF NOT EXISTS `equipment_type` (
  `id` int(11) NOT NULL DEFAULT '0',
  `descrip` varchar(48) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_type_seq`
--

CREATE TABLE IF NOT EXISTS `equipment_type_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `er`
--

CREATE TABLE IF NOT EXISTS `er` (
  `date` date NOT NULL DEFAULT '0000-00-00',
  `status` enum('Open','Requested','Approved','Closed','Canceled') NOT NULL DEFAULT 'Open',
  `created_by` varchar(128) DEFAULT NULL,
  `checked_by` varchar(128) DEFAULT NULL,
  `approved_by` varchar(128) DEFAULT NULL,
  `section` int(11) NOT NULL DEFAULT '0',
  `draft_number` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `tr_draft_number` int(11) DEFAULT NULL,
  `overexpense` text,
  PRIMARY KEY (`draft_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=53 ;

-- --------------------------------------------------------

--
-- Table structure for table `er_items`
--

CREATE TABLE IF NOT EXISTS `er_items` (
  `id` int(11) NOT NULL DEFAULT '0',
  `receipt` int(11) DEFAULT NULL,
  `type` enum('Advance','Transport','AccomodationBreakfast','Accomodation','Subsistence-5h','Subsistence-10h','Conference-Fee','Abstract-Fee','Other') NOT NULL DEFAULT 'Transport',
  `price` decimal(9,2) NOT NULL DEFAULT '0.00',
  `quantity` int(11) NOT NULL DEFAULT '1',
  `currency` enum('e','l','d','c','o') NOT NULL DEFAULT 'e',
  `exchangerate` float NOT NULL DEFAULT '1',
  `description` varchar(255) NOT NULL DEFAULT '',
  `comment` varchar(255) DEFAULT NULL,
  `prepaid` enum('Y','N') NOT NULL DEFAULT 'N',
  `draft_number` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `draft_number` (`draft_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `er_items_seq`
--

CREATE TABLE IF NOT EXISTS `er_items_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `er_seq`
--

CREATE TABLE IF NOT EXISTS `er_seq` (
  `id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE IF NOT EXISTS `invoices` (
  `inv_number` int(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `organization` int(11) NOT NULL DEFAULT '0',
  `gl_credit` varchar(12) NOT NULL DEFAULT '',
  `gl_debit` varchar(12) NOT NULL DEFAULT '',
  `comments` text,
  `open` enum('Y','N') NOT NULL DEFAULT 'Y',
  `tax1` decimal(9,2) NOT NULL DEFAULT '0.00',
  `tax2` decimal(9,2) NOT NULL DEFAULT '0.00',
  `total` decimal(9,2) NOT NULL DEFAULT '0.00',
  `created_by` varchar(16) DEFAULT NULL,
  `approved` enum('Y','N') NOT NULL DEFAULT 'N',
  `approved_by` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`inv_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `invoices_seq`
--

CREATE TABLE IF NOT EXISTS `invoices_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `inv_lines`
--

CREATE TABLE IF NOT EXISTS `inv_lines` (
  `id` int(11) NOT NULL DEFAULT '0',
  `inv_number` int(11) NOT NULL DEFAULT '0',
  `rcv_date` date NOT NULL DEFAULT '0000-00-00',
  `rcv_by` varchar(30) DEFAULT NULL,
  `qty` decimal(9,1) NOT NULL DEFAULT '0.0',
  `descrip` varchar(255) NOT NULL DEFAULT '',
  `unit_price` decimal(9,2) NOT NULL DEFAULT '0.00',
  `amount` decimal(9,2) NOT NULL DEFAULT '0.00',
  `taxable` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`),
  KEY `inv_number` (`inv_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `inv_lines_seq`
--

CREATE TABLE IF NOT EXISTS `inv_lines_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `item_master`
--

CREATE TABLE IF NOT EXISTS `item_master` (
  `id` int(11) NOT NULL DEFAULT '0',
  `make` varchar(30) NOT NULL DEFAULT '',
  `model` varchar(30) NOT NULL DEFAULT '',
  `serial` varchar(50) NOT NULL DEFAULT '',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `warranty` date NOT NULL DEFAULT '0000-00-00',
  `descrip` text,
  `po_approved_number` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `serial` (`serial`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `item_master_seq`
--

CREATE TABLE IF NOT EXISTS `item_master_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `line_items`
--

CREATE TABLE IF NOT EXISTS `line_items` (
  `id` int(11) NOT NULL DEFAULT '0',
  `po_number` int(11) NOT NULL DEFAULT '0',
  `qty` int(11) NOT NULL DEFAULT '0',
  `inv_qty` int(11) NOT NULL DEFAULT '0',
  `unit` varchar(10) DEFAULT 'each',
  `descrip` varchar(255) NOT NULL DEFAULT '',
  `alloc` varchar(16) DEFAULT NULL,
  `unit_price` decimal(9,2) NOT NULL DEFAULT '0.00',
  `amount` decimal(9,2) NOT NULL DEFAULT '0.00',
  `received` enum('Y','N') NOT NULL DEFAULT 'N',
  `invoiced` enum('Y','P','N') NOT NULL DEFAULT 'N',
  `draft_number` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `po_number` (`po_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `line_items_seq`
--

CREATE TABLE IF NOT EXISTS `line_items_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `category` enum('PO','TR','ER') NOT NULL,
  `draft_number` int(11) DEFAULT '0',
  `date` datetime NOT NULL,
  `level` enum('info','warn','crit') NOT NULL,
  `message` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `po`
--

CREATE TABLE IF NOT EXISTS `po` (
  `date` date NOT NULL DEFAULT '0000-00-00',
  `vendor` int(11) NOT NULL DEFAULT '0',
  `open` enum('Y','N','C') NOT NULL DEFAULT 'Y',
  `created_by` varchar(128) DEFAULT NULL,
  `approved` enum('Y','N') NOT NULL DEFAULT 'N',
  `approved_by` varchar(128) DEFAULT NULL,
  `section` int(11) NOT NULL DEFAULT '0',
  `draft_number` int(11) NOT NULL AUTO_INCREMENT,
  `po_approved_number` varchar(12) DEFAULT NULL,
  `sent_to_supplier` enum('Y','N') NOT NULL DEFAULT 'N',
  `paid` enum('Y','N') NOT NULL DEFAULT 'N',
  `currency` enum('e','l','d','c') NOT NULL DEFAULT 'e',
  `delivery` decimal(9,2) NOT NULL DEFAULT '0.00',
  `vat` enum('a','b','c','d') NOT NULL DEFAULT 'a',
  PRIMARY KEY (`draft_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11767 ;

-- --------------------------------------------------------

--
-- Table structure for table `po_comments`
--

CREATE TABLE IF NOT EXISTS `po_comments` (
  `draft_number` int(11) NOT NULL,
  `comment` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Comment of the PO - added by JFB';

-- --------------------------------------------------------

--
-- Table structure for table `po_seq`
--

CREATE TABLE IF NOT EXISTS `po_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(50) NOT NULL DEFAULT '',
  `shortname` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE IF NOT EXISTS `section` (
  `id` int(11) NOT NULL DEFAULT '0',
  `enabled` set('Y','N') NOT NULL DEFAULT 'Y',
  `name` varchar(50) NOT NULL DEFAULT '',
  `superapprover` varchar(50) NOT NULL,
  `headof` varchar(50) NOT NULL DEFAULT '',
  `delegate` varchar(50) NOT NULL,
  `receptionist` varchar(50) NOT NULL,
  `address1` varchar(100) DEFAULT '',
  `address2` varchar(100) DEFAULT '',
  `city` varchar(50) DEFAULT '',
  `province` varchar(50) DEFAULT '',
  `country` varchar(50) DEFAULT '',
  `p_code` varchar(16) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `section_seq`
--

CREATE TABLE IF NOT EXISTS `section_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `tag` int(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `po` int(11) NOT NULL DEFAULT '0',
  `section` int(11) NOT NULL DEFAULT '0',
  `config` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tr`
--

CREATE TABLE IF NOT EXISTS `tr` (
  `date` date NOT NULL DEFAULT '0000-00-00',
  `status` enum('Open','Requested','Approved','Closed','Canceled') NOT NULL DEFAULT 'Open',
  `created_by` varchar(128) DEFAULT NULL,
  `checked_by` varchar(128) DEFAULT NULL,
  `approved_by` varchar(128) DEFAULT NULL,
  `section` int(11) NOT NULL DEFAULT '0',
  `draft_number` int(11) NOT NULL AUTO_INCREMENT,
  `depart_date` date NOT NULL DEFAULT '0000-00-00',
  `return_date` date NOT NULL DEFAULT '0000-00-00',
  `destination` varchar(100) NOT NULL,
  `purpose` text NOT NULL,
  `maximum_budget` int(11) NOT NULL DEFAULT '0',
  `advance_requested` int(11) NOT NULL DEFAULT '0',
  `advance_transfered` int(11) NOT NULL DEFAULT '0',
  `er_draft_number` int(11) DEFAULT NULL,
  PRIMARY KEY (`draft_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11751 ;

-- --------------------------------------------------------

--
-- Table structure for table `tr_items`
--

CREATE TABLE IF NOT EXISTS `tr_items` (
  `id` int(11) NOT NULL DEFAULT '0',
  `type` enum('Advance','Transport','AccomodationBreakfast','Accomodation','Subsistence-5h','Subsistence-10h','Conference-Fee','Abstract-Fee','Other') NOT NULL DEFAULT 'Transport',
  `price` decimal(9,2) NOT NULL DEFAULT '0.00',
  `quantity` int(11) NOT NULL DEFAULT '1',
  `currency` enum('e','l','d','c','o') NOT NULL DEFAULT 'e',
  `exchangerate` float NOT NULL DEFAULT '1',
  `description` varchar(255) NOT NULL DEFAULT '',
  `comment` varchar(255) DEFAULT NULL,
  `prepaid` enum('Y','N') NOT NULL DEFAULT 'N',
  `draft_number` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `draft_number` (`draft_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tr_items_seq`
--

CREATE TABLE IF NOT EXISTS `tr_items_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tr_seq`
--

CREATE TABLE IF NOT EXISTS `tr_seq` (
  `id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(128) NOT NULL DEFAULT '',
  `password` varchar(128) NOT NULL DEFAULT '',
  `fullname` varchar(48) NOT NULL DEFAULT '',
  `email` varchar(64) DEFAULT NULL,
  `priv` enum('1','2','3','4') NOT NULL DEFAULT '1',
  `phone` varchar(14) DEFAULT '',
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users-roles`
--

CREATE TABLE IF NOT EXISTS `users-roles` (
  `username` varchar(128) NOT NULL DEFAULT '',
  `role` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`,`role`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users-sections`
--

CREATE TABLE IF NOT EXISTS `users-sections` (
  `username` varchar(128) NOT NULL DEFAULT '',
  `section_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`,`section_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vendor`
--

CREATE TABLE IF NOT EXISTS `vendor` (
  `id` int(11) NOT NULL DEFAULT '0',
  `account_number` varchar(30) DEFAULT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `address1` varchar(50) NOT NULL DEFAULT '',
  `address2` varchar(50) DEFAULT NULL,
  `city` varchar(30) DEFAULT '',
  `province` varchar(10) DEFAULT '',
  `country` varchar(30) DEFAULT NULL,
  `p_code` varchar(16) DEFAULT '',
  `attn` varchar(50) DEFAULT NULL,
  `main_phone` varchar(16) DEFAULT NULL,
  `main_fax` varchar(16) DEFAULT NULL,
  `main_email` varchar(50) DEFAULT NULL,
  `main_www` varchar(50) DEFAULT NULL,
  `tech_phone` varchar(16) DEFAULT NULL,
  `tech_fax` varchar(16) DEFAULT NULL,
  `tech_email` varchar(50) DEFAULT NULL,
  `tech_www` varchar(50) DEFAULT NULL,
  `comments` text,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vendor-old`
--

CREATE TABLE IF NOT EXISTS `vendor-old` (
  `id` int(11) NOT NULL DEFAULT '0',
  `account_number` varchar(30) DEFAULT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `address1` varchar(50) NOT NULL DEFAULT '',
  `address2` varchar(50) DEFAULT NULL,
  `city` varchar(30) NOT NULL DEFAULT '',
  `province` varchar(10) NOT NULL DEFAULT '',
  `country` varchar(30) DEFAULT NULL,
  `p_code` varchar(16) NOT NULL DEFAULT '',
  `attn` varchar(50) DEFAULT NULL,
  `main_phone` varchar(16) DEFAULT NULL,
  `main_fax` varchar(16) DEFAULT NULL,
  `main_email` varchar(50) DEFAULT NULL,
  `main_www` varchar(50) DEFAULT NULL,
  `tech_phone` varchar(16) DEFAULT NULL,
  `tech_fax` varchar(16) DEFAULT NULL,
  `tech_email` varchar(50) DEFAULT NULL,
  `tech_www` varchar(50) DEFAULT NULL,
  `comments` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_categories`
--

CREATE TABLE IF NOT EXISTS `vendor_categories` (
  `id` varchar(30) NOT NULL DEFAULT '',
  `name` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_seq`
--

CREATE TABLE IF NOT EXISTS `vendor_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
