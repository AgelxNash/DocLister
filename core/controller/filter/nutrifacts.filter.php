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
class nutrifacts_DL_filter extends filterDocLister{
	private $field;
	private $operator;
	private $value;
	
	function parseFilter($filter) {
		global $modx;
		$return = false;
		
		// first parse the give filter string
		$parsed = explode(':', $filter);
		$this->field = $parsed[1];
		$this->operator = $parsed[2];
		$this->value = $parsed[3];
		// exit if something is wrong
		if (empty($this->field) || empty($this->operator) || empty($this->value)) return false;
		
		return true;
	}
	
	function get_where(){
		$where ='xnp.' . $this->field . ' ';
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
					$word_arr[] = 'xnp.' . $this->field . "  LIKE '%" . trim($word) . "%'";
				}
				$where = '(' . implode(' OR ', $word_arr) . ')';
				break; 
			default: return '';
		}
		return $where;
	}
	
	function get_join(){
		return 'LEFT JOIN xrecipe_nutrition_products xnp ON ' . $this->main_table_alias . '.id = xnp.document_id';
	}
}
?>