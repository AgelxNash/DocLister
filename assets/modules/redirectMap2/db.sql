DROP TABLE IF EXISTS `modx_redirect_map`;
CREATE TABLE `modx_redirect_map` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `page` int(10) DEFAULT NULL,
  `uri` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `save_get` tinyint(1) DEFAULT '0',
  `full_request` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`uri`),
  KEY `active` (`active`),
  KEY `page` (`page`) USING BTREE,
  KEY `full_request` (`full_request`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;