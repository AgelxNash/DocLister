<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

require_once 'content.filter.php';
/**
 * Filters DocLister results using a custom filter implementation of the xrecipe plugin
 * @author kabachello
 *
 */
class xrecipe_DL_filter extends content_DL_filter{
	
	function parseFilter($filter) {
		global $xrecipe;
		if (!$this->filters = $xrecipe->searcher->get_filters()) return false;
		return true;
	}
	
	function get_where(){
		global $xrecipe;
		if (!$this->filters) return '';
		foreach ($xrecipe->searcher->get_filters() as $join => $fields){
			foreach ($fields as $field => $values){
				foreach ($values as $op => $values){
					foreach ($values as $val){
						$wheres[] = $this->build_sql_where($join, $field, $op, $val);
					}
				}
			}
		}
		return is_array($wheres) ? implode(' AND ', $wheres) : '';
	}
	
	function get_join(){
		global $xrecipe;
		if (!$this->filters) return '';
		foreach ($xrecipe->searcher->get_filters() as $join => $fields){
			$joins[] = 'LEFT JOIN xrecipe_' . $join . ' ' . $join . ' ON ' . content_DL_filter::TableAlias . '.id = ' . $join . '.document_id';
		}
		
		if (!empty($joins)){
			return implode(' ', $joins);
		}
		
		return '';
	}
}
?>