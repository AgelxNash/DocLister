DROP TABLE IF EXISTS `{PREFIX}redirect_map`;
CREATE TABLE `{PREFIX}redirect_map` (
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

CREATE TABLE `{PREFIX}city` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `hide` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `hide` (`hide`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `{PREFIX}street` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `hide` tinyint(1) DEFAULT '0',
  `parent_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hide` (`hide`),
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `name_parent` (`name`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;