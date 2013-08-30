<?php
/**
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author kabachello <kabachnik@hotmail.com>
 */
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

abstract class filterDocLister{
    /**
     * Объект унаследованный от абстрактоного класса DocLister
     * @var DocLister $DocLister
     * @access protected
     */
    protected $DocLister;

    /**
     * Объект DocumentParser - основной класс MODX
     * @var DocumentParser
     * @access protected
     */
    protected $modx;

    /**
     * Алиас таблицы которая подключается для фильтрации
     * @var string
     * @access protected
     */
    protected $tableAlias = null;

    /**
     * Поле по которому происходит фильтрация
     * @var string
     * @access protected
     */
    protected $field = '';

    /**
     * Вид сопоставления поля со значением
     * @var string
     * @access protected
     */
    protected $operator = '';

    /**
     * Значение которое учавствует в фильтрации
     * @var string
     * @access protected
     */
    protected $value = '';

    /**
     * Запуск фильтра
     *
     * @param $DocLister экземпляр класса DocLister
     * @param $filter строка с условиями фильтрации
     * @return bool
     */
    final public function init($DocLister, $filter){
        $flag=false;
        if($DocLister instanceof DocLister){
            $this->DocLister=$DocLister;
            $this->modx = $this->DocLister->getMODX();
            $flag = $this->parseFilter($filter);
        }
        return $flag;
    }

    /**
     * Получение строки для подстановки в секцию WHERE SQL запроса
     *
     * @return string
     */
    abstract public function get_where();

    /**
     * Получение строки для подстановки в SQL запрос после подключения основной таблицы
     *
     * @return string
     */
    abstract public function get_join();

    /**
     * Разбор строки фильтрации
     *
     * @param $filter строка фильтрации
     * @return bool результат разбора фильтра
     */
    protected function parseFilter($filter){
        // first parse the give filter string
        $parsed = explode(':', $filter);
        $this->field = $parsed[1];
        $this->operator = $parsed[2];
        $this->value = $parsed[3];
        // exit if something is wrong
        return !(empty($this->field) || empty($this->operator) || is_null($this->value));
    }

    /**
     * Установка алиаса таблицы
     * @param string $value алиас
     */
    public function setTableAlias($value){
        $this->tableAlias = $value;
    }

    /**
     * Конструктор условий для WHERE секции
     *
     * @param $table_alias алиас таблицы
     * @param $field поле для фильтрации
     * @param $operator оператор сопоставления
     * @param $value искомое значение
     * @return string
     */
    protected function build_sql_where($table_alias, $field, $operator, $value){
        $this->DocLister->debug->debug('Build SQL query for filters', 'buildQuery', 2);
        $output = $table_alias . '.' . $field . ' ';
        switch ($operator){
            case '=': case 'eq': $output .= ' = ' . floatval($value); break;
            case 'gt': $output .= ' > ' . $value; break;
            case 'lt': $output .= ' < ' . $value; break;
            case 'elt': $output .= ' <= ' . $value; break;
            case 'egt': $output .= ' >= ' . $value; break;
            case 'like': $output .= " LIKE '%" . $this->escapeString($value) . "%'"; break;
            case 'is': $output .= " = '" . $this->escapeString($value) . "'"; break;
            case 'containsOne' :
                $words = explode($this->DocLister->getCFGDef('filter_delimiter', ','), $value);
                $word_arr = array();
                foreach ($words as $word){
                    $word_arr[] = $table_alias . '.' . $field . "  LIKE '%" . $this->escapeString(trim($word)) . "%'";
                }
                if(!empty($word_arr)){
                    $output = '(' . implode(' OR ', $word_arr) . ')';
                }
                break;
            default: $output = '';
        }
        $this->DocLister->debug->debugEnd("buildQuery");
        return $output;
    }

    /**
     * Получение алиаса таблицы по которой идет выборка
     * @return string
     */
    public function getTableAlias(){
        return $this->tableAlias;
    }
    
    /**
     * Экранирует строку перед передачей в SQL.
     * TODO добавить проверки на инжекшены
     * @param string $string
     * @return string
     */
    public function escapeString($string){
    	return str_replace("'", "\'", $string);
    }
}