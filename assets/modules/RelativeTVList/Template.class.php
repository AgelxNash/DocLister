<?php namespace DLCity;

class Template
{
    protected $_modx = null;
    protected $_tplFolder = null;

    const TPL_EXT = 'html';

    public $vars = array(
        'modx_lang_attribute',
        'modx_textdir',
        'manager_theme',
        'modx_manager_charset',
        '_lang',
        '_style',
        'e',
        'SystemAlertMsgQueque',
        'incPath',
        'content'
    );
    protected static $_ajax = false;

    public function __construct(\DocumentParser $modx, $ajax = false)
    {
        $this->_modx = $modx;
        self::$_ajax = (boolean)$ajax;
        $this->loadVars();
        $this->_tplFolder = dirname(__FILE__) . "/template/";
    }

    public static function isAjax()
    {
        return self::$_ajax;
    }

    public function showHeader()
    {
        return $this->_getMainTpl('header.inc.php');
    }

    protected function _getMainTpl($name)
    {
        $content = '';
        if (!self::isAjax()) {

            ob_start();
            extract($this->vars);
            if (file_exists($incPath . $name)) {
                include($incPath . $name);
                $content = ob_get_contents();
            }
            ob_end_clean();
        }
        return $content;
    }

    public function loadVars()
    {
        $vars = array();
        foreach ($this->vars as $item) {
            global $$item;
            $vars[$item] = $$item;
        }
        $this->vars = $vars;
        $this->vars['tplClass'] = $this;
        $this->vars['modx'] = $this->_modx;
    }

    public function showFooter()
    {
        return $this->_getMainTpl('footer.inc.php');
    }

    public function showBody($TplName, array $tplParams = array())
    {
        ob_start();
        if (file_exists($this->_tplFolder . $TplName . "." . self::TPL_EXT)) {
            extract($this->vars);
            include($this->_tplFolder . $TplName . "." . self::TPL_EXT);
        }
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public static function getParam($key, array $param = array(), $default = null)
    {
        return isset($param[$key]) ? $param[$key] : $default;
    }

    public function makeUrl($action, array $data = array())
    {
        $action = is_scalar($action) ? $action : '';
        $content = self::getParam('content', $this->vars, array());
        $data = array_merge(
            array(
                'mode' => \DLCity\Helper::getMode()
            ),
            $data,
            array(
                'a' => 112,
                'action' => $action,
                'id' => self::getParam('id', $content, 0)
            )
        );
        return implode("?", array($this->_modx->getManagerPath(), http_build_query($data)));
    }

    public static function showLog()
    {
        return self::isAjax() ? 'log' : 'main';
    }

    public function Lists()
    {
        $out = '';
        $this->_modx->documentIdentifier = '1';
        $this->_modx->config['site_url'] = MODX_MANAGER_URL;
        $data = array();
        switch (\DLCity\Helper::getMode()) {
            case 'street':
            {
                $dataID = (int)self::getParam('dataID', $_GET);
                $data = array('table' => 'street',
                    'addWhereList' => 'parent_id=' . $dataID,
                    'tpl' => '@CODE: ' . $this->showBody('table/StreetBody'),
                    'ownerTPL' => '@CODE: ' . $this->showBody('table/StreetTable')
                );
                break;
            }
            case 'city':
            {
                $data = array('table' => 'city',
                    'tpl' => '@CODE: ' . $this->showBody('table/CityBody'),
                    'ownerTPL' => '@CODE: ' . $this->showBody('table/CityTable')
                );
                break;
            }
        }
        if (!empty($data)) {
            $out = $this->_modx->runSnippet('DocLister', array_merge(array(
                'controller' => 'onetable',
                'sortBy' => 'name',
                'sortDir' => 'ASC',
                'altItemClass' => 'gridAltItem',
                'itemClass' => 'gridItem',
                'display' => '10',
                'id' => 'dl',
                'noneTPL' => '@CODE: База пуста',
                'noneWrapOuter' => 0,
                'paginate' => 'pages',
                'prepare' => function (array $data = array(), \DocumentParser $modx, \onetableDocLister $_DocLister) {
                        if ($_DocLister->getCFGDef('table') == 'city') {
                            $q = $modx->db->query("SELECT count(id) FROM " . $_DocLister->getTable('street') . " WHERE parent_id='" . (int)$data['id'] . "'");
                            $data['total'] = $modx->db->getValue($q);
                            $q = $modx->db->query("SELECT count(id) FROM " . $_DocLister->getTable('street') . " WHERE parent_id='" . (int)$data['id'] . "' AND `hide`='0'");
                            $data['show'] = $modx->db->getValue($q);
                        }
                        $data['action'] = $data['hide'] ? 'add' : 'stop';

                        $data['class'] = (isset($data['iteration']) && $data['iteration'] % 2) ? $_DocLister->getCFGDef('itemClass') : $_DocLister->getCFGDef('altItemClass');
                        return $data;
                    },
                'idType' => 'documents',
                'ignoreEmpty' => 1
            ), $data));
            $out .= $this->_modx->getPlaceholder('dl.pages');
        }
        return $out;
    }
}