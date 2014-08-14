<?php
include_once(MODX_BASE_PATH.'assets/lib/APIHelpers.class.php');

abstract class MODxAPI extends MODxAPIhelpers
{
    protected $modx = null;
    protected $log = array();
    protected $field = array();
    protected $default_field = array();
    protected $id = null;
    protected $set = array();
    protected $newDoc = false;
    protected $pkName = 'id';

    public function __construct($modx)
    {
        try {
            if ($modx instanceof DocumentParser) {
                $this->modx = $modx;
            } else throw new Exception('MODX should be instance of DocumentParser');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    final protected function modxConfig($name, $default = null)
    {
        return isset($this->modx->config[$name]) ? $this->modx->config[$name] : $default;
    }

    final protected function query($SQL)
    {
        return $this->modx->db->query($SQL);
    }

    final protected function invokeEvent($name, $data = array(), $flag = false)
    {
        $flag = (isset($flag) && $flag != '') ? (bool)$flag : false;
        if ($flag) {
            $this->modx->invokeEvent($name, $data);
        }
        return $this;
    }

    final public function clearLog()
    {
        $this->log = array();
        return $this;
    }

    final public function getLog()
    {
        return $this->log;
    }

    final public function list_log($flush = false)
    {
        echo '<pre>' . print_r($this->log, true) . '</pre>';
        if ($flush) $this->clearLog();
        return $this;
    }

    final public function getCachePath($full = true)
    {
        $path = $this->modx->getCachePath();
        if ($full) {
            $path = MODX_BASE_PATH . substr($path, strlen(MODX_BASE_URL));
        }
        return $path;
    }

    final public function clearCache($fire_events = null)
    {
        $this->modx->clearCache();
        include_once(MODX_MANAGER_PATH . 'processors/cache_sync.class.processor.php');
        $sync = new synccache();
        $path = $this->getCachePath(true);
        $sync->setCachepath($path);
        $sync->setReport(false);
        $sync->emptyCache();

        $this->invokeEvent('OnSiteRefresh', array(), $fire_events);
    }

    public function set($key, $value)
    {
        if (is_scalar($value) && is_scalar($key) && !empty($key)) {
            $this->field[$key] = $value;
        }
        return $this;
    }

    final public function getID()
    {
        return $this->id;
    }

    public function get($key)
    {
        return isset($this->field[$key]) ? $this->field[$key] : null;
    }

    public function fromArray($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->set($key, $value);
            }
        }
        return $this;
    }

    final protected function Uset($key, $id = '')
    {
        $tmp = '';
        if (!isset($this->field[$key])) {
            $tmp = "{$key}=''";
            $this->log[] = "{$key} is empty";
        } else {
            try {
                if ($this->issetField($key) && is_scalar($this->field[$key])) {
                    $tmp = "`{$key}`='{$this->modx->db->escape($this->field[$key])}'";
                } else throw new Exception("{$key} is invalid <pre>" . print_r($this->field[$key], true) . "</pre>");
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
        if (!empty($tmp)) {
            if ($id == '') {
                $this->set[] = $tmp;
            } else {
                $this->set[$id][] = $tmp;
            }
        }
        return $this;
    }


    final protected function cleanIDs($IDs, $sep = ',', $ignore = array())
    {
        $out = array();
        if (!is_array($IDs)) {
            try {
                if (is_scalar($IDs)) {
                    $IDs = explode($sep, $IDs);
                } else {
                    $IDs = array();
                    throw new Exception('Invalid IDs list <pre>' . print_r($IDs, 1) . '</pre>');
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
        foreach ($IDs as $item) {
            $item = trim($item);
            if (is_scalar($item) && (int)$item >= 0) { //Fix 0xfffffffff
                if (!empty($ignore) && in_array((int)$item, $ignore, true)) {
                    $this->log[] = 'Ignore id ' . (int)$item;
                } else {
                    $out[] = (int)$item;
                }
            }
        }
        $out = array_unique($out);
        return $out;
    }

    final public function fromJson($data, $callback = null)
    {
        try {
            if (is_scalar($data) && !empty($data)) {
                $json = json_decode($data);
            } else throw new Exception("json is not string with json data");
            if ($this->jsonError($json)) {
                if (isset($callback) && is_callable($callback)) {
                    call_user_func_array($callback, array($json));
                } else {
                    if (isset($callback)) throw new Exception("Can't call callback JSON unpack <pre>" . print_r($callback, 1) . "</pre>");
                    foreach ($json as $key => $val) {
                        $this->set($key, $val);
                    }
                }
            } else throw new Exception('Error from JSON decode: <pre>' . print_r($data, 1) . '</pre>');
        } catch (Exception $e) {
            die($e->getMessage());
        }
        return $this;
    }

    final public function toJson($callback = null)
    {
        try {
            $data = $this->toArray();
            $json = json_encode($data);
            if (!$this->jsonError($data, $json)) {
                $json = false;
                throw new Exception('Error from JSON decode: <pre>' . print_r($data, 1) . '</pre>');
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
        return $json;
    }

    final protected function jsonError($data)
    {
        $flag = false;
        if (!function_exists('json_last_error')) {
            function json_last_error()
            {
                return JSON_ERROR_NONE;
            }
        }
        if (json_last_error() === JSON_ERROR_NONE && is_object($data) && $data instanceof stdClass) {
            $flag = true;
        }
        return $flag;
    }

    public function toArray($prefix = '', $suffix = '', $sep = '_')
    {
        $tpl = '';
        $plh = '[+key+]';
        if ($prefix !== '') {
            $tpl = $prefix . $sep;
        }
        $tpl .= $plh;
        if ($suffix !== '') {
            $tpl .= $sep . $suffix;
        }
        $out = array();
        $fields = $this->field;
        $fields[$this->pkName] = $this->getID();
        if ($tpl != $plh) {
            foreach ($fields as $key => $value) {
                $out[str_replace($plh, $key, $tpl)] = $value;
            }
        } else {
            $out = $fields;
        }
        return $out;
    }

    final protected function makeTable($table)
    {
        return (isset($this->_table[$table])) ? $this->_table[$table] : $this->modx->getFullTableName($table);
    }

    final protected function sanitarIn($data, $sep = ',')
    {
        if (!is_array($data)) {
            $data = explode($sep, $data);
        }
        $out = array();
        foreach ($data as $item) {
            $out[] = $this->modx->db->escape($item);
        }
        $out = "'" . implode("','", $out) . "'";
        return $out;
    }

    protected function checkUnique($table, $field, $PK = 'id')
    {
        $val = $this->get($field);
        if ($val != '') {
            $sql = $this->query("SELECT `" . $this->modx->db->escape($PK) . "` FROM " . $this->makeTable($table) . " WHERE `" . $this->modx->db->escape($field) . "`='" . $this->modx->db->escape($val) . "'");
            $id = $this->modx->db->getValue($sql);
            if (is_null($id) || (!$this->newDoc && $id == $this->getID())) {
                $flag = true;
            } else {
                $flag = false;
            }
        } else {
            $flag = false;
        }
        return $flag;
    }

    public function create($data = array())
    {
        $this->close();
        $this->fromArray($data);
        return $this;
    }

    public function copy($id)
    {
        $this->edit($id)->id = 0;
        $this->newDoc = true;
        return $this;
    }

    public function close()
    {
        $this->newDoc = true;
        $this->id = null;
        $this->field = array();
        $this->set = array();
    }

    public function issetField($key)
    {
        return (is_scalar($key) && array_key_exists($key, $this->default_field));
    }

    abstract public function edit($id);

    abstract public function save($fire_events = null, $clearCache = false);

    abstract public function delete($ids, $fire_events = null);

    final public function sanitarTag($data)
    {
        return parent::sanitarTag($this->modx->stripTags($data));
    }

    final protected function checkVersion($version, $dmi3yy = true)
    {
        $flag = false;
        $currentVer = $this->modx->getVersionData('version');
        if (is_array($currentVer)) {
            $currentVer = isset($currentVer['version']) ? $currentVer['version'] : '';
        }
        $tmp = substr($currentVer, 0, strlen($version));
        if (version_compare($tmp, $version, '>=')) {
            $flag = true;
            if ($dmi3yy) {
                $flag = (boolean)preg_match('/^' . $tmp . '(.*)\-d/', $currentVer);
            }
        }
        return $flag;
    }

    protected function eraseField($name)
    {
        $flag = false;
        if (array_key_exists($name, $this->field)) {
            unset($this->field[$name]);
            $flag = true;
        }
        return $flag;
    }
}

class MODxAPIhelpers
{
    public function emailValidate($email, $dns = true)
    {
        return \APIhelpers::emailValidate($email, $dns);
    }
    public function genPass($len, $data = '')
    {
        return \APIhelpers::genPass($len, $data);
    }
    public function getUserIP($out = '127.0.0.1')
    {
        return \APIhelpers::getUserIP($out);
    }

    public function sanitarTag($data)
    {
        return \APIhelpers::sanitarTag($data);
    }

    public function checkString($value, $minLen = 1, $alph = array(), $mixArray = array(), $debug = false)
    {
        return \APIhelpers::checkString($value, $minLen, $alph, $mixArray, $debug);
    }
}