<?php
/**
 * site_content controller
 * @see http://modx.im/blog/addons/374.html
 *
 * @category controller
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @date 24.05.2013
 * @version 1.0.15
 *
 * @TODO add parameter showFolder - include document container in result data whithout children document if you set depth parameter.
 * @TODO st placeholder [+dl.title+] if menutitle not empty
 */

class site_contentDocLister extends DocLister{
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
							$item['summary']=$item['introtext'];
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
                    if($i==0){
						$tpl=$this->getCFGDef('tplFirst',$tpl);
						$class[]='first';
					}
                    if($i==count($this->_docs)){
						$tpl=$this->getCFGDef('tplLast',$tpl);
						$class[]='last';
					}
                    if($this->modx->documentIdentifier == $item['id']){
						$tpl=$this->getCFGDef('tplCurrent',$tpl);
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
            if(($this->getCFGDef("noneWrapOuter","1") && count($this->_docs)==0) || count($this->_docs)>0){
				$ownerTPL=$this->getCFGDef("ownerTPL","");
				// echo $this->modx->getChunk($ownerTPL);
				if($ownerTPL!=''){
					$out=$this->parseChunk($ownerTPL,array($this->getCFGDef("sysKey","dl").".wrap"=>$out));
				}
			}
		}else{
			$out='none TPL';
		}

		return $this->toPlaceholders($out);
	}
	
	public function getJSON($data,$fields,$array=array()){
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
		$where=$this->getCFGDef('addWhereList','');
		if($where!=''){
			$where.=" AND ";
		}
		$tbl_site_content = $this->modx->getFullTableName('site_content');
		$sanitarInIDs = $this->sanitarIn($this->IDs);
		$getCFGDef = $this->getCFGDef('showParent','0') ? '' : "AND c.id NOT IN({$sanitarInIDs})";
		$fields = 'count(c.`id`) as `count`';
		$from   = "{$tbl_site_content} as c";
		$where  = "{$where} c.parent IN ({$sanitarInIDs}) AND c.deleted=0 AND c.published=1 {$getCFGDef}";
		$rs=$this->modx->db->select($fields, $from, $where);
		return $this->modx->db->getValue($rs);
	}

    protected  function getDocList(){
		/*
		* @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
		* @TODO: 5) Добавить фильтрацию по основным параметрам документа
		*/
		$where=$this->getCFGDef('addWhereList','');
		if($where!=''){
			$where.=" AND ";
		}
		
		$tbl_site_content = $this->modx->getFullTableName('site_content');
		$sanitarInIDs = $this->sanitarIn($this->IDs);
		$where   = "WHERE {$where} id IN ({$sanitarInIDs}) AND deleted=0 AND published=1";
		$limit   = $this->LimitSQL($this->getCFGDef('queryLimit',0));
		$rs=$this->modx->db->query("SELECT * FROM {$tbl_site_content} {$where} {$this->SortOrderSQL("if(pub_date=0,createdon,pub_date)")} {$limit}");
		
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

		$where=$this->getCFGDef('addWhereList','');
		if($where!=''){
			$where.=" AND ";
		}

		$sql=$this->modx->db->query("
			SELECT c.* FROM ".$this->modx->getFullTableName('site_content')." as c
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
}
?>