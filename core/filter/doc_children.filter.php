<?php
/**
 * Dummy filter. Some day might be used to replace the built-in parents parameter and thus make it optional.
 * @author kabachello
 */
class doc_children_DL_filter extends filterDocLister{
	
	function parseFilter($filter) {
		return true;
	}
	
	function get_where(){
		return '';
	}
	
	function get_join(){
		return '';
	}
}
?>