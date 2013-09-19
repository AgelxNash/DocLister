<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

/**
 * Prepare extender for DocLister
 *
 * @category extender
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 */

class prepare_DL_Extender extends extDocLister
{
    private $_store = array();
    public function setStore($name,$val){
        $this->_store[$name] = $val;
    }
    public function getStore($name){
        return isset($this->_store[$name]) ? $this->_store[$name] : null;
    }
    protected function run()
    {
        $out = $this->_cfg;
        if (($prepare = $this->DocLister->getCFGDef('prepare', '')) != '') {
            $out = array(
                '_extDocLister' => $this,
                '_DocLister' => $this->DocLister,
                'data' => $out,
                'modx' => $this->modx
            );
            $out = unserialize($this->modx->runSnippet($prepare, $out));
        }
        return $out;
    }
}