<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
/**
 * DocLister
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @date 19.12.2012
 *
 *	@TODO add controller for work with plugin http://modx.com/extras/package/quid and get TV value via LEFT JOIN
 *	@TODO add controller for filter by TV values
 *  @TODO add extender user for information user by id
 *  @TODO add method load default template
 *  @TODO add example custom controller for build google sitemap.xml
 *  @TODO add method build tree for replace Wayfinder if need TV value in menu OR sitemap
 *  @TODO add controller for show list web-user with filter by group and other user information
 *  @TODO create new site_content controller without TagSaver plugin
*/

abstract class DocLister {
    protected  $_docs=array();
    protected $IDs;
    protected $modx;
    protected $extender;
    protected $_plh=array();
    protected $_lang=array();
    private  $_cfg=array();


    function __construct($modx,$cfg){
		mb_internal_encoding("UTF-8");
		$this->modx=$modx;
		$this->setConfig($cfg);
		$this->loadLang('core');
        $this->loadExtender($this->getCFGDef("extender",""));
        if($this->extender['request'] instanceof requestDocLister){
            $this->extender['request']->init($this,$this->getCFGDef("requestActive",""));
        }
	}

    abstract public function getUrl($id=0);
    abstract public function getDocs($tvlist='');
    abstract public function render($tpl='');

    /*
     * CORE Block
     */
    final public function loadExtender($ext){
         $ext=explode(",",$ext);
         foreach($ext as $item){
             $this->_loadExtender($item);
         }
    }
    final public function setConfig($cfg){
		if(is_array($cfg)){
			$this->_cfg=array_merge($this->_cfg,$cfg);
		}
	}
    final public function getCFGDef($name,$def){
		return isset($this->_cfg[$name])?$this->_cfg[$name]:$def;
	}
    final public function toPlaceholders($data,$set=0,$key='contentPlaceholder'){
        $this->_plh[$key]=$data;
		if($set==0){
			$set=$this->getCFGDef('contentPlaceholder',0);
		}
		if($set!=0){
			$id=$this->getCFGDef('id','');
			if($id!='') $id.=".";
			$this->modx->toPlaceholder($key,$data,$id);
		}else{
			return $data;
		}
	}
	final protected function sanitarIn($data,$sep=','){
		if(!is_array($data)){
			$data=explode($sep,$data);
		}
		$out=array();
		foreach($data as $item){
			$out[]=$this->modx->db->escape($item);
		}
		$out="'".implode("','",$out)."'";
		return $out;
	}
    final protected function loadLang($name='core',$lang=''){
		if($lang==''){
			$lang=$this->getCFGDef('lang',$this->modx->config['manager_language']);
		}
        if(file_exists(dirname(__FILE__)."/lang/".$lang."/".$name.".inc.php")){
            $tmp=include_once(dirname(__FILE__)."/lang/".$lang."/".$name.".inc.php");
            if(is_array($tmp)) {
                $this->_lang=array_merge($this->_lang,$tmp);
            }
        }
	}
    final public function getMsg($name,$def=''){
        return (isset($this->_lang[$name])) ? $this->_lang[$name] : $def;
    }
    final protected  function renameKeyArr($data,$prefix='',$suffix='',$sep='.'){
        $out=array();
        if($prefix=='' && $suffix==''){
            $out=$data;
        }else{
            if($prefix!=''){
                $prefix=$prefix.$sep;
            }
            if($suffix!=''){
                $suffix=$sep.$suffix;
            }
            foreach($data as $key=>$item){
                $out[$prefix.$key.$suffix]=$item;
            }
        }
        return $out;
    }
    final private function _loadExtender($name){
        $flag=false;

        $classname=($name!='') ? $name."DocLister" : "";
        if($classname!='' && isset($this->extender[$name]) && $this->extender[$name] instanceof $classname){
            $flag=true;
        }else{
            if(!class_exists($classname,false) && $classname!=''){
                if(file_exists(dirname(__FILE__)."/controller/extender/".$name.".extender.inc")){
                    include_once(dirname(__FILE__)."/controller/extender/".$name.".extender.inc");
                }
            }
            if(class_exists($classname,false) && $classname!=''){
                $this->extender[$name]=new $classname;
                $this->loadLang($name);
                $flag=true;
            }
        }
        return $flag;
    }

    /*
     * IDs BLOCK
     */
    final public function setIDs($IDs){
        $IDs=$this->cleanIDs($IDs);
        $type = $this->getCFGDef('idType','parents');
        $depth = $this->getCFGDef('depth','1');
        if($type=='parents' && $depth>1){
            $tmp=$IDs;
            do{
                if(count($tmp)>0){
                    $tmp=$this->getChildernFolder($tmp);
                    $IDs=array_merge($IDs,$tmp);
                }
            }while((--$depth)>1);
        }
        return ($this->IDs=$IDs);
    }

    final public function cleanIDs($IDs,$sep=',') {
        $out=array();
        if(!is_array($IDs)){
            $IDs=explode($sep,$IDs);
        }
        foreach($IDs as $item){
            if((int)$item==$item){
                $out[]=$item;
            }
        }
        $out = array_unique($out);
		return $out;
	}
    final protected function checkIDs(){
           return (is_array($this->IDs) && count($this->IDs)>0) ? true : false;
    }

    /*
     * SQL BLOCK
     */
    abstract public function getChildrenCount();
    abstract public function getChildernFolder($id);

    final protected  function LimitSQL($limit=0,$offset=0){
		$ret='';
		if($limit==0){
			$limit=$this->getCFGDef('display',0);
		}
		if($offset==0){
			$offset=$this->getCFGDef('offset',0);
		}
		$offset+=$this->getCFGDef('start',0);
		$total=$this->getCFGDef('total',0);
		if($limit<($total-$limit)){
			$limit=$total-$offset;
		}

		if($limit!=0){
			$ret="LIMIT ".(int)$offset.",".(int)$limit;
		}else{
			if($offset!=0){
				 /*
				 * To retrieve all rows from a certain offset up to the end of the result set, you can use some large number for the second parameter
				 * @see http://dev.mysql.com/doc/refman/5.0/en/select.html
				 */
				$ret="LIMIT ".(int)$offset.",18446744073709551615";
			}
		}
		return $ret;
	}

    /*
    * @TODO: replace { and }
    */
	final public function sanitarData($data){
		$data=str_replace(array('[', '%5B', ']', '%5D'), array('&#91;', '&#91;', '&#93;', '&#93;'),htmlspecialchars($data));
		return $data;
	}
}

abstract class extDocLister{
    protected $DocLister;
    protected $_cfg=array();

    abstract protected function run();

    final public function init($DocLister){
        $flag=false;
        if($DocLister instanceof DocLister){
            $this->DocLister=$DocLister;
            $this->checkParam(func_get_args());
            $flag=$this->run();
        }
        return $flag;
    }
    
    final protected function checkParam($args){
        if(isset($args[1])){
            $this->_cfg=$args[1];
        }
    }

    final protected function getCFGDef($name,$def){
		return isset($this->_cfg[$name])?$this->_cfg[$name]:$def;
	}
}
?>