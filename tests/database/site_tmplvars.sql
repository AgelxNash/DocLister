SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for modx_site_tmplvars
-- ----------------------------
DROP TABLE IF EXISTS `modx_site_tmplvars`;
CREATE TABLE `modx_site_tmplvars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `caption` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `editor_type` int(11) NOT NULL DEFAULT '0' COMMENT '0-plain text,1-rich text,2-code editor',
  `category` int(11) NOT NULL DEFAULT '0' COMMENT 'category id',
  `locked` tinyint(4) NOT NULL DEFAULT '0',
  `elements` text COLLATE utf8mb4_unicode_ci,
  `rank` int(11) NOT NULL DEFAULT '0',
  `display` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Display Control',
  `display_params` text COLLATE utf8mb4_unicode_ci COMMENT 'Display Control Properties',
  `default_text` text COLLATE utf8mb4_unicode_ci,
  `createdon` int(11) NOT NULL DEFAULT '0',
  `editedon` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `indx_rank` (`rank`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Site Template Variables';

-- ----------------------------
-- Records of modx_site_tmplvars
-- ----------------------------
INSERT INTO `modx_site_tmplvars` VALUES ('1', 'text', 'testWithDefault', 'testWithDefault', '', '0', '0', '0', '', '0', '', '', 'MyDefaultValue', '1541650652', '1541650652');
INSERT INTO `modx_site_tmplvars` VALUES ('2', 'text', 'TestWithWidget', 'TestWithWidget', '', '0', '0', '0', '', '0', 'image', '&align=none', '', '1541650710', '1541650710');
INSERT INTO `modx_site_tmplvars` VALUES ('3', 'text', 'testA', 'testA', '', '0', '0', '0', '', '0', '', '', '', '1541650614', '1541650614');
SET FOREIGN_KEY_CHECKS=1;
