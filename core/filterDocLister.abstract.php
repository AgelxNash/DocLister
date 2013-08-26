<?php
/**
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author kabachello <kabachnik@hotmail.com>
 * @date 16.08.2013
 * @version 1.1.0
 */
abstract class filterDocLister{
    /**
     * @var DocLister $DocLister
     * @access protected
     */
    protected $DocLister;
    protected $modx;
    protected $tableAlias = null;

    protected $field;
    protected $operator;
    protected $value;

    final public function init($DocLister, $filter){
        $flag=false;
        if($DocLister instanceof DocLister){
            $this->DocLister=$DocLister;
            $this->modx = $this->DocLister->getMODX();
            $flag = $this->parseFilter($filter);
        }
        return $flag;
    }

    abstract public function get_where();
    abstract public function get_join();

    protected function parseFilter($filter){
        // first parse the give filter string
        $parsed = explode(':', $filter);
        $this->field = $parsed[1];
        $this->operator = $parsed[2];
        $this->value = $parsed[3];
        // exit if something is wrong
        if (empty($this->field) || empty($this->operator) || is_null($this->value)) return false;

        return true;
    }

    public function setTableAlias($value){
        $this->tableAlias = $value;
    }

    protected function build_sql_where($table_alias, $field, $operator, $value){
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
                $word_arr = array();
                foreach ($words as $word){
                    $word_arr[] = $table_alias . '.' . $field . "  LIKE '%" . trim($word) . "%'";
                }
                if(!empty($word_arr)){
                    $output = '(' . implode(' OR ', $word_arr) . ')';
                }
                break;
            default: return '';
        }
        return $output;
    }

    public function getTableAlias(){
        return $this->tableAlias;
    }
}