<?php
require_once 'content.filter.php';
/**
 * Filters DocLister results by value of a given MODx Template Variables (TVs).
 * @author kabachello
 *
 */
class tv_DL_filter extends content_DL_filter{
	private $tv_table_alias;
	private $tv_id;
	
	function parseFilter($filter) {
		global $modx;
		$return = false;
		
		// use the parsing mechanism of the content filter for the start
		if (!parent::parseFilter($filter)) return false;
		
		// now add some variables specific to the TV-filter
		// get the id of the TV
		$tvid = $modx->db->select('id', $modx->getFullTableName('site_tmplvars'), 'name = "' . $this->field . '"');
		$tvid = $modx->db->makeArray($tvid);
		$this->tv_id = intval($tvid[0]['id']);
		if (!$this->tv_id) $modx->logEvent(0, 2, 'DocLister filtering by template variable "' . $this->field . '" failed. TV not found!');
		
		// create the alias for the join
		// FIXME this only works if the TV is used in exactly one filter. Multiple Filters on one TV would need different table_aliases.
		$this->tv_table_alias = 'dltv_' . $this->field;
		return true;
	}
	
	function get_where(){
		return $this->build_sql_where($this->tv_table_alias, 'value', $this->operator, $this->value);
	}
	
	function get_join(){
		global $modx;
		$join = 'LEFT JOIN ' . $modx->getFullTableName('site_tmplvar_contentvalues') . ' ' . $this->tv_table_alias 
		. ' ON ' . $this->tv_table_alias . '.contentid = ' . $this->main_table_alias . '.id AND ' . $this->tv_table_alias . '.tmplvarid = ' . $this->tv_id;
		return $join;
	}
}
?>