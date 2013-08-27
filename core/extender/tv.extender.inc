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

    protected function run(){
        return true;
    }

    public function getTVnames(){
        return $this->_TVnames;
    }
    public function getAllTV_Name()
    {
        $rs = $this->modx->db->select('id,name', $this->DocLister->getTable('site_tmplvars'));
        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out[$item['name']] = $item['id'];
        }
        return $this->_TVnames = $out;
    }

    public function getTVList($IDs, $tvlist)
    {
        $tv = $this->getTVid($tvlist);
        $tvId = array_keys($tv);
        $tbl_site_tmplvar_contentvalues = $this->DocLister->getTable('site_tmplvar_contentvalues');
        $sanitarInIDs = $this->DocLister->sanitarIn($IDs);
        $implodeTvId = implode(',', $tvId);
        $where = "contentid IN({$sanitarInIDs}) AND tmplvarid IN({$implodeTvId})";
        $rs = $this->modx->db->select('tmplvarid,value,contentid', $tbl_site_tmplvar_contentvalues, $where);
        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out[$item['contentid']]['tv.' . $tv[$item['tmplvarid']]] = $item['value'];
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

    protected function getListRenderTV()
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

    protected function renderTV($iddoc, $tvname, $tvval, $param)
    {
        include_once MODX_MANAGER_PATH . "includes/tmplvars.format.inc.php";
        include_once MODX_MANAGER_PATH . "includes/tmplvars.commands.inc.php";
        return getTVDisplayFormat($tvname, $tvval, $param['display'], $param['display_params'], $param['type'], $iddoc, '');
    }

    //@TODO: _TVnames
    protected function getTVid($tvlist)
    {
        if (!empty($tvlist) && is_scalar($tvlist)) {
            $tvlist = explode(",", $tvlist);
        }
        $out = array();
        foreach ($tvlist as $tv) {
            if (isset($this->_TVnames[$tv])) {
                $out[$this->_TVnames[$tv]] = $tv;
            }
        }
        return $out;
    }

    protected function loadTVDefault($tvId)
    {
        $tbl_site_tmplvars = $this->DocLister->getTable('site_tmplvars');
        $fields = 'id,name,default_text as value,display,display_params,type';
        $implodeTvId = implode(',', $tvId);
        $rs = $this->modx->db->select($fields, $tbl_site_tmplvars, "id IN({$implodeTvId})");
        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out['tv.' . $item['name']] = $item;
        }
        return $out;
    }
}