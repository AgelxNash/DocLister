<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}
error_reporting(E_ALL);
ini_set('display_errors','On');
/**
 * DocLister class
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 *
 * @TODO add controller for work with plugin http://modx.com/extras/package/quid and get TV value via LEFT JOIN
 * @TODO add controller for filter by TV values
 * @TODO add method load default template
 * @TODO add example custom controller for build google sitemap.xml
 * @TODO add method build tree for replace Wayfinder if need TV value in menu OR sitemap
 * @TODO add controller for show list web-user with filter by group and other user information
 * @TODO depending on the parameters
 * @TODO prepare value before return final data (maybe callback function OR extender)
 */

require_once(dirname(dirname(__FILE__))."/lib/jsonHelper.class.php");
require_once(dirname(dirname(__FILE__)). "/lib/xnop.class.php");

abstract class DocLister
{
    /**
     * Текущая версия ядра DocLister
     */
    const VERSION = '1.1.8';

    /**
     * Ключ в массиве $_REQUEST в котором находится алиас запрашиваемого документа
     */
    const AliasRequest = 'q';
    /**
     * Массив документов полученный в результате выборки из базы
     * @var array
     * @access protected
     */
    protected $_docs = array();

    /**
     * Массив документов self::$_docs собранный в виде дерева
     * @var array
     * @access protected
     */
    protected $_tree = array();

    /**
     * @var
     * @access protected
     */
    protected $IDs = 0;

    /**
     * Объект DocumentParser - основной класс MODX'а
     * @var DocumentParser
     * @access protected
     */
    protected $modx = null;

    /**
     * Массив загруженных экстендеров
     * @var array
     * @access protected
     */
    protected $extender = array();

    /**
     * Массив плейсхолдеров доступных в шаблоне
     * @var array
     * @access protected
     */
    protected $_plh = array();

    /**
     * Языковой пакет
     * @var array
     * @access protected
     */
    protected $_lang = array();

    /**
     * Пользовательский языковой пакет
     * @var array
     * @access protected
     */
    protected $_customLang = array();
    /**
     * Массив настроек переданный через параметры сниппету
     * @var array
     * @access private
     */
    private $_cfg = array();

    /**
     * Список таблиц уже с префиксами MODX
     * @var array
     * @access private
     */
    private $_table = array();

    /**
     * PrimaryKey основной таблицы
     * @var string
     * @access protected
     */
    protected $idField = 'id';

    /**
     * Дополнительные условия для SQL запросов
     * @var array
     * @access protected
     */
    protected $_filters = array('where'=>'', 'join'=>'');

    /**
     * Список доступных логических операторов для фильтрации
     * @var array
     * @access protected
     */
    protected $_logic_ops = array('AND'=>' AND ', 'OR' => ' OR '); // logic operators currently supported

    /**
     * Режим отладки
     * @var int
     * @access private
     */
    private $_debugMode = 0;

    /**
     * Отладчик
     *
     * @var debugDl|xNop
     * @access public
     */
    public $debug = null;

    /**
     * Время запуска сниппета
     * @var int
     */
    private $_timeStart = 0;

    /**
     * Номер фильтра в общем списке фильтров
     * @var int
     * @access protected
     */
    protected $totalFilters = 0;

    /**
     * Конструктор контроллеров DocLister
     *
     * @param DocumentParser $modx объект DocumentParser - основной класс MODX
     * @param array $cfg массив параметров сниппета
     */
    function __construct($modx, $cfg = array())
    {
        try {
            if (extension_loaded('mbstring')) {
                mb_internal_encoding("UTF-8");
            } else {
                throw new Exception('Not found php extension mbstring');
            }

            if ($modx instanceof DocumentParser) {
                $this->modx = $modx;
                $this->setDebug(1);
                $this->loadLang(array('core','json'));

                if (!is_array($cfg) || empty($cfg)) $cfg = $this->modx->Event->params;
            } else {
                throw new Exception('MODX var is not instaceof DocumentParser');
            }
            if(isset($cfg['config'])){
                $cfg = array_merge($this->loadConfig($cfg['config']), $cfg);
            }
            if (!$this->setConfig($cfg)) {
                throw new Exception('no parameters to run DocLister');
            }
        } catch (Exception $e) {
            $this->ErrorLogger($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTrace());
        }
        $this->setDebug($this->getCFGDef('debug', 0));

        if ($this->checkDL()) {
            $cfg = array();
            if (($IDs = $this->getCFGDef('documents', '')) != '' || $this->getCFGDef('idType', '') == 'documents') {
                $cfg['idType'] = "documents";
            } else {
                $cfg['idType'] = "parents";
                if (($IDs = $this->getCFGDef('parents')) === null) {
                    $IDs = $this->modx->documentIdentifier;
                }
            }
            $this->setConfig($cfg);
            $this->setIDs($IDs);
        }

        $this->setLocate();

        if($this->getCFGDef("customLang")){
            $this->getCustomLang();
        }
        $this->loadExtender($this->getCFGDef("extender", ""));

        if ($this->checkExtender('request')) {
            $this->extender['request']->init($this, $this->getCFGDef("requestActive", ""));
        }
        $this->_filters = $this->getFilters($this->getCFGDef('filters', ''));
    }

    /**
     * Установить время запуска сниппета
     * @param float|null $time
     */
    public function setTimeStart($time = null){
        $this->_timeStart = is_null($time) ? microtime(true) : $time;
    }

    /**
     * Время запуска сниппета
     *
     * @return int
     */
    public function getTimeStart(){
        return $this->_timeStart;
    }

    /**
     * Установка режима отладки
     * @param int $flag режим отладки
     */
    public function setDebug($flag=0){
        if($this->_debugMode!=(int)$flag){
            $this->_debugMode = (int)$flag;
            $this->debug = null;
            if($this->_debugMode>0){
                if(isset($_SESSION['usertype']) && $_SESSION['usertype']=='manager'){
                    error_reporting(E_ALL);
                    ini_set('display_errors','On');
                }
                $dir = dirname(dirname(__FILE__));
                if (file_exists($dir . "/lib/debugDL.class.php")) {
                    include_once($dir . "/lib/debugDL.class.php");
                    if (class_exists("debugDL", false)) {
                        $this->debug = new debugDL($this);
                    }
                }
            }

            if(is_null($this->debug)){
                $this->debug = new xNop();
                $this->_debugMode = 0;
            }
        }
    }

    /**
     * Информация о режиме отладки
     */
    public function getDebug(){
        return $this->_debugMode;
    }

    /**
     * Генерация имени таблицы с префиксом и алиасом
     *
     * @param string $name имя таблицы
     * @param string $alias желаемый алиас таблицы
     * @return string имя таблицы с префиксом и алиасом
     */
    public function getTable($name, $alias = ''){
        if(!isset($this->_table[$name])){
            $this->_table[$name] = $this->modx->getFullTableName($name);
        }
        $table = $this->_table[$name];
        if(!empty($alias) && is_scalar($alias)){
            $table .= " as ".$alias;
        }
        return $table;
    }

    /**
     * Загрузка конфигов из файла
     *
     * @param $name string имя конфига
     * @return array массив с настройками
     */
    public function loadConfig($name){
        $this->debug->debug('Load json config: '.$this->debug->dumpData($name), 'loadconfig', 2);
        if(!is_scalar($name)){
            $name = '';
        }
        $config = array();
        $name = explode(";", $name);
        foreach($name as $cfgName){
            $cfgName = explode(":", $cfgName, 2);
            if(empty($cfgName[1])){
                $cfgName[1] = 'custom';
            }
            $configFile = dirname(dirname(__FILE__))."/config/{$cfgName[1]}/{$cfgName[0]}.json";
            if(file_exists($configFile) && is_readable($configFile)){
                $json = file_get_contents($configFile);
                $config = array_merge($config, $this->jsonDecode($json, array('assoc'=>true), true));
            }
        }

        $this->debug->debugEnd("loadconfig");
        return $config;
    }


    /**
     * Разбор JSON строки при помощи json_decode
     *
     * @param $json string строка c JSON
     * @param array $config ассоциативный массив с настройками для json_decode
     * @param bool $nop создавать ли пустой объект запрашиваемого типа
     * @return array|mixed|xNop
     */
    public function jsonDecode($json, $config = array(), $nop = false){
        $this->debug->debug('Decode JSON: '.$this->debug->dumpData($json, 'code').' with config: '.$this->debug->dumpData($config), 'jsonDecode', 2);
        $config = jsonHelper::jsonDecode($json, $config, $nop);
        $this->isErrorJSON($json);
        $this->debug->debugEnd("jsonDecode");
        return $config;
    }

    /**
     * Были ли ошибки во время работы с JSON
     *
     * @param $json string строка с JSON для записи в лог при отладке
     * @return bool|string
     */
    public function isErrorJSON($json){
        $error = false;
        $error = jsonHelper::json_last_error_msg();
        if(!in_array($error, array('error_none','other'))){
            $this->debug->error($this->getMsg('json.'.$error).": ".$this->debug->dumpData($json, 'code'), 'JSON');
            $error = true;
        }
        return $error;
    }
    /**
     * Проверка параметров и загрузка необходимых экстендеров
     * return boolean статус загрузки
     */
    public function checkDL()
    {
        $this->debug->debug('Check DocLister parameters', 'checkDL', 2);
        $flag = true;
        $extenders = $this->getCFGDef('extender', '');
        $extenders = explode(",", $extenders);
        try {
            if (($this->getCFGDef('requestActive', '') != '' || in_array('request', $extenders)) && !$this->_loadExtender('request')) { //OR request in extender's parameter
                throw new Exception('Error load request extender');
                $flag = false;
            }

            if (($this->getCFGDef('summary', '') != '' || in_array('summary', $extenders)) && !$this->_loadExtender('summary')) { //OR summary in extender's parameter
                throw new Exception('Error load summary extender');
                $flag = false;
            }

            if (
                (int)$this->getCFGDef('display', 0) > 0 && ( //OR paginate in extender's parameter
                    in_array('paginate', $extenders) || $this->getCFGDef('paginate', '') != '' ||
                    $this->getCFGDef('TplPrevP', '') != '' || $this->getCFGDef('TplPage', '') != '' ||
                    $this->getCFGDef('TplCurrentPage', '') != '' || $this->getCFGDef('TplWrapPaginate', '') != '' ||
                    $this->getCFGDef('pageLimit', '') != '' || $this->getCFGDef('pageAdjacents', '') != '' ||
                    $this->getCFGDef('PaginateClass', '') != '' || $this->getCFGDef('TplNextP', '') != ''
                ) && !$this->_loadExtender('paginate')
            ) {
                throw new Exception('Error load paginate extender');
                $flag = false;
            } else if ((int)$this->getCFGDef('display', 0) == 0) {
                $extenders = $this->unsetArrayVal($extenders, 'paginate');
            }

            if($this->getCFGDef('prepare', '')!=''){
                $this->_loadExtender('prepare');
            }
        } catch (Exception $e) {
            $this->ErrorLogger($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTrace());
        }

        $this->setConfig('extender', implode(",", $extenders));
        $this->debug->debugEnd("checkDL");
        return $flag;
    }

    /**
     * Удаление определенных данных из массива
     *
     * @param array $data массив с данными
     * @param mixed $val значение которые необходимо удалить из массива
     * @return array отчищеный массив с данными
     */
    private function unsetArrayVal($data, $val)
    {
        $out = array();
        if (is_array($data)) {
            foreach ($data as $item) {
                if ($item != $val) {
                    $out[] = $item;
                } else {
                    continue;
                }
            }
        }
        return $out;
    }

    /**
     * Генерация URL страницы
     *
     * @param int $id уникальный идентификатор страницы
     * @return string URL страницы
     */
    abstract public function getUrl($id = 0);

    /**
     * Получение массива документов из базы
     * @param mixed $tvlist дополнительные параметры выборки
     * @return array Массив документов выбранных из базы
     */
    abstract public function getDocs($tvlist = '');

    /**
     * Подготовка результатов к отображению.
     *
     * @param string $tpl шаблон
     * @return mixed подготовленный к отображению результат выборки
     */
    abstract public function _render($tpl = '');

    /**
     * Подготовка результатов к отображению в соответствии с настройками
     *
     * @param string $tpl шаблон
     * @return string
     */
    public function render($tpl = '')
    {
        $this->debug->debug('Render data with template '.$this->debug->dumpData($tpl), 'render', 2);
        $out = '';
        if (1 == $this->getCFGDef('tree', '0')) {
            foreach ($this->_tree as $item) {
                $out .= $this->renderTree($item);
            }
            $out = $this->parseChunk($this->getCFGDef("ownerTPL", ""), array($this->getCFGDef("sysKey", "dl") . ".wrap" => $out));
        } else {
            $out = $this->_render($tpl);
        }
        $this->debug->debugEnd('render');
        return $out;
    }

    /***************************************************
     ****************** CORE Block *********************
     ***************************************************/

    /**
     * Display and save error information
     *
     * @param string $message error message
     * @param integer $code error number
     * @param string $file error on file
     * @param integer $line error on line
     * @param array $trace stack trace
     *
     * @todo $this->debug
     */
    final public function ErrorLogger($message, $code, $file, $line, $trace)
    {
        if ($this->getCFGDef('debug', '0') == '1') {
            echo "CODE #" . $code . "<br />";
            echo "on file: " . $file . ":" . $line . "<br />";
            echo "<pre>";
            var_dump($trace);
            echo "</pre>";
        }
        die($message);
    }

    /**
     * Получение объекта DocumentParser
     *
     * @return DocumentParser
     */
    final public function getMODX()
    {
        return $this->modx;
    }

    /**
     * load extenders
     *
     * @param string $ext name extender separated by ,
     * @return boolean status load extenders
     */
    final public function loadExtender($ext = '')
    {
        $out = true;
        if ($ext != '') {
            $ext = explode(",", $ext);
            foreach ($ext as $item) {
                try {
                    if ($item != '' && !$this->_loadExtender($item)) {
                        $out = false;
                        throw new Exception('Error load ' . htmlspecialchars($item) . ' extender');
                        break;
                    }
                } catch (Exception $e) {
                    $this->ErrorLogger($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTrace());
                }
            }
        }
        return $out;
    }

    /**
     * Сохранение настроек вызова сниппета
     * @param array $cfg массив настроек
     * @return boolean результат сохранения настроек
     */
    final public function setConfig($cfg)
    {
        if (is_array($cfg)) {
            $this->_cfg = array_merge($this->_cfg, $cfg);
            $ret = count($this->_cfg);
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * Получение информации из конфига
     *
     * @param string $name имя параметра в конфиге
     * @param mixed $def значение по умолчанию, если в конфиге нет искомого параметра
     * @return mixed значение из конфига
     */
    final public function getCFGDef($name, $def = null)
    {
        return isset($this->_cfg[$name]) ? $this->_cfg[$name] : $def;
    }

    /**
     * Сохранение данных в массив плейсхолдеров
     *
     * @param mixed $data данные
     * @param int $set устанавливать ли глобальнй плейсхолдер MODX
     * @param string $key ключ локального плейсхолдера
     * @return string
     */
    final public function toPlaceholders($data, $set = 0, $key = 'contentPlaceholder')
    {
        $this->debug->debug(null,'toPlaceholders',2);
        $out = '';
        $this->_plh[$key] = $data;
        if ($set == 0) {
            $set = $this->getCFGDef('contentPlaceholder', 0);
        }
        if ($set != 0) {
            $id = $this->getCFGDef('id', '');
            if ($id != '') $id .= ".";
            $this->modx->toPlaceholder($key, $data, $id);
            $this->debug->debugEnd(
                "toPlaceholders",
                "Save ".$this->debug->dumpData($key)." placeholder: ".$this->debug->dumpData($data)
            );
        } else {
            $out = $data;
            $this->debug->debugEnd(
                "toPlaceholders",
                "Show ".$this->debug->dumpData($key)." placeholder: ".$this->debug->dumpData($data)
            );
        }
        return $out;
    }

    /**
     * Предварительная обработка данных перед вставкой в SQL запрос вида IN
     * Если данные в виде строки, то происходит попытка сформировать массив из этой строки по разделителю $sep
     * Точно по тому, по которому потом данные будут собраны обратно
     *
     * @param mixed $data данные для обработки
     * @param string $sep разделитель
     * @param boolean $quote заключать ли данные на выходе в кавычки
     * @return string обработанная строка
     */
    final public function sanitarIn($data, $sep = ',', $quote=true)
    {
        if (!is_array($data)) {
            $data = explode($sep, $data);
        }
        $out = array();
        foreach ($data as $item) {
            $out[] = $this->modx->db->escape($item);
        }
        $q = $quote ? "'" : "";
        $out = $q . implode($q.",".$q, $out) . $q;
        return $out;
    }

    /**
     * Загрузка кастомного лексикона
     *
     * В файле с кастомным лексиконом ключи в массиве дожны быть полные
     * Например:
     *      - core.bla-bla
     *      - paginate.next
     *
     * @param string $lang имя языкового пакета
     * @return array
     */
    final public function getCustomLang($lang = ''){
        if (empty($lang)) {
            $lang = $this->getCFGDef('lang', $this->modx->config['manager_language']);
        }
        if (file_exists(dirname(dirname(__FILE__)) . "/lang/" . $lang . ".php")) {
            $tmp = include_once(dirname(__FILE__) . "/lang/" . $lang . ".php");
            $this->_customLang = is_array($tmp) ? $tmp : array();
        }
        return $this->_customLang;
    }

    /**
     * Загрузка языкового пакета
     *
     * @param string $name ключ языкового пакета
     * @param string $lang имя языкового пакета
     * @param boolean $rename Переименовывать ли элементы массива
     * @return array массив с лексиконом
     */
    final public function loadLang($name = 'core', $lang = '', $rename=true)
    {
        if (empty($lang)) {
            $lang = $this->getCFGDef('lang', $this->modx->config['manager_language']);
        }

        $this->debug->debug('Load language '.$this->debug->dumpData($name).".".$this->debug->dumpData($lang), 'loadlang', 2);
        if(is_scalar($name)){
            $name = array($name);
        }
        foreach($name as $n){
            if (file_exists(dirname(__FILE__) . "/lang/" . $lang . "/" . $n . ".inc.php")) {
                $tmp = include_once(dirname(__FILE__) . "/lang/" . $lang . "/" . $n . ".inc.php");
                if (is_array($tmp)) {
                    /**
                     * Переименовыываем элементы массива из array('test'=>'data') в array('name.test'=>'data')
                     */
                    if($rename){
                        $tmp = $this->renameKeyArr($tmp, $n, '', '.');
                    }
                    $this->_lang = array_merge($this->_lang, $tmp);
                }
            }
        }
        $this->debug->debugEnd("loadlang");
        return $this->_lang;
    }

    /**
     * Получение строки из языкового пакета
     *
     * @param $name имя записи в языковом пакете
     * @param string $def Строка по умолчанию, если запись в языковом пакете не будет обнаружена
     * @return string строка в соответствии с текущими языковыми настройками
     */
    final public function getMsg($name, $def = '')
    {
        if(isset($this->_customLang[$name])){
            $say = $this->_customLang[$name];
        }else{
            $say = isset($this->_lang[$name]) ? $this->_lang[$name] : $def;
        }
        return $say;
    }

    /**
     * Переменовывание элементов массива
     *
     * @param $data массив с данными
     * @param string $prefix префикс ключей
     * @param string $suffix суффикс ключей
     * @param string $sep разделитель суффиксов, префиксов и ключей массива
     * @return array массив с переименованными ключами
     */
    final public function renameKeyArr($data, $prefix = '', $suffix = '', $sep = '.')
    {
        $out = array();
        if ($prefix == '' && $suffix == '') {
            $out = $data;
        } else {
            if ($prefix != '') {
                $prefix = $prefix . $sep;
            }
            if ($suffix != '') {
                $suffix = $sep . $suffix;
            }
            foreach ($data as $key => $item) {
                $out[$prefix . $key . $suffix] = $item;
            }
        }
        return $out;
    }

    /**
     * Установка локали
     *
     * @param string $locale локаль
     * @return string имя установленной локали
     */
    final public function setLocate($locale = '')
    {
        if ('' == $locale) {
            $locale = $this->getCFGDef('locale', '');
        }
        if ('' != $locale) {
            setlocale(LC_ALL, $locale);
        }
        return $locale;
    }

    /**
     * Шаблонизация дерева.
     * Перевод из массива в HTML в соответствии с указанным шаблоном
     *
     * @param array $data массив сформированный как дерево
     * @return string строка для отображения пользователю
     */
    protected function renderTree($data)
    {
        $out = '';
        if (!empty($data['#childNodes'])) {
            foreach ($data['#childNodes'] as $item) {
                $out .= $this->renderTree($item);
            }
        }

        $data[$this->getCFGDef("sysKey", "dl") . ".wrap"] = $this->parseChunk($this->getCFGDef("ownerTPL", ""), array($this->getCFGDef("sysKey", "dl") . ".wrap" => $out));
        $out = $this->parseChunk($this->getCFGDef('tpl', ''), $data);
        return $out;
    }

    /**
     * refactor $modx->getChunk();
     *
     * @param string $name Template: chunk name || @CODE: template || @FILE: file with template
     * @return string html template with placeholders without data
     *
     * @TODO debug mode for log error
     */
    private function _getChunk($name)
    {
        $this->debug->debug('Get chunk by name "'.$this->debug->dumpData($name).'"',"getChunk",2);
        $tpl = '';
        //without trim
        if ($name != '' && !isset($this->modx->chunkCache[$name])) {
            $mode = (preg_match('/^((@[A-Z]+)[:]{0,1})(.*)/Asu', trim($name), $tmp) && isset($tmp[2], $tmp[3])) ? $tmp[2] : false;
            $subTmp = (isset($tmp[3])) ? trim($tmp[3]) : null;
            switch ($mode) {
                case '@FILE':
                { //tpl in file
                    if ($subTmp != '') {
                        $real = realpath(MODX_BASE_PATH . 'assets/templates');
                        $path = realpath(MODX_BASE_PATH . 'assets/templates/' . preg_replace(array('/\.*[\/|\\\]/i', '/[\/|\\\]+/i'), array('/', '/'), $subTmp) . '.html');
                        $fname = explode(".", $path);
                        if ($real == substr($path, 0, strlen($real)) && end($fname) == 'html' && file_exists($path)) {
                            $tpl = file_get_contents($path);
                        }
                    }
                    break;
                }
                case '@CHUNK':
                {
                    if ($subTmp != '') {
                        $tpl = $this->modx->getChunk($subTmp);
                    } else {
                        //error chunk name
                    }
                    break;
                }
                case '@TPL':
                case '@CODE':
                {
                    $tpl = $tmp[3]; //without trim
                    break;
                }
                case '@DOCUMENT':
                case '@DOC':
                {
                    switch (true) {
                        case ((int)$subTmp > 0):
                        {
                            $tpl = $this->modx->getPageInfo((int)$subTmp, 0, "content");
                            $tpl = isset($tpl['content']) ? $tpl['content'] : '';
                            break;
                        }
                        case ((int)$subTmp == 0):
                        {
                            $tpl = $this->modx->documentObject['content'];
                            break;
                        }
                        default:
                            {
                            //error docid
                            }
                    }
                    break;
                }
                case '@PLH':
                case '@PLACEHOLDER':
                {
                    if ($subTmp != '') {
                        $tpl = $this->modx->getPlaceholder($subTmp);
                    } else {
                        //error placeholder name
                    }
                    break;
                }
                case '@CFG':
                case '@CONFIG':
                case '@OPTIONS':
                {
                    if ($subTmp != '') {
                        $tpl = $this->modx->getConfig($subTmp);
                    } else {
                        //error config name
                    }
                    break;
                }
                default:
                    {
                    if ($this->checkExtender('template')) {
                        $tpl = $this->extender['template']->init($this, array('full' => $name, 'mode' => $mode, 'tpl' => $tmp[3])); //without trim
                    } else {
                        $tpl = $this->modx->getChunk($name);
                    }
                    }
            }

            $tpl = $this->modx->chunkCache[$name] = $this->parseLang($tpl);
        }else{
            if($name!=''){
                $tpl = $this->modx->getChunk($name);
                $tpl = $this->parseLang($tpl);
            }
        }

        $this->debug->debugEnd("getChunk");
        return $tpl;
    }

    /**
     * Замена в шаблоне фраз из лексикона
     *
     * @param string $tpl HTML шаблон
     * @return string
     */
    public function parseLang($tpl){
        $this->debug->debug(
            "parseLang ".$this->debug->dumpData($tpl),
            "parseLang",
            2
        );
        if(is_scalar($tpl) && !empty($tpl)){
            if(preg_match_all("/\[\%([a-zA-Z0-9\.\_\-]+)\%\]/", $tpl, $match)){
                $langVal = array();
                foreach($match[1] as $item){
                    $langVal[] = $this->getMsg($item);
                }
                $tpl = str_replace($match[0], $langVal, $tpl);
            }
        }else{
            $tpl = '';
        }
        $this->debug->debugEnd("parseLang");
        return $tpl;
    }
    /**
     * refactor $modx->parseChunk();
     *
     * @param string $name Template: chunk name || @CODE: template || @FILE: file with template
     * @param array $data paceholder
     * @return string html template with data without placeholders
     */
    public function parseChunk($name, $data)
    {
        $this->debug->debug(
            "parseChunk ".$this->debug->dumpData($name)." ".$this->debug->dumpData($data),
            "parseChunk",
            2
        );
        if (is_array($data) && ($out = $this->_getChunk($name)) != '') {
            if(preg_match("/\[\+[a-zA-Z0-9\.\_\-]+\+\]/",$out)){
                $data = $this->renameKeyArr($data, '[', ']', '+');
                $out = str_replace(array_keys($data), array_values($data), $out);
            }else{
                $this->debug->debug("No placeholders in chunk: ".$this->debug->dumpData($name), '', 2);
            }
        }else{
            $this->debug->debug("Empty chunk: ".$this->debug->dumpData($name), '', 2);
        }
        $this->debug->debugEnd("parseChunk");
        return $out;
    }

    /**
     * Get full template from parameter name
     *
     * @param string $name param name
     * @param string $val default value
     *
     * @return string html template from parameter
     */
    public function getChunkByParam($name, $val = '')
    {
        $data = $this->getCFGDef($name, $val);
        $data = $this->_getChunk($data);
        return $data;
    }

    /**
     * Формирование JSON ответа
     *
     * @param array $data массив данных которые подготавливаются к выводу в JSON
     * @param mixed $fields список полей учавствующих в JSON ответе. может быть либо массив, либо строка с разделителем , (запятая)
     * @param array $array данные которые необходимо примешать к ответу на каждой записи $data
     * @return string JSON строка
     */
    public function getJSON($data, $fields, $array = array())
    {
        $out = array();
        $fields = is_array($fields) ? $fields : explode(",", $fields);
        if (is_array($array) && count($array) > 0) {
            $tmp = array();
            foreach ($data as $i => $v) { //array_merge not valid work with integer index key
                $tmp[$i] = (isset($array[$i]) ? array_merge($v, $array[$i]) : $v);
            }
            $data = $tmp;
        }

        foreach ($data as $num => $doc) {
            $tmp = array();
            foreach ($doc as $name => $value) {
                if (in_array($name, $fields) || (isset($fields[0]) && $fields[0] == '1')) {
                    $tmp[str_replace(".", "_", $name)] = $value; //JSON element name without dot
                }
            }
            $out[$num] = $tmp;
        }

        if ('new' == $this->getCFGDef('JSONformat', 'old')) {
            $return = array();

            $return['rows'] = array();
            foreach ($out as $key => $item) {
                $return['rows'][] = isset($item[$key]) ? $item[$key] : $item;
            }
            $return['total'] = $this->getChildrenCount();
        } else {
            $return = $out;
        }
        $out = json_encode($return);
        $this->isErrorJSON($return);
        return $out;
    }

    /**
     * @param string $name extender name
     * @return boolean status extender load
     */
    final public function checkExtender($name)
    {
        return (isset($this->extender[$name]) && $this->extender[$name] instanceof $name . "_DL_Extender");
    }

    /**
     * Вытащить экземпляр класса экстендера из общего массива экстендеров
     *
     * @param $name имя экстендера
     * @param bool $autoload Если экстендер не загружен, то пытаться ли его загрузить
     * @param bool $nop если экстендер не загружен, то загружать ли xNop
     * @return null|xNop
     */
    public function getExtender($name, $autoload = false, $nop = false){
        $out = null;
        if((is_scalar($name) && $this->checkExtender($name)) || ($autoload && $this->_loadExtender($name))){
            $out = $this->extender[$name];
        }
        if($nop){
            $out = new xNop();
        }
        return $out;
    }

    /**
     * load extender
     *
     * @param string $name name extender
     * @return boolean $flag status load extender
     */
    final protected function _loadExtender($name)
    {
        $this->debug->debug('Load Extender '.$this->debug->dumpData($name), 'LoadExtender', 2);
        $flag = false;

        $classname = ($name != '') ? $name . "_DL_Extender" : "";
        if ($classname != '' && isset($this->extender[$name]) && $this->extender[$name] instanceof $classname) {
            $flag = true;
        } else {
            if (!class_exists($classname, false) && $classname != '') {
                if (file_exists(dirname(__FILE__) . "/extender/" . $name . ".extender.inc")) {
                    include_once(dirname(__FILE__) . "/extender/" . $name . ".extender.inc");
                }
            }
            if (class_exists($classname, false) && $classname != '') {
                $this->extender[$name] = new $classname($this,$name);
                $flag = true;
            }
        }
        if(!$flag){
            $this->debug->debug("Error load Extender ".$this->debug->dumpData($name));
        }
        $this->debug->debugEnd('LoadExtender');
        return $flag;
    }

    /*************************************************
     ****************** IDs BLOCK ********************
     ************************************************/

    /**
     * Очистка массива $IDs по которому потом будет производиться выборка документов
     *
     * @param mixed $IDs список id документов по которым необходима выборка
     * @return array очищенный массив
     */
    final public function setIDs($IDs)
    {
        $this->debug->debug('set ID list '.$this->debug->dumpData($IDs), 'setIDs', 2);
        $IDs = $this->cleanIDs($IDs);
        $type = $this->getCFGDef('idType', 'parents');
        $depth = $this->getCFGDef('depth', '1');
        if ($type == 'parents' && $depth > 1) {
            $tmp = $IDs;
            do {
                if (count($tmp) > 0) {
                    $tmp = $this->getChildernFolder($tmp);
                    $IDs = array_merge($IDs, $tmp);
                }
            } while ((--$depth) > 1);
        }
        $this->debug->debugEnd("setIDs");
        return ($this->IDs = $IDs);
    }

    /**
     * Очистка данных и уникализация списка цифр.
     * Если был $IDs был передан как строка, то эта строка будет преобразована в массив по разделителю $sep
     * @param mixed $IDs данные для обработки
     * @param string $sep разделитель
     * @return array очищенный массив с данными
     */
    final public function cleanIDs($IDs, $sep = ',')
    {
        $this->debug->debug('clean IDs '.$this->debug->dumpData($IDs).' with separator '.$this->debug->dumpData($sep), 'cleanIDs', 2);
        $out = array();
        if (!is_array($IDs)) {
            $IDs = explode($sep, $IDs);
        }
        foreach ($IDs as $item) {
            $item = trim($item);
            if (is_numeric($item) && (int)$item >= 0) { //Fix 0xfffffffff
                $out[] = (int)$item;
            }
        }
        $out = array_unique($out);
        $this->debug->debugEnd("cleanIDs");
        return $out;
    }

    /**
     * Проверка массива с id-шниками документов для выборки
     * @return boolean пригодны ли данные для дальнейшего использования
     */
    final protected function checkIDs()
    {
        return (is_array($this->IDs) && count($this->IDs) > 0) ? true : false;
    }

    /**
     * Get all field values from array documents
     *
     * @param string $userField field name
     * @param boolean $uniq Only unique values
     * @global array $_docs all documents
     * @return array all field values
     */
    final public function getOneField($userField, $uniq = false)
    {
        $out = array();
        foreach ($this->_docs as $doc => $val) {
            if (isset($val[$userField]) && (($uniq && !in_array($val[$userField], $out)) || !$uniq)) {
                $out[$doc] = $val[$userField];
            }
        }
        return $out;
    }

    /**********************************************************
     ********************** SQL BLOCK *************************
     *********************************************************/

    /**
     * Подсчет документов удовлетворящиюх выборке
     *
     * @return int Число дочерних документов
     */
    abstract public function getChildrenCount();

    /**
     * Выборка документов которые являются дочерними относительно $id документа и в тоже время
     * являются родителями для каких-нибудь других документов
     *
     * @param string $id значение PrimaryKey родителя
     * @return array массив документов
     */
    abstract public function getChildernFolder($id);

    /**
     *    Sorting method in SQL queries
     *
     *    @global string $order
     *    @global string $orderBy
     *    @global string sortBy
     *
     *    @param string $sortNme default sort field
     *    @param string $orderDef default order (ASC|DESC)
     *
     *    @return string Order by for SQL
     */
    final protected function SortOrderSQL($sortName, $orderDef = 'DESC')
    {
        $this->debug->debug('', 'sortORDER', 2);

        $sort = '';
        switch($this->getCFGDef('sortType','')){
            case 'none':{
                break;
            }
            case 'doclist':{
                $idList = $this->sanitarIn($this->IDs,',', false);
                $out['orderBy'] = "find_in_set({$this->getPK()}, '{$idList}')";
                $this->setConfig($out); //reload config;
                $sort = "ORDER BY ".$out['orderBy'];
                break;
            }
            default:{
            $out = array('orderBy' => '', 'order' => '', 'sortBy' => '');
            if (($tmp = $this->getCFGDef('orderBy', '')) != '') {
                $out['orderBy'] = $tmp;
            } else {
                switch (true) {
                    case ('' != ($tmp = $this->getCFGDef('sortDir', ''))):
                    { //higher priority than order
                        $out['order'] = $tmp;
                    }
                    case ('' != ($tmp = $this->getCFGDef('order', ''))):
                    {
                        $out['order'] = $tmp;
                    }
                }
                if ('' == $out['order'] || !in_array(strtoupper($out['order']), array('ASC', 'DESC'))) {
                    $out['order'] = $orderDef; //Default
                }

                $out['sortBy'] = (($tmp = $this->getCFGDef('sortBy', '')) != '') ? $tmp : $sortName;
                $out['orderBy'] = $out['sortBy'] . " " . $out['order'];
            }
            $this->setConfig($out); //reload config;
            $sort = "ORDER BY " . $out['orderBy'];
            break;
            }
        }
        $this->debug->debugEnd("sortORDER",'Get sort order for SQL: '.$this->debug->dumpData($sort));
        return $sort;
    }

    /**
     * Получение LIMIT вставки в SQL запрос
     *
     * @return string LIMIT вставка в SQL запрос
     */
    final protected function LimitSQL($limit = 0, $offset = 0)
    {
        $this->debug->debug('', 'limitSQL', 2);
        $ret = '';
        if ($limit == 0) {
            $limit = $this->getCFGDef('display', 0);
        }
        if ($offset == 0) {
            $offset = $this->getCFGDef('offset', 0);
        }
        $offset += $this->getCFGDef('start', 0);
        $total = $this->getCFGDef('total', 0);
        if ($limit < ($total - $limit)) {
            $limit = $total - $offset;
        }

        if ($limit != 0) {
            $ret = "LIMIT " . (int)$offset . "," . (int)$limit;
        } else {
            if ($offset != 0) {
                /**
                 * To retrieve all rows from a certain offset up to the end of the result set, you can use some large number for the second parameter
                 * @see http://dev.mysql.com/doc/refman/5.0/en/select.html
                 */
                $ret = "LIMIT " . (int)$offset . ",18446744073709551615";
            }
        }
        $this->debug->debugEnd("limitSQL","Get limit for SQL: ".$this->debug->dumpData($ret));
        return $ret;
    }

    /**
     * Clean up the modx and html tags
     *
     * @param string $data String for cleaning
     * @return string Clear string
     */
    final public function sanitarData($data)
    {
        return is_scalar($data) ? str_replace(array('[', '%5B', ']', '%5D', '{', '%7B', '}', '%7D'), array('&#91;', '&#91;', '&#93;', '&#93;', '&#123;', '&#123;', '&#125;', '&#125;'), htmlspecialchars($data)) : '';
    }

    /**
     * run tree build
     *
     * @param string $idField default name id field
     * @param string $parentField default name parent field
     */
    final public function treeBuild($idField = 'id', $parentField = 'parent')
    {
        return $this->_treeBuild($this->_docs, $this->getCFGDef('idField', $idField), $this->getCFGDef('parentField', $parentField));
    }

    /**
     * @see: https://github.com/DmitryKoterov/DbSimple/blob/master/lib/DbSimple/Generic.php#L986
     *
     * @param array $data Associative data array
     * @param string $idName name ID field in associative data array
     * @param string $pidName name parent field in associative data array
     */
    final private function _treeBuild($data, $idName, $pidName)
    {
        $children = array(); // children of each ID
        $ids = array();
        foreach ($data as $i => $r) {
            $row =& $data[$i];
            $id = $row[$idName];
            $pid = $row[$pidName];
            $children[$pid][$id] =& $row;
            if (!isset($children[$id])) $children[$id] = array();
            $row['#childNodes'] =& $children[$id];
            $ids[$row[$idName]] = true;
        }
        // Root elements are elements with non-found PIDs.
        $this->_tree = array();
        foreach ($data as $i => $r) {
            $row =& $data[$i];
            if (!isset($ids[$row[$pidName]])&&(($row['hidemenu']==0&&$this->getCFGDef('checkHideMenu', '1')==1))) {
                $this->_tree[$row[$idName]] = $row;
            }
        }

        return $this->_tree;
    }

    /**
     * Получение PrimaryKey основной таблицы.
     * По умолчанию это id. Переопределить можно в контроллере присвоив другое значение переменной idField
     *
     * @return string PrimaryKey основной таблицы
     */
    public function getPK(){
        return isset($this->idField) ? $this->idField : 'id';
    }

    /**
     * Разбор фильтров
     * OR(AND(filter:field:operator:value;filter2:field:oerpator:value);(...)), etc.
     *
     * @param string $filter_string строка со всеми фильтрами
     * @return mixed результат разбора фильтров
     */
    protected function getFilters($filter_string){
        $this->debug->debug("getFilters: ".$this->debug->dumpData($filter_string),'getFilter',1);
        // the filter parameter tells us, which filters can be used in this query
        $filter_string = trim($filter_string);
        if (!$filter_string) return;
        $output = array('join' => '', 'where'=>'');
        $logic_op_found = false;
        foreach ($this->_logic_ops as $op => $sql){
            if (strpos($filter_string, $op) === 0){
                $logic_op_found = true;
                $subfilters = substr($filter_string, strlen($op)+1, -1);
                $subfilters = explode(';', $subfilters);
                foreach ($subfilters as $subfilter){
                    $subfilter = $this->getFilters(trim($subfilter));
                    if (!$subfilter) continue;
                    if ($subfilter['join']) $joins[] = $subfilter['join'];
                    if ($subfilter['where']) $wheres[] = $subfilter['where'];
                }
                $output['join'] = !empty($joins) ? implode(' ', $joins) : '';
                $output['where'] = !empty($wheres) ? '(' . implode($sql, $wheres) . ')' : '';
            }
        }

        if (!$logic_op_found) {
            $filter = $this->loadFilter($filter_string);
            if (!$filter) {
                $this->debug->warning('Error while loading DocLister filter "' . $this->debug->dumpData($filter_string) . '": check syntax!');
                $output = false;
            }else{
                $output['join'] = $filter->get_join();
                $output['where'] = $filter->get_where();
            }
        }
        $this->debug->debug('getFilter');
        return $output;
    }

    /**
     * Загрузка фильтра
     * @param string $filter срока с параметрами фильтрации
     * @return bool
     */
    protected function loadFilter($filter){
        $this->debug->debug('Load filter '.$this->debug->dumpData($filter) , 'loadFilter', 2);
        $out = false;
        $fltr_params = explode(':', $filter, 2);
        $fltr = isset($fltr_params[0]) ? $fltr_params[0] : null;
        // check if the filter is implemented
        if (!is_null($fltr) && file_exists(dirname(__FILE__) . '/filter/' . $fltr . '.filter.php')){
            require_once dirname(__FILE__) . '/filter/' . $fltr . '.filter.php';
            /**
             * @var tv_DL_filter|content_DL_filter $fltr_class
             */
            $fltr_class = $fltr . '_DL_filter';
            $this->totalFilters++;
            $fltr_obj = new $fltr_class();
            if($fltr_obj->init($this, $filter)){
                $out = $fltr_obj;
            }else{
                $this->debug->error("Wrong filter parameter: '{$this->debug->dumpData($filter)}'", 'Filter');
            }
        }else{
            $this->debug->error("Error load Filter: '{$this->debug->dumpData($filter)}'", 'Filter');
        }
        $this->debug->debugEnd("loadFilter");
        return $out;
    }

    /**
     * Общее число фильтров
     * @return int
     */
    public function getCountFilters(){
        return (int)$this->totalFilters;
    }
    /**
     * Выполнить SQL запрос
     * @param string $q SQL запрос
     */
    public function dbQuery($q){
        $this->debug->debug($this->debug->dumpData($q), "query", 1);
        $out = $this->modx->db->query($q);
        $this->debug->debugEnd("query");
        return $out;
    }

    /**
     * Экранирование строки в SQL запросе LIKE
     * @see: http://stackoverflow.com/a/3683868/2323306
     *
     * @param string $field поле по которому осуществляется поиск
     * @param string $value искомое значение
     * @param string $escape экранирующий символ
     * @param string $tpl шаблон подстановки значения в SQL запрос
     * @return string строка для подстановки в SQL запрос
     */
    public function LikeEscape($field, $value, $escape='=', $tpl='%[+value+]%'){
        $str = '';
        if(!empty($field) && is_string($field) && is_scalar($value) && $value!==''){
            if(is_scalar($escape) && !empty($escape) && !in_array($escape,array("_", "%", "'"))){
                $str = str_replace(array($escape, '_', '%'), array($escape.$escape, $escape.'_', $escape.'%'), $value);
                $str = $this->modx->db->escape($str);
                $str = str_replace('[+value+]', $str, $tpl);
                $str = "{$field} LIKE '{$str}' ESCAPE '{$escape}'";
            }else{
                $this->debug->error("Error LikeEscape escaping: '{$this->debug->dumpData($escape)}'", 'LikeEscape');
            }
        }else{
            $this->debug->error("Error LikeEscape parameters. Field: '{$this->debug->dumpData($field)}' or value: '{$this->debug->dumpData($value)}'", 'LikeEscape');
        }
        return $str;
    }

    /**
     * Получение REQUEST_URI без GET-ключа с
     * @return string
     */
    final public function getRequest(){
        $URL = null;
        parse_str(parse_url(MODX_SITE_URL.$_SERVER['REQUEST_URI'], PHP_URL_QUERY), $URL);
        return http_build_query(array_merge($URL, array(DocLister::AliasRequest => null)));
    }
}