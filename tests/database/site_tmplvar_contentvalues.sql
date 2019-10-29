SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for modx_site_tmplvar_contentvalues
-- ----------------------------
DROP TABLE IF EXISTS `modx_site_tmplvar_contentvalues`;
CREATE TABLE `modx_site_tmplvar_contentvalues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tmplvarid` int(10) NOT NULL DEFAULT '0' COMMENT 'Template Variable id',
  `contentid` int(10) NOT NULL DEFAULT '0' COMMENT 'Site Content Id',
  `value` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_tvid_contentid` (`tmplvarid`,`contentid`),
  KEY `idx_tmplvarid` (`tmplvarid`),
  KEY `idx_id` (`contentid`),
  FULLTEXT KEY `value_ft_idx` (`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Site Template Variables Content Values Link Table';

-- ----------------------------
-- Records of modx_site_tmplvar_contentvalues
-- ----------------------------
INSERT INTO `modx_site_tmplvar_contentvalues` VALUES ('1', '1', '1', 'zxc');
SET FOREIGN_KEY_CHECKS=1;
