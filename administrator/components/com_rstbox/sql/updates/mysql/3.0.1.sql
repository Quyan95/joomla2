CREATE TABLE IF NOT EXISTS `#__rstbox_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionid` varchar(64) CHARACTER SET utf8 NOT NULL,
  `visitorid` varchar(64) CHARACTER SET utf8 NOT NULL,
  `user` int(11) NOT NULL,
  `box` int(11) NOT NULL,
  `event` tinyint(6) NOT NULL DEFAULT '1',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `visitorid` (`visitorid`),
  KEY `box` (`box`),
  KEY `sessionid` (`sessionid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0