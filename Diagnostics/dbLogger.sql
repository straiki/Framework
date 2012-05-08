SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `debug_logs`;
CREATE TABLE `debug_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `time` int(11) DEFAULT NULL,
  `memory` float(6,2) DEFAULT NULL,
  `mode` enum('dev','prod') DEFAULT 'dev',
  `module` varchar(60) DEFAULT NULL,
  `presenter` varchar(60) NOT NULL,
  `view` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;