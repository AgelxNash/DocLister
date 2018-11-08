
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for modx_site_tmplvar_templates
-- ----------------------------
DROP TABLE IF EXISTS `modx_site_tmplvar_templates`;
CREATE TABLE `modx_site_tmplvar_templates` (
  `tmplvarid` int(10) NOT NULL DEFAULT '0' COMMENT 'Template Variable id',
  `templateid` int(11) NOT NULL DEFAULT '0',
  `rank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tmplvarid`,`templateid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Site Template Variables Templates Link Table';

-- ----------------------------
-- Records of modx_site_tmplvar_templates
-- ----------------------------
INSERT INTO `modx_site_tmplvar_templates` VALUES ('1', '1', '0');
INSERT INTO `modx_site_tmplvar_templates` VALUES ('2', '1', '0');
INSERT INTO `modx_site_tmplvar_templates` VALUES ('3', '1', '0');
SET FOREIGN_KEY_CHECKS=1;
