<?php

use AgelxNash\Modx\Evo\Database\Database;

class DocumentParser
{
    const CMS_VERSION = '1.4.6';

    /** @var Database */
    public $db;

    public $documentIdentifier;
    public $config = array();
    public $aliasListing = array();

    public $test_userId = 0;

    public function __construct()
    {
        $this->test_define();
        $this->db = $this->test_loadDbApi();
    }

    protected function test_define()
    {

    }

    protected function test_loadDbApi()
    {
        $database = new Database(
            array(
                'host' => isset($_SERVER['DB_HOST']) ? $_SERVER['DB_HOST'] : 'localhost',
                'database' => isset($_SERVER['DB_BASE']) ? $_SERVER['DB_BASE'] : 'doclister',
                'username' => isset($_SERVER['DB_USER']) ? $_SERVER['DB_USER'] : 'homestead',
                'password' => isset($_SERVER['DB_PASSWORD']) ? $_SERVER['DB_PASSWORD'] : 'secret',
                'prefix' => isset($_SERVER['DB_PREFIX']) ? $_SERVER['DB_PREFIX'] : 'modx_',
                'charset' => isset($_SERVER['DB_CHARSET']) ? $_SERVER['DB_CHARSET'] : 'utf8mb4',
                'method' => isset($_SERVER['DB_METHOD']) ? $_SERVER['DB_METHOD'] : 'SET NAMES',
                'collation' => isset($_SERVER['DB_COLLATION']) ? $_SERVER['DB_COLLATION'] : 'utf8mb4_unicode_ci'
            )
        );
        $database->setDebug(true)->connect();

        return $database;
    }

    public function getFullTableName($table)
    {
        return $this->db->getFullTableName($table);
    }

    public function getLoginUserID()
    {
        return $this->test_userId;
    }

    public function stripAlias($alias)
    {
        return $alias;
    }

    public function getVersionData($data = null)
    {
        return $data === null ? [static::CMS_VERSION] : static::CMS_VERSION;
    }

    public function getAliasListing($id)
    {
        if (isset($this->aliasListing[$id])) {
            $out = $this->aliasListing[$id];
        } else {
            $q = $this->db->query("SELECT id,alias,isfolder,parent,alias_visible FROM " . $this->getFullTableName("site_content") . " WHERE id=" . (int)$id);
            if ($this->db->getRecordCount($q) == '1') {
                $q = $this->db->getRow($q);
                $this->aliasListing[$id] = array(
                    'id' => (int)$q['id'],
                    'alias' => $q['alias'] == '' ? $q['id'] : $q['alias'],
                    'parent' => (int)$q['parent'],
                    'isfolder' => (int)$q['isfolder'],
                    'alias_visible' => (int)$q['alias_visible'],
                );
                if ($this->aliasListing[$id]['parent'] > 0) {
                    //fix alias_path_usage
                    if ($this->config['use_alias_path'] == '1') {
                        //&& $tmp['path'] != '' - fix error slash with epty path
                        $tmp = $this->getAliasListing($this->aliasListing[$id]['parent']);
                        $this->aliasListing[$id]['path'] = $tmp['path'] . ($tmp['alias_visible'] ? (($tmp['parent'] > 0 && $tmp['path'] != '') ? '/' : '') . $tmp['alias'] : '');
                    } else {
                        $this->aliasListing[$id]['path'] = '';
                    }
                }
                $out = $this->aliasListing[$id];
            }
        }
        return $out;
    }
}
