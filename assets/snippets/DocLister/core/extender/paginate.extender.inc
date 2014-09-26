<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

/**
 * Paginate extender for DocLister
 *
 * @category extender
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 */

class paginate_DL_Extender extends extDocLister
{
    protected $lang = true;

    private $_pages = array();

    /**
     * Call method
     *
     * @return int total pages
     *
     * @todo paginate ditto mode
     */
    protected function run()
    {
        if (($paginate = $this->DocLister->getCFGDef('paginate', '')) == '') {
            $this->DocLister->setConfig(array('start' => 0));
        }
        $pages = 0;

        switch ($paginate) {
            case 'pages':
            {
                $requestName = $this->getRequestName('page');
                $start = (isset($_GET[$requestName]) && intval($_GET[$requestName]) > 1) ? (intval($_GET[$requestName]) - 1) : 0;
                $this->DocLister->setConfig(array('start' => $start * $this->DocLister->getCFGDef('display', 0)));
                $this->_pages['current'] = $start + 1;
                $pages = $this->getListPages($paginate, 'total');
                $this->DocLister->toPlaceholders($this->currentPage(), 1, "current");
                $this->DocLister->toPlaceholders($this->renderPages("pages"), 1, "pages");
                break;
            }
            case 'ditto':
            {
                /*if($id!=''){ $dittoID = $dittoID."_"; }
                $start=isset($_GET[$dittoID.'start']) ? intval($_GET[$dittoID.'start']): 0;
                $this->setConfig(array('start'=>$start));
                $this->_pages['current']=1;
                $pages=$this->getListPages($paginate,'total');
                $this->toPlaceholders($this->renderPages("ditto",$dittoID),1,"pages");*/
                break;
            }
            default:
                {
                }
        }

        $this->DocLister->toPlaceholders(($this->currentPage() == 1 ? 1 : 0), 1, "isstart"); //[+isstart+] – is start page
        $this->DocLister->toPlaceholders(($this->currentPage() == $pages ? 1 : 0), 1, "isstop"); //[+isstop+] – is end page
        $this->DocLister->toPlaceholders($pages, 1, "totalPages"); // [+totalPages+] - total page.
        return $pages;
    }

    /**
     * Get information about pagination (current, total or all in array)
     *
     * @param string $paginate Mode paginate. Empty string if no paginate
     * @param string $name What information is interested
     * @return string|array information about pagination
     *
     * @todo Set always placeholder count document. Not only pagination
     */
    protected function getListPages($paginate, $name = '')
    {
        $this->_pages['current'] = $this->currentPage();
        if ($paginate == '') {
            $this->_pages['total'] = 1;
        } else {
            $addTables = $this->DocLister->AddTable;
            $tmp = $this->DocLister->getChildrenCount();
            $this->DocLister->AddTable = $addTables;

            $display = $this->DocLister->getCFGDef('display', 0);
            $this->_pages['total'] = ceil($tmp / $display);
            if ($this->_pages['current'] <= $this->_pages['total'] || $this->_pages['total'] == 0 || $this->DocLister->getCFGDef('noRedirect', 0)) {
                $this->DocLister->toPlaceholders($tmp, 1, "count");
                $from = ($this->_pages['current'] - 1) * $display + 1;
                $this->DocLister->toPlaceholders($from, 1, "from");
                $to = ($this->_pages['current'] == $this->_pages['total']) ? $from - 1 + $tmp - ($this->_pages['current'] - 1) * $display : $from - 1 + $display;
                $this->DocLister->toPlaceholders($to, 1, "to");
            } else {
                $this->modx->sendRedirect($this->getUrl($this->getRequestName('page')));
            }
        }
        return ($name != '' && isset($this->_pages[$name])) ? $this->_pages[$name] : $this->_pages;
    }

    /**
     * Номер текущей страницы
     * @return int
     */
    public function currentPage()
    {
        return isset($this->_pages['current']) ? (int)$this->_pages['current'] : 1;
    }

    /**
     * Общее число страниц
     * @return int
     */
    public function totalPage()
    {
        return isset($this->_pages['total']) ? (int)$this->_pages['total'] : 1;
    }

    /**
     *
     * @todo template list pages
     * @todo add parameter of count pages to display
     * @todo pagintion ditto style (in $_GET array set count offset documents)
     * @todo update Digg pagination for compbility php 5.4
     */
    private function renderPages($mode = '')
    {
        $out = '';
        switch ($mode) {
            case 'ditto':
            {
                $out = '';
                break;
            }
            default:
                {
                /**
                 * @see http://mis-algoritmos.com/2007/05/27/digg-style-pagination-class
                 */
                $out = '';
                $dir = dirname(dirname(dirname(__FILE__)));
                if (file_exists($dir . "/lib/DLpaginate.class.php")) {
                    include_once($dir . "/lib/DLpaginate.class.php");
                    if (class_exists("DLpaginate", false) && $this->totalPage() > 1) {
                        $p = new DLpaginate;
                        $p->nextT = $this->DocLister->getChunkByParam('TplNextP', '@CODE: <a href="[+link+]">[%paginate.next%] ></a>');
                        $p->prevT = $this->DocLister->getChunkByParam('TplPrevP', '@CODE: <a href="[+link+]">< [%paginate.prev%]</a>');
                        $p->numberT = $this->DocLister->getChunkByParam('TplPage', '@CODE: <a href="[+link+]" class="page">[+num+]</a>');
                        $p->currentT = $this->DocLister->getChunkByParam('TplCurrentPage', '@CODE: <b class="current">[+num+]</b>');
                        $p->dotsT = $this->DocLister->getChunkByParam('TplDotsPage', '@CODE: ...');
                        $p->prevI = '';
                        $p->nextI = '';
                        if ($this->DocLister->getCFGDef("PrevNextAlwaysShow", 0) == '1') {
                            $p->nextI = $this->DocLister->getChunkByParam('TplNextI', '@CODE: [%paginate.next%] >');
                            $p->prevI = $this->DocLister->getChunkByParam('TplPrevI', '@CODE: < [%paginate.prev%]');
                        }
                        $p->mainTpl = $this->DocLister->getChunkByParam('TplWrapPaginate', '@CODE: <div class="[+class+]">[+wrap+]</div>');
                        $p->Items($this->totalPage());
                        $p->limit($this->DocLister->getCFGDef("pageLimit", 1)); //show page count
                        $p->adjacents($this->DocLister->getCFGDef("pageAdjacents", 4));
                        $p->target($this->getUrl());
                        $p->currentPage($this->currentPage());
                        $p->parameterName($this->getRequestName('page'));
                        $p->changeClass($this->DocLister->getCFGDef("PaginateClass", "paginate"));
                        $out = $p->getOutput();
                    }
                }
                }
        }
        return $out;
    }

    public function getUrl($requestName = '')
    {
        $url = $this->DocLister->getUrl();
        $params = parse_url($url, PHP_URL_QUERY);
        parse_str(html_entity_decode($params), $params);
        if ($requestName == '') {
            switch ($this->DocLister->getCFGDef('paginate', '')) {
                case 'pages':
                {
                    $requestName = 'page';
                    break;
                }
                case 'ditto':
                {
                    $requestName = 'start';
                    break;
                }
                default:
                    {

                    }
            }
        }
        if ($requestName != '' && is_array($params)) {
            $q = http_build_query(array_merge($params, array($this->getRequestName($requestName) => null)));
            $url = explode("?", $url, 2);
            $url = $url[0];
            if (!empty($q)) {
                $url .= "?" . $q;
            }
        }
        return $url;
    }

    /**
     * Имя REQUEST переменной в пагинаторе
     *
     * @param $name основное имя REQUEST переменной в пагинаторе
     * @return string
     */
    public function getRequestName($name)
    {
        $id = $this->DocLister->getCFGDef('id', '');
        return (($id != '') ? ($id . "_") : "") . $name;
    }
}
