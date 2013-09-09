<?php

    die('IDE Helper for MODX Evolution ');

    if (!defined("MGR_DIR")) define("MGR_DIR", "manager");
    if (!defined('MODX_BASE_PATH')) define('MODX_BASE_PATH', 'base_path');
    if (!defined('MODX_BASE_URL')) define('MODX_BASE_URL', 'base_url');
    if (!defined('MODX_SITE_URL')) define('MODX_SITE_URL', 'site_url');
    if (!defined('MODX_MANAGER_PATH')) define('MODX_MANAGER_PATH', 'base_path/manager/');
    if (!defined('MODX_MANAGER_URL')) define('MODX_MANAGER_URL', 'site_url/manager/');

    $modx = new DocumentParser();

    class SystemEvent {
        var $name;
        var $_propagate;
        var $_output;
        var $activated;
        var $activePlugin;
        var $params;

        /**
         * @param string $name Name of the event
         */
        function SystemEvent($name= "") {}
        /**
         * Display a message to the user
         *
         * @param string $msg The message
         */
        function alert($msg) {}

        /**
         * Output
         *
         * @param string $msg
         */
        function output($msg) {}

        /**
         * Stop event propogation
         */
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
        var $event=null;
        /**
         * @var SystemEvent
         */
        var $Event=null;
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

        /**
         * Loads an extension from the extenders folder.
         * Currently of limited use - can only load the DBAPI and ManagerAPI.
         *
         * @param string $extname getAllChildren
         * @return boolean
         */
        function loadExtension($extname) {}
        function getMicroTime() {}

        /**
         * Redirect
         *
         * @param string $url
         * @param int $count_attempts
         * @param string $type
         * @param string $responseCode
         * @return boolean
         */
        function sendRedirect($url, $count_attempts= 0, $type= '', $responseCode= '') {}

        /**
         * Forward to another page
         *
         * @param int $id
         * @param string $responseCode
         */
        function sendForward($id, $responseCode= '') {}

        /**
         * Redirect to the error page, by calling sendForward(). This is called for example when the page was not found.
         */
        function sendErrorPage() {}

        /**
         * Redirect to the unauthorized page, for example on calling a page without having the permissions to see this page.
         */
        function sendUnauthorizedPage() {}

        /**
         * Connect to the database
         *
         * @deprecated use $modx->db->connect()
         */
        function dbConnect() {}

        /**
         * Query the database
         *
         * @deprecated use $modx->db->query()
         * @param string $sql The SQL statement to execute
         * @return resource|bool
         */
        function dbQuery($sql) {}

        /**
         * Count the number of rows in a record set
         *
         * @deprecated use $modx->db->getRecordCount($rs)
         * @param resource
         * @return int
         */
        function recordCount($rs) {}

        /**
         * Get a result row
         *
         * @deprecated use $modx->db->getRow()
         * @param array $rs
         * @param string $mode
         * @return array
         */
        function fetchRow($rs, $mode= 'assoc') {}

        /**
         * Get the number of rows affected in the last db operation
         *
         * @deprecated use $modx->db->getAffectedRows()
         * @param array $rs
         * @return int
         */
        function affectedRows($rs) {}

        /**
         * Get the ID generated in the last query
         *
         * @deprecated use $modx->db->getInsertId()
         * @param array $rs
         * @return int
         */
        function insertId($rs) {}

        /**
         * Close a database connection
         *
         * @deprecated use $modx->db->disconnect()
         */
        function dbClose() {}

        /**
         * Get MODx settings including, but not limited to, the system_settings table
         */
        function getSettings() {}

        /**
         * Get the method by which the current document/resource was requested
         *
         * @return string 'alias' (friendly url alias), 'rss' (friendly url alias with rss/ at the start of $_REQUEST['q']) or 'id' (may or may not be an RSS request).
         */
        function getDocumentMethod() {}

        /**
         * Returns the document identifier of the current request
         *
         * @param string $method id and alias are allowed
         * @return int
         */
        function getDocumentIdentifier($method) {}

        /**
         * Check for manager login session
         *
         * @return boolean
         */
        function checkSession() {}

        /**
         * Checks, if a the result is a preview
         *
         * @return boolean
         */
        function checkPreview() {}

        /**
         * check if site is offline
         *
         * @return boolean
         */
        function checkSiteStatus() {}

        /**
         * Create a 'clean' document identifier with path information, friendly URL suffix and prefix.
         *
         * @param string $qOrig
         * @return string
         */
        function cleanDocumentIdentifier($qOrig) {}

        /**
         * Check the cache for a specific document/resource
         *
         * @param int $id
         * @return string
         */
        function checkCache($id) {}

        /**
         * Final processing and output of the document/resource.
         *
         * - runs uncached snippets
         * - add javascript to <head>
         * - removes unused placeholders
         * - converts URL tags [~...~] to URLs
         *
         * @param boolean $noEvent Default: false
         */
        function outputContent($noEvent= false) {}

        /**
         * Checks the publish state of page
         */
        function checkPublishStatus() {}

        /**
         * Final jobs.
         *
         * - cache page
         */
        function postProcess() {}

        /**
         * Merge meta tags
         *
         * @param string $template
         * @return string
         * @deprecated
         */
        function mergeDocumentMETATags($template) {}

        /**
         * Merge content fields and TVs
         *
         * @param string $template
         * @return string
         */
        function mergeDocumentContent($template) {}

        /**
         * Merge system settings
         *
         * @param string $template
         * @return string
         */
        function mergeSettingsContent($template) {}

        /**
         * Merge chunks
         *
         * @param string $content
         * @return string
         */
        function mergeChunkContent($content) {}

        /**
         * Merge placeholder values
         *
         * @param string $content
         * @return string
         */
        function mergePlaceholderContent($content) {}

        /**
         * Run a plugin
         *
         * @param string $pluginCode Code to run
         * @param array $params
         */
        function evalPlugin($pluginCode, $params) {}

        /**
         * Run a snippet
         *
         * @param string $snippet Code to run
         * @param array $params
         * @return string
         */
        function evalSnippet($snippet, $params) {}

        /**
         * Run snippets as per the tags in $documentSource and replace the tags with the returned values.
         *
         * @param string $documentSource
         * @return string
         */
        function evalSnippets($documentSource) {}

        /**
         * Create a friendly URL
         *
         * @param string $pre
         * @param string $suff
         * @param string $alias
         * @param int $isfolder
         * @return string
         */
        function makeFriendlyURL($pre, $suff, $alias, $isfolder=0) {}

        /**
         * Convert URL tags [~...~] to URLs
         *
         * @param string $documentSource
         * @return string
         */
        function rewriteUrls($documentSource) { }

        /**
         * Get all db fields and TVs for a document/resource
         *
         * @param string $method
         * @param string $identifier
         * @return array
         */
        function getDocumentObject($method, $identifier) {}

        /**
         * Parse a source string.
         *
         * Handles most MODx tags. Exceptions include:
         *   - URL tags [~...~]
         *
         * @param string $source
         * @return string
         */
        function parseDocumentSource($source) {}

        /**
         * Starts the parsing operations.
         *
         * - connects to the db
         * - gets the settings (including system_settings)
         * - gets the document/resource identifier as in the query string
         * - finally calls prepareResponse()
         */
        function executeParser() {}

        /**
         * The next step called at the end of executeParser()
         *
         * - checks cache
         * - checks if document/resource is deleted/unpublished
         * - checks if resource is a weblink and redirects if so
         * - gets template and parses it
         * - ensures that postProcess is called when PHP is finished
         */
        function prepareResponse() {}

        /**
         * Returns an array of all parent record IDs for the id passed.
         *
         * @param int $id Docid to get parents for.
         * @param int $height The maximum number of levels to go up, default 10.
         * @return array
         */
        function getParentIds($id, $height= 10) {}

        /**
         * Returns an array of child IDs belonging to the specified parent.
         *
         * @param int $id The parent resource/document to start from
         * @param int $depth How many levels deep to search for children, default: 10
         * @param array $children Optional array of docids to merge with the result.
         * @return array Contains the document Listing (tree) like the sitemap
         */
        function getChildIds($id, $depth= 10, $children= array ()) {}

        /**
         * Displays a javascript alert message in the web browser
         *
         * @param string $msg Message to show
         * @param string $url URL to redirect to
         */
        function webAlert($msg, $url= "") {}

        /**
         * Returns true if user has the currect permission
         *
         * @param string $pm Permission name
         * @return int
         */
        function hasPermission($pm) {}

        /**
         * Add an a alert message to the system event log
         *
         * @param int $evtid Event ID
         * @param int $type Types: 1 = information, 2 = warning, 3 = error
         * @param string $msg Message to be logged
         * @param string $source source of the event (module, snippet name, etc.)
         *                       Default: Parser
         */
        function logEvent($evtid, $type, $msg, $source= 'Parser') {}

        /**
         * Returns true if we are currently in the manager/backend
         *
         * @return boolean
         */
        function isBackend() {}

        /**
         * Returns true if we are currently in the frontend
         *
         * @return boolean
         */
        function isFrontend() {}

        /**
         * Gets all child documents of the specified document, including those which are unpublished or deleted.
         *
         * @param int $id The Document identifier to start with
         * @param string $sort Sort field
         *                     Default: menuindex
         * @param string $dir Sort direction, ASC and DESC is possible
         *                    Default: ASC
         * @param string $fields Default: id, pagetitle, description, parent, alias, menutitle
         * @return array
         */
        function getAllChildren($id= 0, $sort= 'menuindex', $dir= 'ASC', $fields= 'id, pagetitle, description, parent, alias, menutitle') {}

        /**
         * Gets all active child documents of the specified document, i.e. those which published and not deleted.
         *
         * @param int $id The Document identifier to start with
         * @param string $sort Sort field
         *                     Default: menuindex
         * @param string $dir Sort direction, ASC and DESC is possible
         *                    Default: ASC
         * @param string $fields Default: id, pagetitle, description, parent, alias, menutitle
         * @return array
         */
        function getActiveChildren($id= 0, $sort= 'menuindex', $dir= 'ASC', $fields= 'id, pagetitle, description, parent, alias, menutitle') {}

        /**
         * Returns the children of the selected document/folder.
         *
         * @param int $parentid The parent document identifier
         *                      Default: 0 (site root)
         * @param int $published Whether published or unpublished documents are in the result
         *                      Default: 1
         * @param int $deleted Whether deleted or undeleted documents are in the result
         *                      Default: 0 (undeleted)
         * @param string $fields List of fields
         *                       Default: * (all fields)
         * @param string $where Where condition in SQL style. Should include a leading 'AND '
         *                      Default: Empty string
         * @param string $sort Should be a comma-separated list of field names on which to sort
         *                    Default: menuindex
         * @param string $dir Sort direction, ASC and DESC is possible
         *                    Default: ASC
         * @param string|int $limit Should be a valid SQL LIMIT clause without the 'LIMIT' i.e. just include the numbers as a string.
         *                          Default: Empty string (no limit)
         * @return array
         */
        function getDocumentChildren($parentid= 0, $published= 1, $deleted= 0, $fields= "*", $where= '', $sort= "menuindex", $dir= "ASC", $limit= "") {}

        /**
         * Returns multiple documents/resources
         *
         * @category API-Function
         * @param array $ids Documents to fetch by docid
         *                   Default: Empty array
         * @param int $published Whether published or unpublished documents are in the result
         *                      Default: 1
         * @param int $deleted Whether deleted or undeleted documents are in the result
         *                      Default: 0 (undeleted)
         * @param string $fields List of fields
         *                       Default: * (all fields)
         * @param string $where Where condition in SQL style. Should include a leading 'AND '.
         *                      Default: Empty string
         * @param string $sort Should be a comma-separated list of field names on which to sort
         *                    Default: menuindex
         * @param string $dir Sort direction, ASC and DESC is possible
         *                    Default: ASC
         * @param string|int $limit Should be a valid SQL LIMIT clause without the 'LIMIT' i.e. just include the numbers as a string.
         *                          Default: Empty string (no limit)
         * @return array|boolean Result array with documents, or false
         */
        function getDocuments($ids= array (), $published= 1, $deleted= 0, $fields= "*", $where= '', $sort= "menuindex", $dir= "ASC", $limit= "") {}

        /**
         * Returns one document/resource
         *
         * @category API-Function
         * @param int $id docid
         *                Default: 0 (no documents)
         * @param string $fields List of fields
         *                       Default: * (all fields)
         * @param int $published Whether published or unpublished documents are in the result
         *                      Default: 1
         * @param int $deleted Whether deleted or undeleted documents are in the result
         *                      Default: 0 (undeleted)
         * @return boolean|string
         */
        function getDocument($id= 0, $fields= "*", $published= 1, $deleted= 0) {}

        /**
         * Returns the page information as database row, the type of result is
         * defined with the parameter $rowMode
         *
         * @param int $pageid The parent document identifier
         *                    Default: -1 (no result)
         * @param int $active Should we fetch only published and undeleted documents/resources?
         *                     1 = yes, 0 = no
         *                     Default: 1
         * @param string $fields List of fields
         *                       Default: id, pagetitle, description, alias
         * @return boolean|array
         */
        function getPageInfo($pageid= -1, $active= 1, $fields= 'id, pagetitle, description, alias') {}

        /**
         * Returns the parent document/resource of the given docid
         *
         * @param int $pid The parent docid. If -1, then fetch the current document/resource's parent
         *                 Default: -1
         * @param int $active Should we fetch only published and undeleted documents/resources?
         *                     1 = yes, 0 = no
         *                     Default: 1
         * @param string $fields List of fields
         *                       Default: id, pagetitle, description, alias
         * @return boolean|array
         */
        function getParent($pid= -1, $active= 1, $fields= 'id, pagetitle, description, alias, parent') {}

        /**
         * Returns the id of the current snippet.
         *
         * @return int
         */
        function getSnippetId() {}

        /**
         * Returns the name of the current snippet.
         *
         * @return string
         */
        function getSnippetName() {}

        /**
         * Clear the cache of MODX.
         *
         * @return boolean
         */
        function clearCache() {}

        /**
         * Create an URL for the given document identifier. The url prefix and
         * postfix are used, when friendly_url is active.
         *
         * @param int $id The document identifier
         * @param string $alias The alias name for the document
         *                      Default: Empty string
         * @param string $args The paramaters to add to the URL
         *                     Default: Empty string
         * @param string $scheme With full as valus, the site url configuration is
         *                       used
         *                       Default: Empty string
         * @return string
         */
        function makeUrl($id, $alias= '', $args= '', $scheme= '') {}

        /**
         * Returns an entry from the config
         *
         * Note: most code accesses the config array directly and we will continue to support this.
         *
         * @return boolean|string
         */
        function getConfig($name= '') {}

        /**
         * Returns the MODX version information as version, branch, release date and full application name.
         *
         * @return array
         */
        function getVersionData($data=null) {}

        /**
         * Returns an ordered or unordered HTML list.
         *
         * @param array $array
         * @param string $ulroot Default: root
         * @param string $ulprefix Default: sub_
         * @param string $type Default: Empty string
         * @param boolean $ordered Default: false
         * @param int $tablevel Default: 0
         * @return string
         */
        function makeList($array, $ulroot= 'root', $ulprefix= 'sub_', $type= '', $ordered= false, $tablevel= 0) {}

        /**
         * Returns user login information, as loggedIn (true or false), internal key, username and usertype (web or manager).
         *
         * @return boolean|array
         */
        function userLoggedIn() {}

        /**
         * Returns an array with keywords for the current document, or a document with a given docid
         *
         * @param int $id The docid, 0 means the current document
         *                Default: 0
         * @return array
         * @deprecated
         */
        function getKeywords($id= 0) {}

        /**
         * Returns an array with meta tags for the current document, or a document with a given docid.
         *
         * @param int $id The document identifier, 0 means the current document
         *                Default: 0
         * @return array
         * @deprecated
         */
        function getMETATags($id= 0) {}

        /**
         * Executes a snippet.
         *
         * @param string $snippetName
         * @param array $params Default: Empty array
         * @return string
         */
        function runSnippet($snippetName, $params= array ()) {}

        /**
         * Returns the chunk content for the given chunk name
         *
         * @param string $chunkName
         * @return boolean|string
         */
        function getChunk($chunkName) {}

        /**
         * Old method that just calls getChunk()
         *
         * @deprecated Use getChunk
         * @param string $chunkName
         * @return boolean|string
         */
        function putChunk($chunkName) {}

        /**
         * Parse a chunk for placeholders
         *
         * @param string $chunkname Name of chunk to get from db
         * @param string $chunkArr Array of placeholder names (array keys) and replacements (array values)
         * @param string $prefix Placeholder prefix. Defaults to [+
         * @param string $suffix Placeholder suffix. Defaults to +]
         * @return string
         */
        function parseChunk($chunkName, $chunkArr, $prefix= "{", $suffix= "}") {}

        /**
         * Get data from phpSniff
         *
         * @category API-Function
         * @return array
         */
        function getUserData() {}

        /**
         * Returns the timestamp in the date format defined in $this->config['date_format']
         *
         * @param int $timestamp Default: 0
         * @param string $mode Default: Empty string (adds the time as below). Can also be 'dateOnly' for no time or 'formatOnly' to get the date_format string.
         * @return string
         */
        function toDateFormat($timestamp = 0, $mode = '') {}

        /**
         * Make a timestamp from a string corresponding to the format in $this->config['date_format']
         *
         * @param string $str
         * @return string
         */
        function toTimeStamp($str) {}

        /**
         * Get the TVs of a document's children. Returns an array where each element represents one child doc.
         *
         * Ignores deleted children. Gets all children - there is no where clause available.
         *
         * @param int $parentid The parent docid
         *                 Default: 0 (site root)
         * @param array $tvidnames. Which TVs to fetch - Can relate to the TV ids in the db (array elements should be numeric only)
         *                                               or the TV names (array elements should be names only)
         *                      Default: Empty array
         * @param int $published Whether published or unpublished documents are in the result
         *                      Default: 1
         * @param string $docsort How to sort the result array (field)
         *                      Default: menuindex
         * @param ASC $docsortdir How to sort the result array (direction)
         *                      Default: ASC
         * @param string $tvfields Fields to fetch from site_tmplvars, default '*'
         *                      Default: *
         * @param string $tvsort How to sort each element of the result array i.e. how to sort the TVs (field)
         *                      Default: rank
         * @param string  $tvsortdir How to sort each element of the result array i.e. how to sort the TVs (direction)
         *                      Default: ASC
         * @return boolean|array
         */
        function getDocumentChildrenTVars($parentid= 0, $tvidnames= array (), $published= 1, $docsort= "menuindex", $docsortdir= "ASC", $tvfields= "*", $tvsort= "rank", $tvsortdir= "ASC") {}

        /**
         * Get the TV outputs of a document's children.
         *
         * Returns an array where each element represents one child doc and contains the result from getTemplateVarOutput()
         *
         * Ignores deleted children. Gets all children - there is no where clause available.
         *
         * @param int $parentid The parent docid
         *                        Default: 0 (site root)
         * @param array $tvidnames. Which TVs to fetch. In the form expected by getTemplateVarOutput().
         *                        Default: Empty array
         * @param int $published Whether published or unpublished documents are in the result
         *                        Default: 1
         * @param string $docsort How to sort the result array (field)
         *                        Default: menuindex
         * @param ASC $docsortdir How to sort the result array (direction)
         *                        Default: ASC
         * @return boolean|array
         */
        function getDocumentChildrenTVarOutput($parentid= 0, $tvidnames= array (), $published= 1, $docsort= "menuindex", $docsortdir= "ASC") {}

        /**
         * Modified by Raymond for TV - Orig Modified by Apodigm - DocVars
         * Returns a single site_content field or TV record from the db.
         *
         * If a site content field the result is an associative array of 'name' and 'value'.
         *
         * If a TV the result is an array representing a db row including the fields specified in $fields.
         *
         * @param string $idname Can be a TV id or name
         * @param string $fields Fields to fetch from site_tmplvars. Default: *
         * @param type $docid Docid. Defaults to empty string which indicates the current document.
         * @param int $published Whether published or unpublished documents are in the result
         *                        Default: 1
         * @return boolean
         */
        function getTemplateVar($idname= "", $fields= "*", $docid= "", $published= 1) {}

        /**
         * Returns an array of site_content field fields and/or TV records from the db
         *
         * Elements representing a site content field consist of an associative array of 'name' and 'value'.
         *
         * Elements representing a TV consist of an array representing a db row including the fields specified in $fields.
         *
         * @param array $idnames Which TVs to fetch - Can relate to the TV ids in the db (array elements should be numeric only)
         *                                               or the TV names (array elements should be names only)
         *                        Default: Empty array
         * @param string $fields Fields to fetch from site_tmplvars.
         *                        Default: *
         * @param string $docid Docid. Defaults to empty string which indicates the current document.
         * @param int $published Whether published or unpublished documents are in the result
         *                        Default: 1
         * @param string $sort How to sort the result array (field)
         *                        Default: rank
         * @param string $dir How to sort the result array (direction)
         *                        Default: ASC
         * @return boolean|array
         */
        function getTemplateVars($idnames= array (), $fields= "*", $docid= "", $published= 1, $sort= "rank", $dir= "ASC") {}

        /**
         * Returns an associative array containing TV rendered output values.
         *
         * @param type $idnames Which TVs to fetch - Can relate to the TV ids in the db (array elements should be numeric only)
         *                                               or the TV names (array elements should be names only)
         *                        Default: Empty array
         * @param string $docid Docid. Defaults to empty string which indicates the current document.
         * @param int $published Whether published or unpublished documents are in the result
         *                        Default: 1
         * @param string $sep
         * @return boolean|array
         */
        function getTemplateVarOutput($idnames= array (), $docid= "", $published= 1, $sep='') {}

        function getFullTableName($tbl) {}

        /**
         * Returns the placeholder value
         *
         * @param string $name Placeholder name
         * @return string Placeholder value
         */
        function getPlaceholder($name) {}

        /**
         * Sets a value for a placeholder
         *
         * @param string $name The name of the placeholder
         * @param string $value The value of the placeholder
         */
        function setPlaceholder($name, $value) {}

        /**
         * Set placeholders en masse via an array or object.
         *
         * @param object|array $subject
         * @param string $prefix
         */
        function toPlaceholders($subject, $prefix= '') {}

        /**
         * For use by toPlaceholders(); For setting an array or object element as placeholder.
         *
         * @param string $key
         * @param object|array $value
         * @param string $prefix
         */
        function toPlaceholder($key, $value, $prefix= '') {}

        /**
         * Returns the manager relative URL/path with respect to the site root.
         *
         * @return string The complete URL to the manager folder
         */
        function getManagerPath() {}

        /**
         * Returns the cache relative URL/path with respect to the site root.
         *
         * @return string The complete URL to the cache folder
         */
        function getCachePath() {}

        /**
         * Sends a message to a user's message box.
         *
         * @param string $type Type of the message
         * @param string $to The recipient of the message
         * @param string $from The sender of the message
         * @param string $subject The subject of the message
         * @param string $msg The message body
         * @param int $private Whether it is a private message, or not
         *                     Default : 0
         */
        function sendAlert($type, $to, $from, $subject, $msg, $private= 0) {}

        /**
         * Returns true, install or interact when inside manager.
         *
         * @deprecated
         * @return string
         */
        function insideManager() {}

        /**
         * Returns current user id.
         *
         * @param string $context. Default is an empty string which indicates the method should automatically pick 'web (frontend) or 'mgr' (backend)
         * @return string
         */
        function getLoginUserID($context= '') {}

        /**
         * Returns current user name
         *
         * @param string $context. Default is an empty string which indicates the method should automatically pick 'web (frontend) or 'mgr' (backend)
         * @return string
         */
        function getLoginUserName($context= '') {}

        /**
         * Returns current login user type - web or manager
         *
         * @return string
         */
        function getLoginUserType() {}

        /**
         * Returns a user info record for the given manager user
         *
         * @param int $uid
         * @return boolean|string
         */
        function getUserInfo($uid) {}

        /**
         * Returns a record for the web user
         *
         * @param int $uid
         * @return boolean|string
         */
        function getWebUserInfo($uid) {}

        /**
         * Returns an array of document groups that current user is assigned to.
         * This function will first return the web user doc groups when running from
         * frontend otherwise it will return manager user's docgroup.
         *
         * @param boolean $resolveIds Set to true to return the document group names
         *                            Default: false
         * @return string|array
         */
        function getUserDocGroups($resolveIds= false) {}

        /**
         * Returns an array of document groups that current user is assigned to.
         * This function will first return the web user doc groups when running from
         * frontend otherwise it will return manager user's docgroup.
         *
         * @deprecated
         * @return string|array
         */
        function getDocGroups() {}

        /**
         * Change current web user's password
         *
         * @todo Make password length configurable, allow rules for passwords and translation of messages
         * @param string $oldPwd
         * @param string $newPwd
         * @return string|boolean Returns true if successful, oterhwise return error
         *                        message
         */
        function changeWebUserPassword($oldPwd, $newPwd) {}

        /**
         * Change current web user's password
         *
         * @deprecated
         * @param string $o
         * @param string $n
         * @return string|boolean
         */
        function changePassword($o, $n) {}

        /**
         * Returns true if the current web user is a member the specified groups
         *
         * @param array $groupNames
         * @return boolean
         */
        function isMemberOfWebGroup($groupNames= array ()) {}

        /**
         * Registers Client-side CSS scripts - these scripts are loaded at inside
         * the <head> tag
         *
         * @param string $src
         * @param string $media Default: Empty string
         */
        function regClientCSS($src, $media='') {}

        /**
         * Registers Startup Client-side JavaScript - these scripts are loaded at inside the <head> tag
         *
         * @param string $src
         * @param array $options Default: 'name'=>'', 'version'=>'0', 'plaintext'=>false
         */
        function regClientStartupScript($src, $options= array('name'=>'', 'version'=>'0', 'plaintext'=>false)) {}

        /**
         * Registers Client-side JavaScript these scripts are loaded at the end of the page unless $startup is true
         *
         * @param string $src
         * @param array $options Default: 'name'=>'', 'version'=>'0', 'plaintext'=>false
         * @param boolean $startup Default: false
         * @return string
         */
        function regClientScript($src, $options= array('name'=>'', 'version'=>'0', 'plaintext'=>false), $startup= false) {}

        /**
         * Registers Client-side Startup HTML block
         *
         * @param string $html
         */
        function regClientStartupHTMLBlock($html) {}

        /**
         * Registers Client-side HTML block
         *
         * @param string $html
         */
        function regClientHTMLBlock($html) {}

        /**
         * Remove unwanted html tags and snippet, settings and tags
         *
         * @param string $html
         * @param string $allowed Default: Empty string
         * @return string
         */
        function stripTags($html, $allowed= "") {}
        function jsonDecode($json, $assoc = false) {}

        /**
         * Add an event listner to a plugin - only for use within the current execution cycle
         *
         * @param string $evtName
         * @param string $pluginName
         * @return boolean|int
         */
        function addEventListener($evtName, $pluginName) {}

        /**
         * Remove event listner - only for use within the current execution cycle
         *
         * @param string $evtName
         * @return boolean
         */
        function removeEventListener($evtName) {}

        /**
         * Remove all event listners - only for use within the current execution cycle
         */
        function removeAllEventListener() {}

        /**
         * Invoke an event.
         *
         * @param string $evtName
         * @param array $extParams Parameters available to plugins. Each array key will be the PHP variable name, and the array value will be the variable value.
         * @return boolean|array
         */
        function invokeEvent($evtName, $extParams= array ()) {}

        /**
         * Parses a resource property string and returns the result as an array
         *
         * @param string $propertyString
         * @return array Associative array in the form property name => property value
         */
        function parseProperties($propertyString) {}
        function getIntTableRows($fields= "*", $from= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {}
        function putIntTableRow($fields= "", $into= "") {}
        function updIntTableRow($fields= "", $into= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {}
        function getExtTableRows($host= "", $user= "", $pass= "", $dbase= "", $fields= "*", $from= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {}
        function putExtTableRow($host= "", $user= "", $pass= "", $dbase= "", $fields= "", $into= "") {}
        function updExtTableRow($host= "", $user= "", $pass= "", $dbase= "", $fields= "", $into= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {}
        function dbExtConnect($host, $user, $pass, $dbase) {}
        function getFormVars($method= "", $prefix= "", $trim= "", $REQUEST_METHOD) {}

        /**
         * PHP error handler set by http://www.php.net/manual/en/function.set-error-handler.php
         *
         * Checks the PHP error and calls messageQuit() unless:
         *	- error_reporting() returns 0, or
         *  - the PHP error level is 0, or
         *  - the PHP error level is 8 (E_NOTICE) and stopOnNotice is false
         *
         * @param int $nr The PHP error level as per http://www.php.net/manual/en/errorfunc.constants.php
         * @param string $text Error message
         * @param string $file File where the error was detected
         * @param string $line Line number within $file
         * @return boolean
         */
        function phpError($nr, $text, $file, $line) {}

        /**
         * Error logging and output.
         *
         * If error_handling_silent is 0, outputs an error page with detailed informations about the error.
         * Always logs the error using logEvent()
         *
         * @param string $msg Default: unspecified error
         * @param string $query Default: Empty string
         * @param boolean $is_error Default: true
         * @param string $nr Default: Empty string
         * @param string $file Default: Empty string
         * @param string $source Default: Empty string
         * @param string $text Default: Empty string
         * @param string $line Default: Empty string
         */
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
