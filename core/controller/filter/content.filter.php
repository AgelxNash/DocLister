<?php
/**
 * Basic filter using only the site_content table.
 * It is a good Idea to derive other filters, that join other tables to site_content from this
 * filter. This way a common parseFilter() an build_sql_where() methods can be used.
 * @author kabachello
 *
 */
class content_DL_filter extends filterDocLister{
	protected  $field;
	protected  $operator;
	protected  $value;
	
	function parseFilter($filter) {
		$return = false;
		
		// first parse the give filter string
		$parsed = explode(':', $filter);
		$this->field = $parsed[1];
		$this->operator = $parsed[2];
		$this->value = $parsed[3];
		// exit if something is wrong
		if (empty($this->field) || empty($this->operator) || is_null($this->value)) return false;
		
		return true;
	}
	
	function get_where(){
		return $this->build_sql_where($this->main_table_alias, $this->field, $this->operator, $this->value);
	}
	
	function build_sql_where($table_alias, $field, $operator, $value){
		$output = $table_alias . '.' . $field . ' ';
		switch ($operator){
			case '=': case 'eq': $output .= ' = ' . floatval($value); break;
			case 'gt': $output .= ' > ' . $value; break;
			case 'lt': $output .= ' < ' . $value; break;
			case 'elt': $output .= ' <= ' . $value; break;
			case 'egt': $output .= ' >= ' . $value; break;
			case 'like': $output .= " LIKE '%" . $value . "%'"; break;
			case 'is': $output .= " = '" . $value . "'"; break;
			case 'containsOne' :
				$words = explode($this->DocLister->getCFGDef('filter_delimiter', ','), $value);
				foreach ($words as $word){
					$word_arr[] = $table_alias . '.' . $field . "  LIKE '%" . trim($word) . "%'";
				}
				$output = '(' . implode(' OR ', $word_arr) . ')';
				break;
			default: return '';
		}
		return $output;
	}
	
	function get_join(){
		return '';
	}
}
?>