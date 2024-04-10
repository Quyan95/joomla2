CREATE TABLE IF NOT EXISTS `#__rstbox` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `testmode` tinyint(1) NOT NULL DEFAULT '0',
  `boxtype` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customhtml` text COLLATE utf8mb4_unicode_ci,
  `position` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `triggermethod` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cookie` mediumint(9) NOT NULL,
  `params` text COLLATE utf8mb4_unicode_ci,
  `published` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__rstbox_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `visitorid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user` int(11) NOT NULL,
  `box` int(11) NOT NULL,
  `event` tinyint(6) NOT NULL DEFAULT '1',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `visitorid` (`visitorid`),
  KEY `box` (`box`),
  KEY `sessionid` (`sessionid`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;