CREATE TABLE `codebook` (
	`id` tinyint(4) NOT NULL AUTO_INCREMENT,
	`type` varchar(30) COLLATE utf8_czech_ci NOT NULL,
	`value` varchar(60) COLLATE utf8_czech_ci NOT NULL,
	`display` int(1) DEFAULT NULL,
	`rank` int(3) DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;