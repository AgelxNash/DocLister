<?php namespace RedirectMap;

include_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/lib/MODxAPI/autoTable.abstract.php");

class modRedirectMap extends \autoTable
{
    protected $table = "redirect_map";
    protected $pkName = 'id';

    protected $default_field = array(
        'uri' => '',
        'page' => '',
        'active' => 1,
        'save_get' => 0,
        'full_request' => 0,
    );

    public function __construct(\DocumentParser $modx)
    {
        $this->modx = $modx;
    }

    public function isUniq($uri)
    {
        $oldURI = $this->get('uri');
        $flag = $this->set('uri', $uri)->checkUnique($this->table, 'uri');
        $this->set('uri', $oldURI);
        return $flag;
    }

    public function save($fire_events = null, $clearCache = false)
    {
        if (!$this->checkUnique($this->table, 'uri')) {
            $this->log['UniqueUri'] = 'uri not unique <pre>' . print_r($this->get('uri'), true) . '</pre>';
            return false;
        }
        if ($this->get('uri') == '') {
            $this->log['EmptyUri'] = 'uri is empty';
            return false;
        }
        return parent::save($fire_events, $clearCache);
    }

    public function set($key, $value)
    {
        if (is_scalar($value) && is_scalar($key) && !empty($key)) {
            switch ($key) {
                case 'uri':
                {
                    $value = '/' . ltrim(trim($value), '/');
                    break;
                }
                case 'page':
                {
                    $value = (int)$value;
                    if ($value < 0) {
                        $value = 0;
                    }
                    if (empty($value)) {
                        $this->set('active', 0);
                    }
                    break;
                }
                case 'full_request':
                case 'save_get':
                case 'active':
                {
                    $value = (int)((bool)$value);
                }
            }
            $this->field[$key] = $value;
        }
        return $this;
    }
}