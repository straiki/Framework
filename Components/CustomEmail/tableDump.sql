SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `custom_email`;
CREATE TABLE `custom_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codename` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `from_email` varchar(120) COLLATE utf8_czech_ci NOT NULL,
  `from_name` varchar(60) COLLATE utf8_czech_ci DEFAULT NULL,
  `subject` varchar(60) COLLATE utf8_czech_ci NOT NULL DEFAULT 'Complete subject',
  `subject_en` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `body` text COLLATE utf8_czech_ci NOT NULL,
  `body_en` text COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `custom_email_log`;
CREATE TABLE `custom_email_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `codename` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `to_email` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `to_name` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `subject` varchar(120) COLLATE utf8_czech_ci NOT NULL,
  `body` text COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;