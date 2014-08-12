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
            $params = $this->getParams($out);
            if(is_scalar($prepare)){
                $names = explode(",", $prepare);
                foreach($names as $item){
                    $params['data'] = $this->callPrepare($item, $params);
                }
            }else{
                $params['data'] = $this->callPrepare($prepare, $params);
            }
            $out = $params['data'];
        }
        return $out;
    }

    public function getParams(array $data = array()){
        return array(
            '_extDocLister' => $this,
            '_DocLister' => $this->DocLister,
            'data' => $data,
            'modx' => $this->modx
        );
    }
	
    public function callPrepare($name, array $params){
        $out = null;
        if((is_object($name) && ($name instanceof Closure)) || is_callable($name)){
            $data = call_user_func($name, $params['data'], $params['modx'], $params['_DocLister'], $params['_extDocLister']);
        }else{
            $data = $this->modx->runSnippet($name, $params);
        }
        switch(true){
            case is_array($data):{
                $out = $data;
                break;
            }
            case ($data==='' || (is_bool($data) && $data===false)):{
                $out = false;
                break;
            }
            case is_string($data):{
                if($data[0] == '[' || $data[0] == '{'){
                    $out = $this->DocLister->jsonDecode($data, array('assoc' => true), true);
                }else{
                    $out = unserialize($data);
                }
            }
        }
        return is_null($out) ? $params['data'] : $out;
    }
}