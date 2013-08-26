<?php
/**
 * site_content_filters controller for DocLister
 * 
 * @category controller
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author kabachello <kabachnik@hotmail.com>
 * @date 16.08.2013
 * @version 1.1.0
 * 
 * Adds flexible filters to DocLister. Filter types can be easily added using filter extenders (see filter subfolder).
 * To use filtering via snippet call add the "filters" parameter to the DocLister call like " ... &filters=`tv:tags:like:your_tag`
 * All filters adhere to the following syntax:
 * <logic_operator>(<filter_type>:<field>:<comparator>:<value>, <filter_type>:<field>:<comparator>:<value>, ...)
 * <logic_operator> - AND, OR, etc. - applied to a comma separated list of filters enclosed in parenthesis
 * <filter_type> - name of the filter extender to use (tv, content, etc.)
 * <field> - the field to filter (must be supported by the respecitve filter_type)
 * <comparator> - comparison operator (must be supported by the respecitve filter_type) - is, gt, lt, like, etc.
 * <value> - value to compare with
 * 
 * Examples:
 * AND(content:template:eq:5; tv:tags:like:my tag) - fetch all documents with template id 5 and the words "my tag" in the TV named "tags"
 *
 */

class site_content_filtersDocLister extends DocLister{
	private $_filters;
	private $_logic_ops = array('AND'=>' AND ', 'OR' => ' OR '); // logic operators currently supported
	
    /*
     * @absctract
	 * @todo link maybe include other GET parameter with use pagination. For example - filter
     */
	public function getUrl($id=0){
        $id=$id>0?$id:$this->modx->documentIdentifier;
        $link = $this->checkExtender('request') ? $this->extender['request']->getLink() : "";
        $url = ($id==$this->modx->config['site_start']) ? $this->modx->config['site_url'] . ($link!='' ? "?{$link}" : "") : $this->modx->makeUrl($id, '', $link, 'full');
        return $url;
	}
     /*
     * @absctract
     */
	public function getDocs($tvlist=''){
		// prepare filters
		$this->_filters = $this->getFilters($this->getCFGDef('filters', ''));
		
        if($this->checkExtender('paginate')){
            $pages=$this->extender['paginate']->init($this);
        }else{
            $this->setConfig(array('start'=>0));
        }
        
        $type = $this->getCFGDef('idType','parents');
        $this->_docs = ($type=='parents') ? $this->getChildrenList() : $this->getDocList();

        if($tvlist==''){
            $tvlist=$this->getCFGDef('tvList','');
	    }
	    if($tvlist!='' && $this->checkIDs()){

		    $tv=$this->getTVList(array_keys($this->_docs),$tvlist);
		    foreach($tv as $docID=>$TVitem){
				$this->_docs[$docID]=array_merge($this->_docs[$docID],$TVitem);
		    }
        }
        if(1==$this->getCFGDef('tree','0')){
            $this->treeBuild('id','parent');
        }
        return $this->_docs;
	}


    /*
     * @todo set correct active placeholder if you work with other table. Because $item['id'] can differ of $modx->documentIdentifier (for other controller)
     * @todo set author placeholder (author name). Get id from Createdby OR editedby AND get info from extender user
     * @todo set filter placeholder with string filtering for insert URL
     */
        public function _render($tpl=''){
            $out='';
		if($tpl==''){
			$tpl=$this->getCFGDef('tpl','@CODE:<a href="[+url+]">[+pagetitle+]</a><br />');
		}
		if($tpl!=''){
			$date=$this->getCFGDef('dateSource','pub_date');
            $this->toPlaceholders(count($this->_docs),1,"display"); // [+display+] - сколько показано на странице.

            $i=1;
            $sysPlh=$this->renameKeyArr($this->_plh,$this->getCFGDef("sysKey","dl"));
			$noneTPL=$this->getCFGDef("noneTPL","");
			if(count($this->_docs)==0 && $noneTPL!=''){
				$out=$this->parseChunk($noneTPL,$sysPlh);
			}else{
                if($this->checkExtender('user')){
                    $this->extender['user']->init($this,array('fields'=>$this->getCFGDef("userFields","")));
                }
				foreach($this->_docs as $item){
                    if($this->checkExtender('user')){
                        $item=$this->extender['user']->setUserData($item);  //[+user.id.createdby+], [+user.fullname.publishedby+], [+dl.user.publishedby+]....
                    }

					if($this->checkExtender('summary')){
						if(mb_strlen($item['introtext'], 'UTF-8') > 0){
							// MOD truncate introtext too by PATRIOT
							// $item['summary']=$item['introtext'];
							$item['summary']= $this->extender['summary']->init($this,array("content"=>$item['introtext'],"summary"=>$this->getCFGDef("summary","")));
						}else{
						   $item['summary']= $this->extender['summary']->init($this,array("content"=>$item['content'],"summary"=>$this->getCFGDef("summary","")));
						}
					}
					
					$item=array_merge($item,$sysPlh); //inside the chunks available all placeholders set via $modx->toPlaceholders with prefix id, and with prefix sysKey
					$item['title'] = ($item['menutitle']=='' ? $item['pagetitle'] : $item['menutitle']);
					$item['iteration']=$i; //[+iteration+] - Number element. Starting from zero

					$item['url'] = ($item['type']=='reference') ? $item['content'] : $this->getUrl($item['id']);

					$item['date']=(isset($item[$date]) && $date!='createdon' && $item[$date]!=0 && $item[$date]==(int)$item[$date]) ? $item[$date] : $item['createdon'];
                    $item['date']=$item['date']+$this->modx->config['server_offset_time'];
                    if($this->getCFGDef('dateFormat','%d.%b.%y %H:%M')!=''){
                        $item['date']=strftime($this->getCFGDef('dateFormat','%d.%b.%y %H:%M'),$item['date']);
                    }

                    $class=array();
                    $class[] = ($i%2==0) ? 'odd' : 'even';
                    if($i==0) $class[]='first';
                    if($i==count($this->_docs)) $class[]='last';
                    if($this->modx->documentIdentifier == $item['id']){
                        $item[$this->getCFGDef("sysKey","dl").'.active']=1;  //[+active+] - 1 if $modx->documentIdentifer equal ID this element
                        $class[]='current';
                    }else{
                        $item['active']=0;
                    }
                    $class=implode(" ",$class);
                    $item[$this->getCFGDef("sysKey","dl").'.class']=$class;

                    $tmp=$this->parseChunk($tpl,$item);

                    if($this->getCFGDef('contentPlaceholder',0)!==0){
						$this->toPlaceholders($tmp,1,"item[".$i."]"); // [+item[x]+] – individual placeholder for each iteration documents on this page
					}
					$out.=$tmp;
					$i++;
				}
			}
            $ownerTPL=$this->getCFGDef("ownerTPL","");
            // echo $this->modx->getChunk($ownerTPL);
            if($ownerTPL!=''){
                $out=$this->parseChunk($ownerTPL,array($this->getCFGDef("sysKey","dl").".wrap"=>$out));
            }
		}else{
			$out='none TPL';
		}
		
		return $this->toPlaceholders($out);
	}
	
	public function getJSON($data,$fields){
        $out=array();
		$fields = is_array($fields) ? $fields : explode(",",$fields);
		$date=$this->getCFGDef('dateSource','pub_date');
		
		foreach($data as $num=>$item){
			switch(true){
				case ((array('1')==$fields || in_array('summary',$fields)) && $this->checkExtender('summary')):{
					$out[$num]['summary'] = (mb_strlen($this->_docs[$num]['introtext'], 'UTF-8') > 0) ? $this->_docs[$num]['introtext'] : $this->extender['summary']->init($this,array("content"=>$this->_docs[$num]['content'],"summary"=>$this->getCFGDef("summary","")));
					//without break
				}
				case (array('1')==$fields || in_array('date',$fields)):{
					$tmp = (isset($this->_docs[$num][$date]) && $date!='createdon' && $this->_docs[$num][$date]!=0 && $this->_docs[$num][$date]==(int)$this->_docs[$num][$date]) ? $this->_docs[$num][$date] : $this->_docs[$num]['createdon'];
					$out[$num]['date']=strftime($this->getCFGDef('dateFormat','%d.%b.%y %H:%M'),$tmp + $this->modx->config['server_offset_time']);
					//without break
				}
			}
		}
		
        return parent::getJSON($data,$fields,$out);
    }
	
    protected function getTVList($IDs,$tvlist){
		$tv=$this->getTVid($tvlist);
		$tvId=array_keys($tv);
		$tbl_site_tmplvar_contentvalues = $this->modx->getFullTableName('site_tmplvar_contentvalues');
		$sanitarInIDs = $this->sanitarIn($IDs);
		$implodeTvId = implode(',',$tvId);
		$where = "contentid IN({$sanitarInIDs}) AND tmplvarid IN({$implodeTvId})";
		$rs=$this->modx->db->select('tmplvarid,value,contentid', $tbl_site_tmplvar_contentvalues, $where);
		$rows=$this->modx->db->makeArray($rs);
		$out=array();
		foreach($rows as $item){
			$out[$item['contentid']]['tv.'.$tv[$item['tmplvarid']]]=$item['value'];
		}
		
		$renderTV=$this->getListRenderTV();
		$tvDef=$this->loadTVDefault($tvId);
		$TVkeys=array_keys($tvDef);
		foreach($out as $itemid=>$item){
			foreach($TVkeys as $name){
			    if(!isset($out[$itemid][$name])){
				    $out[$itemid][$name]=$tvDef[$name]['value'];
				}
                if(in_array($name,$renderTV) || $renderTV==array("*")){
                    $out[$itemid][$name]=$this->renderTV($itemid,$name,$out[$itemid][$name],$tvDef[$name]);
                }
            }
        }
		return $out;
	}
    protected function getListRenderTV(){
            $tmp=$this->getCFGDef('renderTV','');
            if($tmp!='' && $tmp!='*'){
                $tmp=explode(",",$tmp);
                if(in_array("*",$tmp)){
                    $tmp=array("*");
                }else{
                    $out=array_unique($tmp);
                    $tmp=array();
                    foreach($out as $item){
                        $tmp[]="tv.".$item;
                    }
                }
            }else{
                $tmp=array($tmp);
            }
            return $tmp;
        }
    protected function renderTV($iddoc,$tvname,$tvval,$param){
            include_once MODX_MANAGER_PATH . "includes/tmplvars.format.inc.php";
            include_once MODX_MANAGER_PATH . "includes/tmplvars.commands.inc.php";
            return getTVDisplayFormat($tvname, $tvval, $param['display'], $param['display_params'], $param['type'], $iddoc, '');
        }
    protected function loadTVDefault($tvId){
            $tbl_site_tmplvars = $this->modx->getFullTableName('site_tmplvars');
            $fields = 'id,name,default_text as value,display,display_params,type';
            $implodeTvId = implode(',', $tvId);
            $rs=$this->modx->db->select($fields, $tbl_site_tmplvars, "id IN({$implodeTvId})");
            $rows=$this->modx->db->makeArray($rs);
            $out=array();
            foreach($rows as $item){
                $out['tv.'.$item['name']]=$item;
            }
            return $out;
        }
    protected function getTVid($tvlist){
            $tbl_site_tmplvars = $this->modx->getFullTableName('site_tmplvars');
            $sanitarInTvlist = $this->sanitarIn($tvlist);
            $rs=$this->modx->db->select('id,name', $tbl_site_tmplvars, "name in ({$sanitarInTvlist})");
            $rows=$this->modx->db->makeArray($rs);
            $out=array();
            foreach($rows as $item){
                $out[$item['id']]=$item['name'];
            }
            return $out;
        }

    /*
     * document
     */

    // @abstract
     public function getChildrenCount(){
		// add the parameter addWhereList
		$where = $this->getCFGDef('addWhereList','');
		// add the filters
		$where = ($where && $this->_filters['where'] ? $where . ' AND ' : '') . $this->_filters['where'];
		
		if($where!=''){
			$where.=" AND ";
		}
		$tbl_site_content = $this->modx->getFullTableName('site_content');
		$sanitarInIDs = $this->sanitarIn($this->IDs);
		$getCFGDef = $this->getCFGDef('showParent','0') ? '' : "AND c.id NOT IN({$sanitarInIDs})";
		$fields = 'count(c.`id`) as `count`';
		$from   = "{$tbl_site_content} as c " . $this->_filters['join'];
		$where  = "{$where} c.parent IN ({$sanitarInIDs}) AND c.deleted=0 AND c.published=1 {$getCFGDef}";
		$rs=$this->modx->db->select($fields, $from, $where);
		return $this->modx->db->getValue($rs);
	}

    protected function getDocList(){
		/*
		* @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
		* @TODO: 5) Добавить фильтрацию по основным параметрам документа
		*/
		// add the parameter addWhereList
		$where = $this->getCFGDef('addWhereList','');
		// add the filters
		$where =($where ? $where . ' AND ' : '') . $this->_filters['where'];

		if($where!=''){
			$where.=" AND ";
		}
		
		$tbl_site_content = $this->modx->getFullTableName('site_content');
		$sanitarInIDs = $this->sanitarIn($this->IDs);
		$where   = "WHERE {$where} sc.id IN ({$sanitarInIDs}) AND sc.deleted=0 AND sc.published=1";
		$limit   = $this->LimitSQL($this->getCFGDef('queryLimit',0));
		$rs=$this->modx->db->query("SELECT * FROM {$tbl_site_content} c " . $this->_filters['join'] . " {$where} {$this->SortOrderSQL("if(pub_date=0,createdon,pub_date)")} {$limit}");

		$rows=$this->modx->db->makeArray($rs);
		$out=array();
		foreach($rows as $item){
			$out[$item['id']]=$item;
		}
		return $out;
	}

    public function getChildernFolder($id){
		/*
		* @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
		* @TODO: 5) Добавить фильтрацию по основным параметрам документа
		*/
		$where=$this->getCFGDef('addWhereFolder','');
		if($where!=''){
			$where.=" AND ";
		}
		
		$tbl_site_content = $this->modx->getFullTableName('site_content');
		$sanitarInIDs = $this->sanitarIn($id);
		$where = "{$where} parent IN ({$sanitarInIDs}) AND deleted=0 AND published=1 AND isfolder=1";
		$rs=$this->modx->db->select('id', $tbl_site_content, $where);

		$rows=$this->modx->db->makeArray($rs);
		$out=array();
		foreach($rows as $item){
			$out[]=$item['id'];
		}
		return $out;
	}

	/*
	* @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
	* @TODO: 5) Добавить фильтрацию по основным параметрам документа
	*/
	protected  function getChildrenList(){
		// add the parameter addWhereList
		$where = $this->getCFGDef('addWhereList','');
		// add the filters
		$where =($where ? $where . ' AND ' : '') . $this->_filters['where'];

		if($where!=''){
			$where.=" AND ";
		}
		
		$sql=$this->modx->db->query("
			SELECT DISTINCT c.* FROM ".$this->modx->getFullTableName('site_content')." as c " . $this->_filters['join'] . "
			WHERE ".$where."
				c.parent IN (".$this->sanitarIn($this->IDs).") 
				AND c.deleted=0 
				AND c.published=1 ".
				(($this->getCFGDef('showParent','0')) ? "" : "AND c.id NOT IN(".$this->sanitarIn($this->IDs).") ").
			$this->SortOrderSQL('if(pub_date=0,createdon,pub_date)')." ".
			$this->LimitSQL($this->getCFGDef('queryLimit',0))
		);
		$rows=$this->modx->db->makeArray($sql);
		$out=array();
		foreach($rows as $item){
			$out[$item['id']]=$item;
		}
		return $out;
	}
	
	/**
	 * OR(AND(filter:field:operator:value;filter2:field:oerpator:value);(...)), etc.
	 * @param string $filter_string
	 */
	protected function getFilters($filter_string){
        // the filter parameter tells us, which filters can be used in this query
		$filter_string = trim($filter_string);
		if (!$filter_string) return;

		$logic_op_found = false;
		foreach ($this->_logic_ops as $op => $sql){
			if (strpos($filter_string, $op) === 0){
				$logic_op_found = true;
				$subfilters = substr($filter_string, strlen($op)+1, -1);
				$subfilters = explode(';', $subfilters);
				foreach ($subfilters as $subfilter){
					$subfilter = $this->getFilters(trim($subfilter));
					if (!$subfilter) continue;
					if ($subfilter['join']) $joins[] = $subfilter['join'];
					if ($subfilter['where']) $wheres[] = $subfilter['where'];
				}
				$output['join'] = !empty($joins) ? implode(' ', $joins) : '';
				$output['where'] = !empty($wheres) ? '(' . implode($sql, $wheres) . ')' : '';
			}
		}
		
		if (!$logic_op_found) {
			$filter = $this->loadFilter($filter_string);
			if (!$filter) {
                $this->modx->logEvent(0, 2, 'Error while loading DocLister filter "' . $filter_string . '": check syntax!');
				return;
			}
			$output['join'] = $filter->get_join();
			$output['where'] = $filter->get_where();
		}
		
		return $output;
	}
	
	protected function loadFilter($filter){
		$fltr_params = explode(':', $filter);
		$fltr = $fltr_params[0];
		// check if the filter is implemented
		if (file_exists(dirname(__FILE__) . '/filter/' . $fltr . '.filter.php')){
			require_once dirname(__FILE__) . '/filter/' . $fltr . '.filter.php';
			$fltr_class = $fltr . '_DL_filter';
			$fltr_obj = new $fltr_class();
			$fltr_obj->init($this, $filter);
			return $fltr_obj;
		} else {
			return false;
		}
	}
}

abstract class filterDocLister{
    /*
     * @var DocLister $DocLister
     * @access protected
     */
	protected $DocLister;
    protected $modx;
	protected $main_table_alias;
	
	function __construct($main_table_alias="c"){
		$this->main_table_alias = $main_table_alias;
	}
	
	final public function init($DocLister, $filter){
		$flag=false;
		if($DocLister instanceof DocLister){
            $this->DocLister=$DocLister;
            $this->modx = $this->DocLister->getMODX();
			$flag = $this->parseFilter($filter);
		}
		return $flag;
	}
	
	abstract public function get_where();
	
	abstract public function get_join();
	
	protected function parseFilter($filter){
        // first parse the give filter string
        $parsed = explode(':', $filter);
        $this->field = $parsed[1];
        $this->operator = $parsed[2];
        $this->value = $parsed[3];
        // exit if something is wrong
        if (empty($this->field) || empty($this->operator) || is_null($this->value)) return false;

        return true;
    }
	
	function set_main_table_alias($value){
		$this->main_table_alias = $value;
	}
}
?>