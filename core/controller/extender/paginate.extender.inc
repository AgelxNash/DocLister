<?php
/**
 * Paginate extender for DocLister
 *
 * @category extender
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @date 24.05.2013
 * @version 1.0.15
 */

class paginate_DL_Extender extends extDocLister{
    private $_pages=array();

    /*
     * Call method
     *
     * @return int total pages
     *
     * @todo paginate ditto mode
     */
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
				$this->DocLister->toPlaceholders($this->_pages['current'],1,"current");
                $this->DocLister->toPlaceholders($this->renderPages("pages"),1,"pages");
				break;
			}
			case 'ditto':{
					/*if($id!=''){ $dittoID = $dittoID."_"; }
					$start=isset($_GET[$dittoID.'start']) ? intval($_GET[$dittoID.'start']): 0;
					$this->setConfig(array('start'=>$start));
					$this->_pages['current']=1;
					$pages=$this->getListPages($paginate,'total');
					$this->toPlaceholders($this->renderPages("ditto",$dittoID),1,"pages");*/
					break;
				}
			default:{
			}
		}

        $this->DocLister->toPlaceholders(($this->_pages['current']==1 ? 1 : 0),1,"isstart"); //[+isstart+] – is start page
        $this->DocLister->toPlaceholders(($this->_pages['current']==$pages ? 1 : 0),1,"isstop"); //[+isstop+] – is end page
        $this->DocLister->toPlaceholders($pages,1,"totalPages"); // [+totalPages+] - total page.
		return $pages;
	}

    /*
     * Get information about pagination (current, total or all in array)
     *
     * @param string $paginate Mode paginate. Empty string if no paginate
     * @param string $name What information is interested
     * @return string|array information about pagination
     *
     * @todo Set always placeholder count document. Not only pagination
     */
	protected function getListPages($paginate,$name=''){
		$this->_pages['current'] = isset($this->_pages['current']) ? $this->_pages['current'] : 1;
		if($paginate==''){
			$this->_pages['total']=1;
		}else{
            $tmp=$this->DocLister->getChildrenCount(); //math count document
            $this->DocLister->toPlaceholders($tmp,1,"count"); //set count placeholder
            $display=$this->DocLister->getCFGDef('display',0);
			$this->_pages['total']=ceil($tmp/$display);
		}
        return ($name!='' && isset($this->_pages[$name])) ? $this->_pages[$name] : $this->_pages;
	}

    /*
     *
     * @todo template list pages
     * @todo add parameter of count pages to display
     * @todo pagintion ditto style (in $_GET array set count offset documents)
     * @todo update Digg pagination for compbility php 5.4
     */
	private function renderPages($mode=''){
		$out='';

		$key = ($this->DocLister->getCFGDef('id','')!='') ? ($this->DocLister->getCFGDef('id','')."_") : "";

		switch ($mode){
			case 'ditto':{
				$out='';
				break;
			}
			default:{
				/*
				* @see http://mis-algoritmos.com/2007/05/27/digg-style-pagination-class
				*/
                $out='';
                $dir=$this->DocLister->getCFGDef('snippetFolder',dirname(dirname(dirname(dirname(__FILE__)))));
                if(file_exists($dir."/lib/pagination.class.php")){
                    include_once($dir."/lib/pagination.class.php");
                    if(class_exists("pagination",false) && $this->_pages['total']>1){
                    						$p = new pagination;
							$p->nextT = $this->DocLister->getChunkByParam('TplNextP','@CODE: <a href="[+link+]">'.$this->DocLister->getMsg('paginate.next','Next').' ></a>');
							$p->prevT = $this->DocLister->getChunkByParam('TplPrevP','@CODE: <a href="[+link+]">< '.$this->DocLister->getMsg('paginate.prev','Prev').'</a>');
							$p->numberT = $this->DocLister->getChunkByParam('TplPage','@CODE: <a href="[+link+]" class="page">[+num+]</a>');
							$p->currentT = $this->DocLister->getChunkByParam('TplCurrentPage','@CODE: <b class="current">[+num+]</b>');
							$p->prevI = '';
							$p->mainTpl=$this->DocLister->getChunkByParam('TplWrapPaginate','@CODE: <div class="[+class+]">[+wrap+]</div>');
							$p->Items($this->_pages['total']);
							$p->limit($this->DocLister->getCFGDef("pageLimit",1)); //show page count
                            $p->adjacents($this->DocLister->getCFGDef("pageAdjacents",4));
							$p->target($this->DocLister->getUrl()); //getUrlPage
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
?>
