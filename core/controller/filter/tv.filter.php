<?php
/**
 * Filters DocLister results by value of a given MODx Template Variables (TVs).
 * Supported comparison operators:
 * - "="
 * - "IN"
 * - "LIKE" also "%LIKE" or "LIKE%"
 * @author aka
 * @param filter_tv tvname:operator:value
 *
 */
class tv_DL_filter extends filterDocLister{
	private $tv_table_alias;
	private $tv_id;
	private $tv_name;
	private $operator;
	private $value;
	
	function parseFilter($filter) {
		global $modx;
		$return = false;
		
		// first parse the give filter string
		$parsed = explode(':', $filter);
		$this->tv_name = $parsed[1];
		$this->operator = $parsed[2];
		$this->value = $parsed[3];
		// exit if something is wrong
		if (empty($this->tv_name) || empty($this->operator) || empty($this->value)) return false;
		
		// get the id of the TV
		$tvid = $modx->db->select('id', $modx->getFullTableName('site_tmplvars'), 'name = "' . $this->tv_name . '"');
		$tvid = $modx->db->makeArray($tvid);
		$this->tv_id = intval($tvid[0]['id']);
		if (!$this->tv_id) $modx->logEvent(0, 2, 'DocLister filtering by template variable "' . $this->tv_name . '" failed. TV not found!');
		
		// create the alias for the join
		$this->tv_table_alias = 'dltv_' . $this->tv_name;
		return true;
	}
	
	function get_where(){
		$where = $this->tv_table_alias . '.value ';
		switch ($this->operator){
			case '=': case 'eq': $where .= ' = ' . $this->value; break;
			case 'gt': $where .= ' > ' . $this->value; break;
			case 'lt': $where .= ' < ' . $this->value; break;
			case 'elt': $where .= ' <= ' . $this->value; break;
			case 'egt': $where .= ' >= ' . $this->value; break;
			case 'like': $where .= " LIKE '%" . $this->value . "%'"; break;
			case 'is': $where .= " = '" . $this->value . "'"; break;
			case 'containsOne' : 
				$words = explode($this->DocLister->getCFGDef('filter_delimiter', ','), $this->value);
				foreach ($words as $word){
					$word_arr[] = $this->tv_table_alias . ".value LIKE '%" . trim($word) . "%'";
				}
				$where = '(' . implode(' OR ', $word_arr) . ')';
				break; 
			default: return '';
		}
		return $where;
	}
	
	function get_join(){
		global $modx;
		$join = 'LEFT JOIN ' . $modx->getFullTableName('site_tmplvar_contentvalues') . ' ' . $this->tv_table_alias 
		. ' ON ' . $this->tv_table_alias . '.contentid = ' . $this->main_table_alias . '.id AND ' . $this->tv_table_alias . '.tmplvarid = ' . $this->tv_id;
		return $join;
	}
}
?>