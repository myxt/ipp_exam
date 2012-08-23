SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DROP TABLE IF EXISTS `exam_answer`;
CREATE TABLE IF NOT EXISTS `exam_answer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contentobject_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `option_id` int(3) NOT NULL,
  `option_value` int(11) NOT NULL,
  `correct` tinyint(1) DEFAULT NULL,
  `content` longtext,
  `version` int(11) DEFAULT NULL,
  `language_code` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `exam_results`;
CREATE TABLE IF NOT EXISTS `exam_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contentobject_id` int(11) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `question_id` int(11) NOT NULL,
  `answer` int(11) NOT NULL,
  `correct` tinyint(1) NOT NULL,
  `followup` tinyint(1) NOT NULL,
  `conditional` int(11) DEFAULT NULL COMMENT 'This is the id of a text element to display after this answer based on a condition',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `exam_statistics`;
CREATE TABLE IF NOT EXISTS `exam_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contentobject_id` int(11) DEFAULT NULL,
  `count` int(11) NOT NULL,
  `pass_first` int(11) DEFAULT NULL,
  `pass_second` int(11) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `high_score` int(3) NOT NULL,
  `score_tally` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `exam_structure`;
CREATE TABLE IF NOT EXISTS `exam_structure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contentobject_id` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `type` varchar(32) NOT NULL,
  `parent` int(11) NOT NULL,
  `xmloptions` longtext NOT NULL,
  `content` longtext NOT NULL,
  `version` int(11) NOT NULL,
  `language_code` varchar(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
