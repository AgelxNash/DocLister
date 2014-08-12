<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

/**
 * tv extender for DocLister
 *
 * @category extender
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 *
 * Предзагрузка данных о TV параметрах
 * Обработка TV параметров перед выводом
 *
 */
class tv_DL_Extender extends extDocLister
{
    protected $_TVnames = array();
    protected $tvValuesTable = 'site_tmplvar_contentvalues';
    protected $_TVprefix = null;

    public function __construct($DocLister, $name)
    {
        parent::__construct($DocLister, $name);
        $this->tvValuesTable = $this->DocLister->getCFGDef('tvValuesTable', $this->tvValuesTable());
        $this->_TVprefix = $this->getTVprefix();
    }

    protected function run()
    {
        return true;
    }

    public function tvValuesTable()
    {
        return $this->tvValuesTable;
    }

    public function getTVnames()
    {
        return $this->_TVnames;
    }

    public function getAllTV_Name($reload = false)
    {
        if (empty($this->_TVnames) || $reload) {
            $from = $this->DocLister->getTable('site_tmplvars');
            $rs = $this->DocLister->dbQuery("SELECT id, name FROM {$from}");
            $rows = $this->modx->db->makeArray($rs);
            $out = array();
            foreach ($rows as $item) {
                $out[$item['name']] = $item['id'];
            }
            $this->_TVnames = $out;
        }
        return $this->_TVnames;
    }

    public function getTVList($IDs, $tvlist)
    {
        $tv = $this->getTVid($tvlist);
        $tvId = array_keys($tv);
        $tbl_site_tmplvar_contentvalues = $this->DocLister->getTable($this->tvValuesTable());
        $sanitarInIDs = $this->DocLister->sanitarIn($IDs);
        $implodeTvId = implode(',', $tvId);
        $where = "contentid IN({$sanitarInIDs}) AND tmplvarid IN({$implodeTvId})";
        $rs = $this->DocLister->dbQuery("SELECT tmplvarid,value,contentid FROM {$tbl_site_tmplvar_contentvalues} WHERE {$where}");
        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        $tvPrefix = $this->_TVprefix;

        foreach ($rows as $item) {
            $out[$item['contentid']][$tvPrefix . $tv[$item['tmplvarid']]] = $item['value'];
        }
        $tmp = explode(",", $this->DocLister->sanitarIn($IDs, ',', false));
        foreach ($tmp as $item) {
            if (!isset($out[$item])) {
                $out[$item] = array();
            };
        }
        $renderTV = $this->getListRenderTV();
        $tvDef = $this->loadTVDefault($tvId);
        $TVkeys = array_keys($tvDef);
        foreach ($out as $itemid => $item) {
            foreach ($TVkeys as $name) {
                if (!isset($out[$itemid][$name])) {
                    $out[$itemid][$name] = $tvDef[$name]['value'];
                }
                if (in_array($name, $renderTV) || $renderTV == array("*")) {
                    $out[$itemid][$name] = $this->renderTV($itemid, $name, $out[$itemid][$name], $tvDef[$name]);
                }
            }
        }
        return $out;
    }

    public function getTVprefix()
    {
        $tvPrefix = $this->DocLister->getCFGDef('tvPrefix', 'tv');
        if (!empty($tvPrefix)) {
            $tvPrefix .= '.';
        }
        return $tvPrefix;
    }

    public function getListRenderTV()
    {
        $tmp = $this->DocLister->getCFGDef('renderTV', '');
        if ($tmp != '' && $tmp != '*') {
            $tmp = explode(",", $tmp);
            if (in_array("*", $tmp)) {
                $tmp = array("*");
            } else {
                $out = array_unique($tmp);
                $tmp = array();
                foreach ($out as $item) {
                    $tmp[] = $this->_TVprefix . $item;
                }
            }
        } else {
            $tmp = array($tmp);
        }
        return $tmp;
    }

    public function renderTV($iddoc, $tvname, $tvval, $param)
    {
        include_once MODX_MANAGER_PATH . "includes/tmplvars.format.inc.php";
        include_once MODX_MANAGER_PATH . "includes/tmplvars.commands.inc.php";
        return getTVDisplayFormat($tvname, $tvval, $param['display'], $param['display_params'], $param['type'], $iddoc, '');
    }

    public function getTVid($tvlist)
    {
        if (!empty($tvlist) && is_scalar($tvlist)) {
            $tvlist = explode(",", $tvlist);
        }
        $out = array();
        foreach ($tvlist as $tv) {
            $tv = trim($tv);
            if (isset($this->_TVnames[$tv])) {
                $out[$this->_TVnames[$tv]] = $tv;
            }
        }
        return $out;
    }

    public function loadTVDefault($tvId)
    {
        $tbl_site_tmplvars = $this->DocLister->getTable('site_tmplvars');
        $fields = 'id,name,default_text as value,display,display_params,type';
        $implodeTvId = implode(',', $tvId);
        $rs = $this->DocLister->dbQuery("SELECT {$fields} FROM {$tbl_site_tmplvars} WHERE id IN({$implodeTvId})");
        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out[$this->_TVprefix . $item['name']] = $item;
        }
        return $out;
    }

    public function addTVSortWithDefault($name)
    {
        $dTV = explode(',', $this->DocLister->getCFGDef('tvSortWithDefault', ''));
        $dTV[] = $name;
        $tvList = implode(",", array_unique($dTV));
        $this->DocLister->setConfig(array('tvSortWithDefault' => $tvList));
        return $tvList;
    }

    public function injectSortByTV($table, $sort)
    {
        if (preg_match("/^ORDER BY (.*)/", $sort, $match)) {
            $TVnames = $this->getTVnames();
            if (!is_array($TVnames)) {
                $TVnames = array();
            }
            $matches = explode(",", $match[1]);
            $sortType = explode(",", $this->DocLister->getCFGDef('tvSortType'));
            $withDefault = explode(",", $this->DocLister->getCFGDef('tvSortWithDefault'));
            foreach ($matches as $i => &$item) {
                $item = explode(" ", trim($item), 2);
                if (isset($TVnames[$item[0]])) {
                    $exists = $this->DocLister->checkTableAlias($item[0], $this->tvValuesTable());
                    $prefix = $this->DocLister->TableAlias($item[0], $this->tvValuesTable(), 'dltv_' . $item[0] . '_' . ($i + 1));
                    if (!$exists) {
                        $table .= " LEFT JOIN " . $this->DocLister->getTable($this->tvValuesTable(), $prefix) . "
                        on `" . $prefix . "`.`contentid`=`c`.`id` AND `" . $prefix . "`.`tmplvarid`=" . $TVnames[$item[0]];
                    }
                    if (in_array($item[0], $withDefault)) {
                        $exists = $this->DocLister->checkTableAlias($item[0], "site_tmplvars");
                        $dPrefix = $this->DocLister->TableAlias($item[0], "site_tmplvars", 'd_' . $prefix);
                        if (!$exists) {
                            $table .= " LEFT JOIN " . $this->DocLister->getTable("site_tmplvars", $dPrefix) . " on `" . $dPrefix . "`.`id` = " . $TVnames[$item[0]];
                        }
                        $field = "IFNULL(`{$prefix}`.`value`, `{$dPrefix}`.`default_text`)";
                    } else {
                        $field = "`{$prefix}`.`value`";
                    }
                    $item[0] = $this->DocLister->changeSortType($field, isset($sortType[$i]) ? $sortType[$i] : null);
                }
                $item = implode(" ", $item);
            }
            $sort = "ORDER BY " . implode(",", $matches);
        }
        return array($table, $sort);
    }
}