<?php

    die('IDE Helper for MODX Evolution ');

    $modx = new DocumentParser();

    class SystemEvent {
        function SystemEvent($name= "") {}
        function alert($msg) {}
        function output($msg) {}
        function stopPropagation() {}
        function _resetEventObject() {}
    }



    class DBAPI {
        var $conn;
        var $config;
        var $isConnected;

        function DBAPI($host='',$dbase='', $uid='',$pwd='',$pre=NULL,$charset='',$connection_method='SET CHARACTER SET') {}
        function initDataTypes() {}
        function connect($host = '', $dbase = '', $uid = '', $pwd = '', $persist = 0) {}
        function disconnect() {}
        function escape($s, $safecount=0) {}
        function query($sql) {}
        function delete($from, $where='', $orderby='', $limit = '') {}
        function select($fields = "*", $from = "", $where = "", $orderby = "", $limit = "") {}
        function update($fields, $table, $where = "") {}
        function insert($fields, $intotable, $fromfields = "*", $fromtable = "", $where = "", $limit = "") {}
        function getInsertId($conn=NULL) {}
        function getAffectedRows($conn=NULL) {}
        function getLastError($conn=NULL) {}
        function getRecordCount($ds) {}
        function getRow($ds, $mode = 'assoc') {}
        function getColumn($name, $dsq) {}
        function getColumnNames($dsq) {}
        function getValue($dsq) {}
        function getXML($dsq) {}
        function getTableMetaData($table) {}
        function prepareDate($timestamp, $fieldType = 'DATETIME') {}
        function getHTMLGrid($dsq, $params) {}
        function makeArray($rs=''){}
        function getVersion() {}
        function replaceFullTableName($str,$force=null) {}
    }


    class DocumentParser {
        /**
         * @var DBAPI
         */
        var $db = null;
        /**
         * @var SystemEvent
         */
        var $event, $Event;
        var $pluginEvent;
        var $config= null;
        var $rs;
        var $result;
        var $sql;
        var $table_prefix;
        var $debug;
        var $documentIdentifier;
        var $documentMethod;
        var $documentGenerated;
        var $documentContent;
        var $tstart;
        var $mstart;
        var $minParserPasses;
        var $maxParserPasses;
        var $documentObject;
        var $templateObject;
        var $snippetObjects;
        var $stopOnNotice;
        var $executedQueries;
        var $queryTime;
        var $currentSnippet;
        var $documentName;
        var $aliases;
        var $visitor;
        var $entrypage;
        var $documentListing;
        var $dumpSnippets;
        var $chunkCache;
        var $snippetCache;
        var $contentTypes;
        var $dumpSQL;
        var $queryCode;
        var $virtualDir;
        var $placeholders;
        var $sjscripts;
        var $jscripts;
        var $loadedjscripts;
        var $documentMap;
        var $forwards= 3;
        var $error_reporting;

        function DocumentParser() {}
        function loadExtension($extname) {}
        function getMicroTime() {}
        function sendRedirect($url, $count_attempts= 0, $type= '', $responseCode= '') {}
        function sendForward($id, $responseCode= '') {}
        function sendErrorPage() {}
        function sendUnauthorizedPage() {}
        function dbConnect() {}
        function dbQuery($sql) {}
        function recordCount($rs) {}
        function fetchRow($rs, $mode= 'assoc') {}
        function affectedRows($rs) {}
        function insertId($rs) {}
        function dbClose() {}
        function getSettings() {}
        function getDocumentMethod() {}
        function getDocumentIdentifier($method) {}
        function checkSession() {}
        function checkPreview() {}
        function checkSiteStatus() {}
        function cleanDocumentIdentifier($qOrig) {}
        function checkCache($id) {}
        function outputContent($noEvent= false) {}
        function checkPublishStatus() {}
        function postProcess() {}
        function mergeDocumentMETATags($template) {}
        function mergeDocumentContent($template) {}
        function mergeSettingsContent($template) {}
        function mergeChunkContent($content) {}
        function mergePlaceholderContent($content) {}
        function evalPlugin($pluginCode, $params) {}
        function evalSnippet($snippet, $params) {}
        function evalSnippets($documentSource) {}
        function makeFriendlyURL($pre, $suff, $alias, $isfolder=0) {}
        function rewriteUrls($documentSource) { }
        function getDocumentObject($method, $identifier) {}
        function parseDocumentSource($source) {}
        function executeParser() {}
        function prepareResponse() {}
        function getParentIds($id, $height= 10) {}
        function getChildIds($id, $depth= 10, $children= array ()) {}
        function webAlert($msg, $url= "") {}
        function hasPermission($pm) {}

        /**
         * @param int $evtid ID события
         * @param int $type Тип события: 1 = information, 2 = warning, 3 = error
         * @param string $msg Сообщение
         * @param string $source Источник
         */
        function logEvent($evtid, $type, $msg, $source= 'Parser') {}
        function isBackend() {}
        function isFrontend() {}
        function getAllChildren($id= 0, $sort= 'menuindex', $dir= 'ASC', $fields= 'id, pagetitle, description, parent, alias, menutitle') {}
        function getActiveChildren($id= 0, $sort= 'menuindex', $dir= 'ASC', $fields= 'id, pagetitle, description, parent, alias, menutitle') {}
        function getDocumentChildren($parentid= 0, $published= 1, $deleted= 0, $fields= "*", $where= '', $sort= "menuindex", $dir= "ASC", $limit= "") {}
        function getDocuments($ids= array (), $published= 1, $deleted= 0, $fields= "*", $where= '', $sort= "menuindex", $dir= "ASC", $limit= "") {}
        function getDocument($id= 0, $fields= "*", $published= 1, $deleted= 0) {}
        function getPageInfo($pageid= -1, $active= 1, $fields= 'id, pagetitle, description, alias') {}
        function getParent($pid= -1, $active= 1, $fields= 'id, pagetitle, description, alias, parent') {}
        function getSnippetId() {}
        function getSnippetName() {}
        function clearCache() {}
        function makeUrl($id, $alias= '', $args= '', $scheme= '') {}
        function getConfig($name= '') {}
        function getVersionData($data=null) {}
        function makeList($array, $ulroot= 'root', $ulprefix= 'sub_', $type= '', $ordered= false, $tablevel= 0) {}
        function userLoggedIn() {}
        function getKeywords($id= 0) {}
        function getMETATags($id= 0) {}
        function runSnippet($snippetName, $params= array ()) {}
        function getChunk($chunkName) {}
        function putChunk($chunkName) {}
        function parseChunk($chunkName, $chunkArr, $prefix= "{", $suffix= "}") {}
        function getUserData() {}
        function toDateFormat($timestamp = 0, $mode = '') {}
        function toTimeStamp($str) {}
        function getDocumentChildrenTVars($parentid= 0, $tvidnames= array (), $published= 1, $docsort= "menuindex", $docsortdir= "ASC", $tvfields= "*", $tvsort= "rank", $tvsortdir= "ASC") {}
        function getDocumentChildrenTVarOutput($parentid= 0, $tvidnames= array (), $published= 1, $docsort= "menuindex", $docsortdir= "ASC") {}
        function getTemplateVar($idname= "", $fields= "*", $docid= "", $published= 1) {}
        function getTemplateVars($idnames= array (), $fields= "*", $docid= "", $published= 1, $sort= "rank", $dir= "ASC") {}
        function getTemplateVarOutput($idnames= array (), $docid= "", $published= 1, $sep='') {}
        function getFullTableName($tbl) {}
        function getPlaceholder($name) {}
        function setPlaceholder($name, $value) {}
        function toPlaceholders($subject, $prefix= '') {}
        function toPlaceholder($key, $value, $prefix= '') {}
        function getManagerPath() {}
        function getCachePath() {}
        function sendAlert($type, $to, $from, $subject, $msg, $private= 0) {}
        function insideManager() {}
        function getLoginUserID($context= '') {}
        function getLoginUserName($context= '') {}
        function getLoginUserType() {}
        function getUserInfo($uid) {}
        function getWebUserInfo($uid) {}
        function getUserDocGroups($resolveIds= false) {}
        function getDocGroups() {}
        function changeWebUserPassword($oldPwd, $newPwd) {}
        function changePassword($o, $n) {}
        function isMemberOfWebGroup($groupNames= array ()) {}
        function regClientCSS($src, $media='') {}
        function regClientStartupScript($src, $options= array('name'=>'', 'version'=>'0', 'plaintext'=>false)) {}
        function regClientScript($src, $options= array('name'=>'', 'version'=>'0', 'plaintext'=>false), $startup= false) {}
        function regClientStartupHTMLBlock($html) {}
        function regClientHTMLBlock($html) {}
        function stripTags($html, $allowed= "") {}
        function jsonDecode($json, $assoc = false) {}
        function addEventListener($evtName, $pluginName) {}
        function removeEventListener($evtName) {}
        function removeAllEventListener() {}
        function invokeEvent($evtName, $extParams= array ()) {}
        function parseProperties($propertyString) {}
        function getIntTableRows($fields= "*", $from= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {}
        function putIntTableRow($fields= "", $into= "") {}
        function updIntTableRow($fields= "", $into= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {}
        function getExtTableRows($host= "", $user= "", $pass= "", $dbase= "", $fields= "*", $from= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {}
        function putExtTableRow($host= "", $user= "", $pass= "", $dbase= "", $fields= "", $into= "") {}
        function updExtTableRow($host= "", $user= "", $pass= "", $dbase= "", $fields= "", $into= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {}
        function dbExtConnect($host, $user, $pass, $dbase) {}
        function getFormVars($method= "", $prefix= "", $trim= "", $REQUEST_METHOD) {}
        function phpError($nr, $text, $file, $line) {}
        function messageQuit($msg= 'unspecified error', $query= '', $is_error= true, $nr= '', $file= '', $source= '', $text= '', $line= '', $output='') {}
        function get_backtrace($backtrace) {}
        function getRegisteredClientScripts() {}
        function getRegisteredClientStartupScripts() {}

        /**
         * Format alias to be URL-safe. Strip invalid characters.
         *
         * @param string Alias to be formatted
         * @return string Safe alias
         */
        function stripAlias($alias) {}

        function nicesize($size) {}
    }
