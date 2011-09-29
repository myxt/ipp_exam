-- phpMyAdmin SQL Dump
-- version 3.4.5deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 29, 2011 at 01:56 PM
-- Server version: 5.1.58
-- PHP Version: 5.2.6-1+lenny13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ez450`
--

-- --------------------------------------------------------

--
-- Table structure for table `exam_question`
--

DROP TABLE IF EXISTS `exam_question`;
CREATE TABLE IF NOT EXISTS `exam_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `type` enum('question','answer') NOT NULL,
  `options` longtext,
  `correct` tinyint(1) DEFAULT NULL,
  `content` longtext,
  `version` int(11) DEFAULT NULL,
  `language_code` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

DROP TABLE IF EXISTS `exam_results`;
CREATE TABLE IF NOT EXISTS `exam_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contentobject_id` int(32) NOT NULL,
  `hash` varchar(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer` varchar(256) NOT NULL,
  `correct` tinyint(1) NOT NULL,
  `followup` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `exam_statistics`
--

DROP TABLE IF EXISTS `exam_statistics`;
CREATE TABLE IF NOT EXISTS `exam_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contentobject_id` int(11) DEFAULT NULL,
  `count` int(11) NOT NULL,
  `pass_first` int(11) DEFAULT NULL,
  `pass_second` int(11) DEFAULT NULL,
  `xml_options` longtext NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `high_score` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `exam_structure`
--

DROP TABLE IF EXISTS `exam_structure`;
CREATE TABLE IF NOT EXISTS `exam_structure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contentobject_id` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `type` varchar(32) NOT NULL,
  `parent` int(11) NOT NULL,
  `options` longtext NOT NULL,
  `content` longtext NOT NULL,
  `version` int(11) NOT NULL,
  `language_code` varchar(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
