SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for modx_user_attributes
-- ----------------------------
DROP TABLE IF EXISTS `modx_user_attributes`;
CREATE TABLE `modx_user_attributes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `internalKey` int(10) NOT NULL DEFAULT '0',
  `fullname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `role` int(10) NOT NULL DEFAULT '0',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `phone` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mobilephone` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `blocked` int(1) NOT NULL DEFAULT '0',
  `blockeduntil` int(11) NOT NULL DEFAULT '0',
  `blockedafter` int(11) NOT NULL DEFAULT '0',
  `logincount` int(11) NOT NULL DEFAULT '0',
  `lastlogin` int(11) NOT NULL DEFAULT '0',
  `thislogin` int(11) NOT NULL DEFAULT '0',
  `failedlogincount` int(10) NOT NULL DEFAULT '0',
  `sessionid` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `dob` int(10) NOT NULL DEFAULT '0',
  `gender` int(1) NOT NULL DEFAULT '0' COMMENT '0 - unknown, 1 - Male 2 - female',
  `country` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `street` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `state` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `zip` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `fax` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'link to photo',
  `comment` text COLLATE utf8mb4_unicode_ci,
  `createdon` int(11) NOT NULL DEFAULT '0',
  `editedon` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`internalKey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contains information about the backend users.';
SET FOREIGN_KEY_CHECKS=1;
