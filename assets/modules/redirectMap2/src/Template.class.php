<?php namespace RedirectMap;

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
        $this->_tplFolder = dirname(dirname(__FILE__)) . "/template/";
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

    public function makeUrl($action, array $data = array(), $full = false)
    {
        $action = is_scalar($action) ? $action : '';
        $content = self::getParam('content', $this->vars, array());
        $data = array_merge(
            array(
                'mode' => Helper::getMode()
            ),
            $data,
            array(
                'a' => 112,
                'action' => $action,
                'id' => self::getParam('id', $content, 0)
            )
        );
        $out = implode("?", array($this->_modx->getManagerPath(), http_build_query($data)));
        if ($full) {
            $out = $this->_modx->getConfig('site_url') . ltrim($out, '/');
        }
        return $out;
    }

    public static function showLog()
    {
        return self::isAjax() ? 'log' : 'main';
    }

    public function Lists()
    {
        $out = '';
        $this->_modx->documentIdentifier = $this->_modx->getConfig('site_start');
        $this->_modx->config['site_url'] = MODX_MANAGER_URL;

        $method = $this->getParam('method', $_GET, '');
        $addWhere = array();
        switch ($method) {
            case 'doc':
            {
                $docID = (int)$this->getParam('doc', $_GET, 0);
                $addWhere[] = '`page`=' . $docID;
                break;
            }
            case 'active':
            {
                $addWhere[] = '`active`=1';
                break;
            }
            case 'deactive':
            {
                $addWhere[] = '`active`=0';
                break;
            }
        }

        /**
         * По какому полю вести сортировку
         */
        $key = $this->getParam('by', $_GET, 'page');
        $modSeo = new modRedirectMap($this->_modx);
        if (!$modSeo->issetField($key)) {
            $key = 'uri';
        }

        $data = array(
            'orderBy' => '`' . $key . '` ' . $this->getParam('order', $_GET, 'ASC'),
            'addWhereList' => implode(" AND ", $addWhere)
        );

        /**
         * Хакаем URL пагинатора
         */
        parse_str(parse_url(MODX_SITE_URL . $_SERVER['REQUEST_URI'], PHP_URL_QUERY), $URL);
        $_SERVER['REQUEST_URI'] = $this->_modx->getManagerPath() . "?" . http_build_query(array_merge($URL, array('q' => null, 'action' => null)));
        if (!empty($data)) {
            $out = $this->_modx->runSnippet('DocLister', array_merge(array(
                'controller' => 'onetable',
                'table' => 'redirect_map',
                'tpl' => '@CODE: ' . $this->showBody('table/body'),
                'ownerTPL' => '@CODE: ' . $this->showBody('table/wrap'),
                'altItemClass' => 'gridAltItem',
                'itemClass' => 'gridItem',
                'display' => self::getParam('display', $this->_modx->event->params),
                'id' => 'dl',
                'pageInfoTpl' => '@CODE: ' . $this->showBody('table/pageInfo'),
                'pageInfoEmptyTpl' => '@CODE: ' . $this->showBody('table/pageInfoEmpty'),
                'debug' => 0,
                'noneTPL' => '@CODE: Нет данных',
                'noneWrapOuter' => 0,
                'paginate' => 'pages',
                'prepare' => function (array $data = array(), \DocumentParser $modx, \onetableDocLister $_DocLister) {
                        if (!empty($data['page'])) {
                            include_once(MODX_BASE_PATH . "assets/lib/MODxAPI/modResource.php");
                            $DOC = new \modResource($modx);
                            $DOC->edit($data['page']);

                            $data['doc_pagetitle'] = $DOC->getID() ? $DOC->get('pagetitle') : '';
                            $data['doc_parent'] = $DOC->getID() ? $DOC->get('parent') : '0';

                            $tpl = 'pageInfoTpl';
                        } else {
                            $tpl = 'pageInfoEmptyTpl';
                        }
                        $data['pageInfo'] = $_DocLister->parseChunk($_DocLister->getCFGDef($tpl), $data);

                        $data['saveGet'] = $data['save_get'] ? 'save' : 'exclamation';
                        $data['fullRequest'] = $data['full_request'] ? 'page_white_copy' : 'page_white_magnify';

                        $data['active'] = $data['active'] ? 'stop' : 'add';
                        $data['class'] = (isset($data['dl.iteration']) && $data['dl.iteration'] % 2) ? $_DocLister->getCFGDef('itemClass') : $_DocLister->getCFGDef('altItemClass');

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