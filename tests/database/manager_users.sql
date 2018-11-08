SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for modx_manager_users
-- ----------------------------
DROP TABLE IF EXISTS `modx_manager_users`;
CREATE TABLE `modx_manager_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contains login information for backend users.';
SET FOREIGN_KEY_CHECKS=1;
