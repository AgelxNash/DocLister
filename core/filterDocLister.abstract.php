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
     * Номер фильтра в общем списке фильтров
     * @var int
     * @access protected
     */
    protected $totalFilters = 0;

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
            $this->totalFilters = $this->DocLister->getCountFilters();
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
        $parsed = explode(':', $filter, 4);
        $this->field = isset($parsed[1]) ? $parsed[1] : null;
        $this->operator = isset($parsed[2]) ? $parsed[2] : null;
        $this->value = isset($parsed[3]) ? $parsed[3] : null;
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
        $this->DocLister->debug->debug('Build SQL query for filters: '.$this->DocLister->debug->dumpData(func_get_args()), 'buildQuery', 2);
        $output = '`'.$table_alias . '`.`' . $field . '` ';
        switch ($operator){
            case '=': case 'eq': case 'is': $output .= " = '" . $this->modx->db->escape($value) ."'"; break;
            case 'gt': $output .= ' > ' . floatval($value); break;
            case 'lt': $output .= ' < ' . floatval($value); break;
            case 'elt': $output .= ' <= ' . floatval($value); break;
            case 'egt': $output .= ' >= ' . floatval($value); break;
            case 'like': $output = $this->DocLister->LikeEscape($output,$value); break;
            case 'against':{ /** content:pagetitle,description,content,introtext:against:искомая строка */
                if(trim($value)!=''){
                    $field = explode(",", $this->field);
                    $field = implode(",", $this->DocLister->renameKeyArr($field, $this->getTableAlias()));
                    $output = "MATCH ({$field}) AGAINST ('{$this->modx->db->escape($value)}*')";
                }
                break;
            }
            case 'containsOne' :
                $words = explode($this->DocLister->getCFGDef('filter_delimiter', ','), $value);
                $word_arr = array();
                foreach ($words as $word){
                    /**
                     * $word оставляю без trim, т.к. мало ли, вдруг важно найти не просто слово, а именно его начало
                     * Т.е. хочется найти не слово содержащее $word, а начинающееся с $word. Для примера:
                     * искомый $word = " когда". С trim найдем "...мне некогда..." и "...тут когда-то...";
                     * Без trim будт обнаружено только "...тут когда-то..."
                     */
                    $word_arr[] = $this->DocLister->LikeEscape($table_alias.'.'.$field, $word);
                }
                if(!empty($word_arr)){
                    $output = '(' . implode(' OR ', $word_arr) . ')';
                }
                break;
            case 'in':
                $output .= ' IN(' . $this->DocLister->sanitarIn($value, ',', false) . ')';
                break;
            default: $output = '';break;
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
}
