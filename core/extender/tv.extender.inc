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
class tv_DL_Extender extends extDocLister{
    protected $_TVnames = array();
    /**
     * Массив подключаемых таблиц для TV
     * @var array
     */
    protected $_TVTable=array();


    protected function run(){
        return true;
    }
    public function getTVnames(){
        return $this->_TVnames;
    }
    public function getAllTV_Name($reload = false)
    {
        if(empty($this->_TVnames) || $reload){
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
        $tbl_site_tmplvar_contentvalues = $this->DocLister->getTable('site_tmplvar_contentvalues');
        $sanitarInIDs = $this->DocLister->sanitarIn($IDs);
        $implodeTvId = implode(',', $tvId);
        $where = "contentid IN({$sanitarInIDs}) AND tmplvarid IN({$implodeTvId})";
        $rs = $this->DocLister->dbQuery("SELECT tmplvarid,value,contentid FROM {$tbl_site_tmplvar_contentvalues} WHERE {$where}");
        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out[$item['contentid']]['tv.' . $tv[$item['tmplvarid']]] = $item['value'];
        }
        $tmp = explode(",", $this->DocLister->sanitarIn($IDs, ',', false));
        foreach($tmp as $item){
            if(!isset($out[$item])){
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
                    $tmp[] = "tv." . $item;
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
            $out['tv.' . $item['name']] = $item;
        }
        return $out;
    }

    public function TableAlias($name, $table, $alias){
        if(!isset($this->_TVTable[$table][$name])){
            $this->_TVTable[$table][$name] = $alias;
        }
        return $this->_TVTable[$table][$name];
    }

    public function checkTableAlias($name, $table){
        return isset($this->_TVTable[$table][$name]);
    }

}