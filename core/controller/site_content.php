<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Agel_Nash
 * Date: 19.12.12
 * Time: 12:24
 * To change this template use File | Settings | File Templates.
 */
 
class site_contentDocLister extends DocLister{
    private $tag=array();
	/*
     * @absctract
     */
	public function getUrl($id=0){
		/*
		* @TODO: ссылки могут содержать дополнительные $_GET параметры при использовании пагинации с фильтрами в URL
		*/
        $id=$id>0?$id:$this->modx->documentIdentifier;
        $link = ($this->extender['request'] instanceof requestDocLister) ? $this->extender['request']->getLink() : "";
		$tag=$this->checkTag();
		if($tag!=false && is_array($tag) && $tag['mode']=='get'){
			$link.="&tag=".urlencode($tag['tag']);
		}
		return $this->modx->makeUrl($id, '', $link, 'full');
	}
     /*
     * @absctract
     */
	public function getDocs($tvlist=''){
        if($this->extender['paginate'] instanceof paginateDocLister){
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
        return $this->_docs;
	}

     /*
     * @absctract
     */
	public function render($tpl=''){
		$out='';
		if($tpl==''){
			$tpl=$this->getCFGDef('tpl','');
		}
		if($tpl!=''){
			$locale=$this->getCFGDef('locale','');
			if($locale!=''){
				setlocale(LC_ALL, $locale);
			}
			$date=$this->getCFGDef('dateSource','createdon');

            $this->toPlaceholders(count($this->_docs),1,"display"); // [+display+] - сколько показано на странице.

            $i=0;
            $sysPlh=$this->renameKeyArr($this->_plh,$this->getCFGDef("sysKey","dl"));
			$noneTPL=$this->getCFGDef("noneTPL","");
			if(count($this->_docs)==0 && $noneTPL!=''){
				$out=$this->modx->parseChunk($noneTPL,$sysPlh,"[+","+]");
			}else{
				foreach($this->_docs as $item){
					if($this->extender['summary'] instanceof summaryDocLister){
						if(mb_strlen($item['introtext'], 'UTF-8') > 0){
							$item['summary']=$item['introtext'];
						}else{
						   $item['summary']= $this->extender['summary']->init($this,array("content"=>$item['content'],"summary"=>$this->getCFGDef("summary","")));
						}
					}
					
					$item=array_merge($item,$sysPlh); //Внутри чанка доступны все плейсхолдеры установленные через $modx->toPlaceholders с префиксом как у ditto, так и с префиксом sysKey
					$item['title'] = ($item['menutitle']=='' ? $item['pagetitle'] : $item['menutitle']);
					$item['iteration']=$i; //[+iteration+] порядковый номер элемента от нуля.

					$item['url'] = ($item['type']=='reference') ? $item['content'] : $this->getUrl($item['id']);

					$item['author'] = ''; //@TODO: [+author+] – Имя автора. Основано на createdby или editedby

					//@TODO: [+active+] если работаем с другими таблицами, то $item['id'] может и отличаться от $modx->documentIdentifier
					$item['active'] = ($this->modx->documentIdentifier == $item['id']) ? 'active' : '';  //[+active+] - 0 или 1 если $modx->documentIdentifer совпадает с ID текущего элемента
					$item['date']=(isset($item[$date]) && $date!='createdon' && $item[$date]!=0 && $item[$date]==(int)$item[$date]) ? $item[$date] : $item['createdon'];
					$item['date']=strftime($this->getCFGDef('dateFormat','%d.%b.%y %H:%M'),$item['date']+$this->modx->config['server_offset_time']);
					$tmp=$this->modx->parseChunk($tpl,$item,"[+","+]");
					if($this->getCFGDef('contentPlaceholder',0)!==0){
						$this->toPlaceholders($tmp,1,"item[".$i."]"); // [+item[x]+] – Сформированный вывод индивидуального документа
					}
					$out.=$tmp;
					$i++;
				}
			}
            $ownerTPL=$this->getCFGDef("ownerTPL","");
                          // echo $this->modx->getChunk($ownerTPL);
            if($ownerTPL!=''){
                $out=$this->modx->parseChunk($ownerTPL,array($this->getCFGDef("sysKey","dl").".wrap"=>$out),"[+","+]");
            }
		}else{
			$out='none TPL';
		}

		/*
		* @TODO: [+filter+] - строка фильтрации (для подстановки в URL)
		*/

		return $this->toPlaceholders($out);
	}

    protected function getTVList($IDs,$tvlist){
		$tv=$this->getTVid($tvlist);
		$tvId=array_keys($tv);
		$sql=$this->modx->db->query("SELECT tmplvarid,value,contentid FROM ".$this->modx->getFullTableName("site_tmplvar_contentvalues")." WHERE contentid IN(".$this->sanitarIn($IDs).") AND tmplvarid IN(".implode(",",$tvId).")");
		$sql=$this->modx->db->makeArray($sql);
		$out=array();
		$tvDef=$this->loadTVDefault($tvId);
		$TVkeys=array_keys($tvDef);

		foreach($sql as $item){
			$out[$item['contentid']]['tv.'.$tv[$item['tmplvarid']]]=$item['value'];
		}
        $renderTV=$this->getListRenderTV();
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
            $sql=$this->modx->db->query("SELECT id,name,default_text as value,display,display_params,type FROM ".$this->modx->getFullTableName("site_tmplvars")." WHERE id IN(".implode(",",$tvId).")");
            $sql=$this->modx->db->makeArray($sql);
            $out=array();
            foreach($sql as $item){
                $out['tv.'.$item['name']]=$item;
            }
            return $out;
        }
    protected function getTVid($tvlist){
            $sql=$this->modx->db->query("SELECT id,name FROM ".$this->modx->getFullTableName("site_tmplvars")." WHERE name in (".$this->sanitarIn($tvlist).")");

            $sql=$this->modx->db->makeArray($sql);
            $out=array();
            foreach($sql as $item){
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
		$where=$this->whereTag($where);
		$sql=$this->modx->db->query("SELECT count(c.`id`) as `count` FROM ".$this->modx->getFullTableName("site_content")." as c ".$where['join']." WHERE ".$where['where']." c.parent IN (".$this->sanitarIn($this->IDs).") AND c.deleted=0 AND c.published=1 ".(($this->getCFGDef('showParent','0')) ? "" : "AND c.id NOT IN(".$this->sanitarIn($this->IDs).")"));
		return $this->modx->db->getValue($sql);
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
		
		$sql=$this->modx->db->query("SELECT * FROM ".$this->modx->getFullTableName("site_content")." WHERE ".$where." id IN (".$this->sanitarIn($this->IDs).") AND deleted=0 AND published=1 ORDER BY ".$this->getCFGDef('sortBy','createdon')." ".$this->getCFGDef('order','DESC')." ".$this->LimitSQL($this->getCFGDef('queryLimit',0)));

		$sql=$this->modx->db->makeArray($sql);
		$out=array();
		foreach($sql as $item){
			$out[$item['id']]=$item;
		}
		return $out;
	}

    protected  function getChildernFolder($id){
		/*
		* @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
		* @TODO: 5) Добавить фильтрацию по основным параметрам документа
		*/
		$where=$this->getCFGDef('addWhereFolder','');
		if($where!=''){
			$where.=" AND ";
		}
		
		$sql=$this->modx->db->query("SELECT id FROM ".$this->modx->getFullTableName("site_content")." WHERE ".$where." parent IN (".$this->sanitarIn($id).") AND deleted=0 AND published=1 AND isfolder=1");

		$sql=$this->modx->db->makeArray($sql);
		$out=array();
		foreach($sql as $item){
			$out[]=$item['id'];
		}
		return $out;
	}
	private function getTag(){
		$tags=$this->getCFGDef('tagsData','');
		$this->tag=array();
		if($tags!=''){
			$tmp=explode(":",$tags,2);
			if(count($tmp)==2){
				switch($tmp[0]){
					case 'get':{
						$tag = (isset($_GET[$tmp[1]]) && !is_array($_GET[$tmp[1]]))? $_GET[$tmp[1]] : '';
						break;
					}
					case 'static':
					default:{
						$tag=$tmp[1];
						break;
					}
				}
				$this->tag=array("mode"=>$tmp[0],"tag"=>$tag);
				$this->toPlaceholders($this->sanitarData($tag),1,"tag");
			}
		}
		return $this->checkTag();
	}
	private function checkTag($reconst=false){
		$data=(is_array($this->tag) && count($this->tag)==2 && isset($this->tag['tag']) && $this->tag['tag']!='') ? $this->tag: false;
		if($data===false && $reconst===true){
			$data=$this->getTag();
		}
		return $data;
	}
	private function whereTag($where){
		$join='';
		$tag=$this->checkTag(true);
		if($tag!==false){
			$join="RIGHT JOIN ".$this->modx->getFullTableName('site_content_tags')." as ct on ct.doc_id=c.id 
					RIGHT JOIN ".$this->modx->getFullTableName('tags')." as t on t.id=ct.tag_id";
			$where.= ($where!='' ? "" : " AND ")."t.`name`='".$this->modx->db->escape($tag['tag'])."'".
					(($this->getCFGDef('tagsData','')>0) ? "AND ct.tv_id=".(int)$this->getCFGDef('tagsData','') : "")." AND ";
		}
		$out=array("where"=>$where,"join"=>$join);
		return $out;
	}
	protected  function getChildrenList(){
		/*
		* @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
		* @TODO: 5) Добавить фильтрацию по основным параметрам документа
		*/
		$where=$this->getCFGDef('addWhereList','');
		$join='';
		if($where!=''){
			$where.=" AND ";
		}
		$where=$this->whereTag($where);

		$sql=$this->modx->db->query("
			SELECT c.* FROM ".$this->modx->getFullTableName("site_content")." as c ".$where['join']."
			WHERE ".$where['where']." 
				c.parent IN (".$this->sanitarIn($this->IDs).") 
				AND c.deleted=0 
				AND c.published=1 ".
				(($this->getCFGDef('showParent','0')) ? "" : "AND c.id NOT IN(".$this->sanitarIn($this->IDs).")")."
			ORDER BY ".$this->getCFGDef('sortBy','c.createdon')." ".$this->getCFGDef('order','DESC')." ".
			$this->LimitSQL($this->getCFGDef('queryLimit',0))
		);
/* echo "
			SELECT * FROM ".$this->modx->getFullTableName("site_content")." as c ".$join."
			WHERE ".$where." 
				c.parent IN (".$this->sanitarIn($this->IDs).") 
				AND c.deleted=0 
				AND c.published=1 ".
				(($this->getCFGDef('showParent','0')) ? "" : "AND c.id NOT IN(".$this->sanitarIn($this->IDs).")")."
			ORDER BY ".$this->getCFGDef('sortBy','c.createdon')." ".$this->getCFGDef('order','DESC')." ".
			$this->LimitSQL($this->getCFGDef('queryLimit',0)); */
		$sql=$this->modx->db->makeArray($sql);
		$out=array();
		foreach($sql as $item){
			$out[$item['id']]=$item;
		}
		return $out;
	}
}