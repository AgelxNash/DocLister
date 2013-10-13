<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

require_once 'content.filter.php';
/**
 *
 * Filters DocLister by TVs using QUID-plugin tables.
 * @author webber <webber12@yandex.ru>
 * example [[DocLister &QFTable=`quidTableName` &filters=`quid:TVname:operator:value`]]
 * use operands form DocLister Filters
 *
 */
class quid_DL_filter extends content_DL_filter{


	public function get_join(){
		return '';
	}
	
	public function get_where(){
		// just using this for making additional conditions to addWhereList
		$QFTable=$this->DocLister->getCFGDef('QFTable','');
		if($QFTable!=''){
		    $addWhereList=$this->DocLister->getCFGDef('addWhereList','');
			if(strpos($addWhereList,$QFTable)!==false){
		        $addWhereList=substr($addWhereList,0,-1);//чтобы убрать закрывающую скобку для всех условий, кроме последнего
			}
		    $addWhereList.=($this->totalFilters==1?($addWhereList==''?'':' AND ').'c.id IN (SELECT '.$QFTable.'.cid FROM '.$QFTable.' WHERE c.id='.$QFTable.'.cid AND ':'');
		    $addWhereList.=($this->totalFilters==1?'':' AND ').$this->build_sql_where($QFTable, $this->field, $this->operator, $this->value).')';
		    $this->DocLister->setConfig(array('addWhereList'=>$addWhereList));
		}
		return '';
	}
}
?>