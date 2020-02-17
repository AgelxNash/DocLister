SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for modx_site_content
-- ----------------------------
DROP TABLE IF EXISTS `modx_site_content`;
CREATE TABLE `modx_site_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'document',
  `contentType` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text/html',
  `pagetitle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `longtitle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `alias` varchar(245) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `link_attributes` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Link attriubtes',
  `published` int(1) NOT NULL DEFAULT '0',
  `pub_date` int(20) NOT NULL DEFAULT '0',
  `unpub_date` int(20) NOT NULL DEFAULT '0',
  `parent` int(10) NOT NULL DEFAULT '0',
  `isfolder` int(1) NOT NULL DEFAULT '0',
  `introtext` text COLLATE utf8mb4_unicode_ci COMMENT 'Used to provide quick summary of the document',
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `richtext` tinyint(1) NOT NULL DEFAULT '1',
  `template` int(10) NOT NULL DEFAULT '0',
  `menuindex` int(10) NOT NULL DEFAULT '0',
  `searchable` int(1) NOT NULL DEFAULT '1',
  `cacheable` int(1) NOT NULL DEFAULT '1',
  `createdby` int(10) NOT NULL DEFAULT '0',
  `createdon` int(20) NOT NULL DEFAULT '0',
  `editedby` int(10) NOT NULL DEFAULT '0',
  `editedon` int(20) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  `deletedon` int(20) NOT NULL DEFAULT '0',
  `deletedby` int(10) NOT NULL DEFAULT '0',
  `publishedon` int(20) NOT NULL DEFAULT '0' COMMENT 'Date the document was published',
  `publishedby` int(10) NOT NULL DEFAULT '0' COMMENT 'ID of user who published the document',
  `menutitle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Menu title',
  `donthit` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Disable page hit count',
  `privateweb` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Private web document',
  `privatemgr` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Private manager document',
  `content_dispo` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-inline, 1-attachment',
  `hidemenu` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hide document from menu',
  `alias_visible` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `parent` (`parent`),
  KEY `aliasidx` (`alias`),
  KEY `typeidx` (`type`),
  FULLTEXT KEY `content_ft_idx` (`pagetitle`,`description`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contains the site document tree.';
SET FOREIGN_KEY_CHECKS=1;
