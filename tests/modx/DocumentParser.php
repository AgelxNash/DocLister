<?php

use AgelxNash\Modx\Evo\Database\Database;

#[\AllowDynamicProperties]
class DocumentParser
{
    const CMS_VERSION = '1.4.6';

    /** @var Database */
    public $db;

    public $documentIdentifier;
    public $documentObject = array();
    public $config = array();
    public $aliasListing = array();
    public $_TVnames = array();
    public $Event;

    public $test_userId = 0;

    public function __construct()
    {
        $this->test_define();
        $this->db = $this->test_loadDbApi();
    }

    protected function test_define()
    {

    }

    protected function test_env($key, $default)
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }

        return $default;
    }

    protected function test_loadDbApi()
    {
        $database = new Database(
            array(
                'host' => $this->test_env('DB_HOST', '127.0.0.1'),
                'database' => $this->test_env('DB_BASE', 'doclister'),
                'username' => $this->test_env('DB_USER', 'root'),
                'password' => $this->test_env('DB_PASSWORD', ''),
                'prefix' => $this->test_env('DB_PREFIX', 'modx_'),
                'charset' => $this->test_env('DB_CHARSET', 'utf8mb4'),
                'method' => $this->test_env('DB_METHOD', 'SET NAMES'),
                'collation' => $this->test_env('DB_COLLATION', 'utf8mb4_unicode_ci')
            )
        );
        $database->setDebug(true)->connect();

        return $database;
    }

    public function getFullTableName($table)
    {
        return $this->db->getFullTableName($table);
    }

    public function getLoginUserID($context = '')
    {
        return $this->test_userId;
    }

    public function getLocale()
    {
        return 'en';
    }

    public function makeUrl($id, $alias = '', $args = '', $scheme = '')
    {
        return 'http://example.com/' . $id;
    }

    public function sendRedirect($url, $count_attempts = 0, $type = '', $responseCode = '')
    {
    }

    public function setPlaceholder($key, $value)
    {
    }

    public function regClientStartupHTMLBlock($html)
    {
    }

    public function toPlaceholder($key, $value, $prefix = '')
    {
    }

    public function __call($name, $arguments)
    {
        return null;
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
