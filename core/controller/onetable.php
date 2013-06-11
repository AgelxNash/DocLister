<?php
/**
 * all controller for show info from all table
 *
 * @category controller
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @date 24.05.2013
 * @version 1.0.15
 *
 * @TODO add controller for construct tree from table
 * @TODO custom prepare field before parse chunk
 * @param introField=`` //introtext
 * @param contentField=`description` //content
 * @param idField=`` //id
 * @param table=`` //table name
 */

class onetableDocLister extends DocLister{
    protected $table='site_content';
    protected $idField='id';
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
        $this->table = $this->modx->getFullTableName($this->getCFGDef('table','site_content'));
        $this->idField=$this->getCFGDef('idField','id');

        if($this->checkExtender('paginate')){
            $pages=$this->extender['paginate']->init($this);
        }else{
            $this->setConfig(array('start'=>0));
        }
        $this->_docs = $this->getDocList();

        return $this->_docs;
	}

	public function _render($tpl=''){
		$out='';
		if($tpl==''){
			$tpl=$this->getCFGDef('tpl','');
		}
		if($tpl!=''){
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
                        $introField=$this->getCFGDef("introField","");
						if(isset($item[$introField]) && mb_strlen($item[$introField], 'UTF-8') > 0){
							$item[$this->getCFGDef("sysKey","dl").'.summary']=$item[$introField];
						}else{
                           $contentField=$this->getCFGDef("contentField","");
                            if(isset($item[$contentField])){
						        $item[$this->getCFGDef("sysKey","dl").'.summary']= $this->extender['summary']->init($this,array("content"=>$item[$contentField],"summary"=>$this->getCFGDef("summary","")));
                            }else{
                                $item[$this->getCFGDef("sysKey","dl").'.summary']='';
                            }
						}
					}

					$item=array_merge($item,$sysPlh); //inside the chunks available all placeholders set via $modx->toPlaceholders with prefix id, and with prefix sysKey
					$item[$this->getCFGDef("sysKey","dl").'.iteration']=$i; //[+iteration+] - Number element. Starting from zero

                    $date=$this->getCFGDef('dateSource','pub_date');
					$date=isset($item[$date]) ? $item[$date]+$this->modx->config['server_offset_time'] : '';
                    if($date!='' && $this->getCFGDef('dateFormat','%d.%b.%y %H:%M')!=''){
                        $item[$this->getCFGDef("sysKey","dl").'.date']=strftime($this->getCFGDef('dateFormat','%d.%b.%y %H:%M'),$date);
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

    protected  function getDocList(){
		$where=$this->getCFGDef('addWhereList','');
        if($where!=''){
            $where="WHERE ".$where;
        }
        $limit   = $this->LimitSQL($this->getCFGDef('queryLimit',0));
		$rs=$this->modx->db->query("SELECT * FROM {$this->table} {$where} {$this->SortOrderSQL($this->idField)} {$limit}");

		$rows=$this->modx->db->makeArray($rs);
		$out=array();
		foreach($rows as $item){
			$out[$item[$this->idField]]=$item;
		}
		return $out;
	}

    // @abstract
    public function getChildrenCount(){
        $where=$this->getCFGDef('addWhereList','');
        $fields = "count(c.`{$this->idField}`) as `count`";
        $from   = "{$this->table} as c";
        $rs=$this->modx->db->select($fields, $from, $where);
        return $this->modx->db->getValue($rs);
    }

    public function getChildernFolder($id){
        $where=$this->getCFGDef('addWhereFolder','');
        $rs=$this->modx->db->select($this->idField, $this->table, $where);

        $rows=$this->modx->db->makeArray($rs);
        $out=array();
        foreach($rows as $item){
            $out[]=$item[$this->idField];
        }
        return $out;
    }
}
?>