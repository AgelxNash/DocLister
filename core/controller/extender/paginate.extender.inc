<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Agel_Nash
 * Date: 19.12.12
 * Time: 15:01
 * To change this template use File | Settings | File Templates.
 */
 
class paginateDocLister extends extDocLister{
    private $_pages=array();

    protected function run(){
        if(($paginate=$this->DocLister->getCFGDef('paginate',''))==''){
            $this->DocLister->setConfig(array('start'=>0));
        }
        $this->DocLister->toPlaceholders(10,1,"count",$this->DocLister->getCFGDef('dittoID',''));
        $pages=0;
        
		switch($paginate){
			case 'pages':{
				if($this->DocLister->getCFGDef('id','')!=''){ $DLid = $this->DocLister->getCFGDef('id','')."_"; }
				$start=(isset($_GET[$DLid.'page']) && intval($_GET[$DLid.'page'])>1) ? (intval($_GET[$DLid.'page'])-1): 0;
				$this->DocLister->setConfig(array('start'=>$start*$this->DocLister->getCFGDef('display',0)));
				$this->_pages['current']=$start+1;
                $pages=$this->getListPages($paginate,'total');
                $this->DocLister->toPlaceholders($this->renderPages("pages"),1,"pages");
				break;
			}
			case 'ditto':{
					/*if($id!=''){ $dittoID = $dittoID."_"; }
					$start=isset($_GET[$dittoID.'start']) ? intval($_GET[$dittoID.'start']): 0;
					$this->setConfig(array('start'=>$start));// @TODO
					$this->_pages['current']=1;
					$pages=$this->getListPages($paginate,'total');
					$this->toPlaceholders($this->renderPages("ditto",$dittoID),1,"pages");*/
					break;
				}
			default:{
			}
		}

        $this->DocLister->toPlaceholders(($this->_pages['current']==1 ? 1 : 0),1,"isstart"); //[+isstart+] – Совпадает ли текущая страница с первой
        $this->DocLister->toPlaceholders(($this->_pages['current']==$pages ? 1 : 0),1,"isstop"); //[+isstop+] – Совпадает ли текущая страница с последней
        $this->DocLister->toPlaceholders($pages,1,"totalPages"); // [+totalPages+] - всего страниц.
		return $pages;
	}


	protected function getListPages($paginate,$name=''){
		$this->_pages['current'] = isset($this->_pages['current']) ? $this->_pages['current'] : 1;
		if($paginate==''){
			$this->_pages['total']=1;
		}else{
            $tmp=$this->DocLister->getChildrenCount(); //расчет числа страниц
            //@TODO: пока этот плейсхолдер устанавливается если включена пагинация
            $this->DocLister->toPlaceholders($tmp,1,"count"); // [+count+] - всего документов
            $display=$this->DocLister->getCFGDef('display',0);
			$this->_pages['total']=ceil($tmp/$display);
		}
        return ($name!='' && isset($this->_pages[$name])) ? $this->_pages[$name] : $this->_pages;
	}
	private function renderPages($mode=''){
		/*
		* @TODO: оформление списка страниц
		* @TODO: добавить параметр сколько страниц отображать в пагинаторе
		*/
		$out='';

		$key = ($this->DocLister->getCFGDef('id','')!='') ? ($this->DocLister->getCFGDef('id','')."_") : "";

		switch ($mode){
			case 'ditto':{
				/*
				* @TODO: пагинация такая же как в ditto (в $_GET указывается число прупускаемых документов)
				*/
				$out='a';
				break;
			}
			default:{
				/*
				* @TODO Переписать пагинатор Digg в соответствии со стандартом PHP 5.4
				*
				* @see http://mis-algoritmos.com/2007/05/27/digg-style-pagination-class
				*/
                $out='';
                $dir=$this->DocLister->getCFGDef('snippetFolder',dirname(__FILE__));
                if(file_exists($dir."/lib/pagination.class.php")){
                    include_once($dir."/lib/pagination.class.php");
                    if(class_exists("pagination",false) && $this->_pages['total']>1){
							$p = new pagination;
							$p->nextT = $this->DocLister->getCFGDef('TplNextP','<a href="[+link+]">'.$this->DocLister->getMsg('paginate.next','Next').' ></a>');

							$p->prevT = $this->DocLister->getCFGDef('TplPrevP','<a href="[+link+]">< '.$this->DocLister->getMsg('paginate.prev','Prev').'</a>');
							$p->numberT = $this->DocLister->getCFGDef('TplPage','<a href="[+link+]" class="page">[+num+]</a>');
							$p->currentT = $this->DocLister->getCFGDef('TplCurrentPage','<b class="current">[+num+]</b>');
							$p->prevI = '';
							$p->mainTpl=$this->DocLister->getCFGDef('TplWrapPaginate','<div class="[+class+]">[+wrap+]</div>');
							$p->Items($this->_pages['total']);
							$p->limit(1); //отображать страниц в списке
							$p->target($this->DocLister->getUrl()); //url
							$p->currentPage($this->_pages['current']);
							$p->parameterName($key."page");
							$p->changeClass($this->DocLister->getCFGDef("PaginateClass","paginate"));
							$out=$p->getOutput();
                    }
                }
			}
		}
		return $out;
	}
}