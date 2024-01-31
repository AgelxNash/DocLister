<?php
/**
 * DocLister class
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 */
include_once(MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');
include_once(MODX_BASE_PATH . 'assets/lib/Helpers/FS.php');
include_once(MODX_BASE_PATH . 'assets/lib/Helpers/Config.php');
include_once(MODX_BASE_PATH . 'assets/lib/Helpers/Lexicon.php');
include_once(MODX_BASE_PATH . 'assets/lib/Helpers/LexiconHandlers/AbstractLexiconHandler.php');
include_once(MODX_BASE_PATH . 'assets/lib/Helpers/LexiconHandlers/EvoBabelLexiconHandler.php');
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/jsonHelper.class.php');
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/sqlHelper.class.php');
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php');
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLCollection.class.php');
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/xnop.class.php');

/**
 * Class DocLister
 */
abstract class DocLister
{
    /**
     * Ключ в массиве $_REQUEST в котором находится алиас запрашиваемого документа
     */
    const AliasRequest = 'q';
    /**
     * Массив документов полученный в результате выборки из базы
     * @var array
     * @access protected
     */
    protected $_docs = [];

    /**
     * Массив документов self::$_docs собранный в виде дерева
     * @var array
     * @access protected
     */
    protected $_tree = [];

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
    protected $modx;

    /**
     * Шаблонизатор чанков
     * @var DLTemplate
     * @access protected
     */
    protected $DLTemplate;

    /**
     * Массив загруженных экстендеров
     * @var array
     * @access protected
     */
    protected $extender = [];

    /**
     * Массив плейсхолдеров доступных в шаблоне
     * @var array
     * @access protected
     */
    protected $_plh = [];

    /**
     * Список таблиц уже с префиксами MODX
     * @var array
     * @access private
     */
    private $_table = [];

    /**
     * PrimaryKey основной таблицы
     * @var string
     * @access protected
     */
    protected $idField = 'id';

    /**
     * Parent Key основной таблицы
     * @var string
     * @access protected
     */
    protected $parentField = 'parent';

    /**
     * Дополнительные условия для SQL запросов
     * @var array
     * @access protected
     */
    protected $_filters = ['where' => '', 'join' => ''];

    /**
     * Список доступных логических операторов для фильтрации
     * @var array
     * @access protected
     */
    protected $_logic_ops = ['AND' => ' AND ', 'OR' => ' OR ']; // logic operators currently supported

    /**
     * Режим отладки
     * @var int
     * @access private
     */
    private $_debugMode = 0;

    /**
     * Отладчик
     *
     * @var DLdebug|xNop
     * @access public
     */
    public $debug;

    /**
     * Массив дополнительно подключаемых таблиц с псевдонимами
     * @var array
     */
    public $AddTable = [];

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

    /** @var string имя шаблона для вывода записи */
    public $renderTPL = '';

    /** @var string имя шаблона обертки для записей */
    public $ownerTPL = '';

    /** @var \Helpers\FS */
    public $FS;
    /** @var string результатирующая строка которая была последний раз сгенирирована
     *               вызовами методов DocLister::render и DocLister::getJSON
     */
    protected $outData = '';

    /** @var int Число документов, которые были отфильтрованы через prepare при выводе */
    public $skippedDocs = 0;

    /** @var string Имя таблицы */
    protected $table = '';
    /** @var string alias таблицы */
    protected $alias = '';

    /** @var null|paginate_DL_Extender */
    protected $extPaginate;

    /** @var null|Helpers\Config */
    public $config;

    /**
     * @var cache_DL_Extender
     */
    protected $extCache;

    protected $lexicon;

    /**
     * Конструктор контроллеров DocLister
     *
     * @param  DocumentParser  $modx  объект DocumentParser - основной класс MODX
     * @param  mixed  $cfg  массив параметров сниппета
     * @param  int  $startTime  время запуска сниппета
     * @throws Exception
     */
    public function __construct($modx, $cfg = [], $startTime = null)
    {
        $this->setTimeStart($startTime);

        if (extension_loaded('mbstring')) {
            mb_internal_encoding("UTF-8");
        } else {
            throw new Exception('Not found php extension mbstring');
        }

        if ($modx instanceof DocumentParser) {
            $this->modx = $modx;
            $this->setDebug(1);

            if (!is_array($cfg) || empty($cfg)) {
                $cfg = $this->modx->Event->params;
            }
        } else {
            throw new Exception('MODX var is not instaceof DocumentParser');
        }

        $this->FS = \Helpers\FS::getInstance();
        $this->config = new \Helpers\Config($cfg);

        if (isset($cfg['config'])) {
            $this->config->setPath(dirname(__DIR__))->loadConfig($cfg['config']);
        }

        if ($this->config->setConfig($cfg) === false) {
            throw new Exception('no parameters to run DocLister');
        }
        $this->lexicon = new \Helpers\Lexicon($this->modx, [
            'langDir' => 'assets/snippets/DocLister/core/lang/',
            'lang'    => $this->getCFGDef('lang', $this->modx->getConfig('lang_code')),
            'handler' => $this->getCFGDef('lexiconHandler', '\\Helpers\\Lexicon\\EvoBabelLexiconHandler')
        ]);

        $this->loadLang(['core', 'json']);
        $this->setDebug($this->getCFGDef('debug', 0));

        if ($this->checkDL()) {
            $cfg = [];
            $idType = $this->getCFGDef('idType', '');
            if (empty($idType) && $this->getCFGDef('documents', '') != '') {
                $idType = 'documents';
            }
            switch ($idType) {
                case 'documents':
                    $IDs = $this->getCFGDef('documents');
                    $cfg['idType'] = "documents";
                    break;
                case 'parents':
                default:
                    $cfg['idType'] = "parents";
                    if (($IDs = $this->getCFGDef('parents', '')) === '') {
                        $IDs = $cfg['parents'] = $this->getCurrentMODXPageID();
                    }
                    break;
            }
            $this->config->setConfig($cfg);
            $this->alias = empty($this->alias) ? $this->getCFGDef(
                'tableAlias',
                'c'
            ) : $this->alias;
            $this->table = $this->getTable(empty($this->table) ? $this->getCFGDef(
                'table',
                'site_content'
            ) : $this->table, $this->alias);

            $this->idField = $this->getCFGDef('idField', $this->idField);
            $this->parentField = $this->getCFGDef('parentField', $this->parentField);
            $this->extCache = $this->getExtender('cache', true);
            $this->extCache->init($this, [
                'cache'         => $this->getCFGDef('cache', 1),
                'cacheKey'      => $this->getCFGDef('cacheKey'),
                'cacheLifetime' => $this->getCFGDef('cacheLifetime', 0),
                'cacheStrategy' => $this->getCFGDef('cacheStrategy')
            ]);
            $this->setIDs($IDs);
        }

        $this->setLocate();

        $this->getCustomLang($this->getCFGDef('customLang', $this->getCFGDef('lexicon')));

        $this->loadExtender($this->getCFGDef("extender", ""));

        if ($this->checkExtender('request')) {
            $this->extender['request']->init($this, $this->getCFGDef("requestActive", ""));
        }
        $this->_filters = $this->getFilters($this->getCFGDef('filters', ''));
        $this->ownerTPL = $this->getCFGDef("ownerTPL", "");
        $DLTemplate = DLTemplate::getInstance($modx);
        if ($path = $this->getCFGDef('templatePath')) {
            $DLTemplate->setTemplatePath($path);
        }
        if ($ext = $this->getCFGDef('templateExtension')) {
            $DLTemplate->setTemplateExtension($ext);
        }
        $this->DLTemplate = $DLTemplate->setTemplateData(['DocLister' => $this]);
    }

    /**
     * Разбиение фильтра на субфильтры с учётом вложенности
     * @param  string  $str  строка с фильтром
     * @return array массив субфильтров
     */
    public function smartSplit($str)
    {
        $res = [];
        $cur = '';
        $open = 0;
        $strlen = mb_strlen($str, 'UTF-8');
        for ($i = 0; $i <= $strlen; $i++) {
            $e = mb_substr($str, $i, 1, 'UTF-8');
            switch ($e) {
                case '\\':
                    $cur .= $e;
                    $cur .= mb_substr($str, ++$i, 1, 'UTF-8');
                    break;
                case ')':
                    $open--;
                    if ($open === 0) {
                        $res[] = $cur . ')';
                        $cur = '';
                    } else {
                        $cur .= $e;
                    }
                    break;
                case '(':
                    $open++;
                    $cur .= $e;
                    break;
                case ';':
                    if ($open === 0) {
                        $res[] = $cur;
                        $cur = '';
                    } else {
                        $cur .= $e;
                    }
                    break;
                default:
                    $cur .= $e;
            }
        }
        $cur = preg_replace("/(\))$/u", '', $cur);
        if ($cur !== '') {
            $res[] = $cur;
        }

        return array_reverse($res);
    }

    /**
     * Трансформация объекта в строку
     * @return string последний ответ от DocLister'а
     */
    public function __toString()
    {
        return $this->outData;
    }

    /**
     * Установить время запуска сниппета
     * @param  float|null  $time
     */
    public function setTimeStart($time = null)
    {
        $this->_timeStart = is_null($time) ? microtime(true) : $time;
    }

    /**
     * Время запуска сниппета
     *
     * @return int
     */
    public function getTimeStart()
    {
        return $this->_timeStart;
    }

    /**
     * Установка режима отладки
     * @param  int  $flag  режим отладки
     */
    public function setDebug($flag = 0)
    {
        $flag = abs((int) $flag);
        if ($this->_debugMode != $flag) {
            $this->_debugMode = $flag;
            $this->debug = null;
            if ($this->_debugMode > 0) {
                if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 'manager') {
                    error_reporting(E_ALL ^ E_NOTICE);
                    ini_set('display_errors', 1);
                }
                $dir = dirname(dirname(__FILE__));
                if (file_exists($dir . "/lib/DLdebug.class.php")) {
                    include_once($dir . "/lib/DLdebug.class.php");
                    if (class_exists("DLdebug", false)) {
                        $this->debug = new DLdebug($this);
                    }
                }
            }

            if (is_null($this->debug)) {
                $this->debug = new xNop();
                $this->_debugMode = 0;
            }
        }
    }

    /**
     * Информация о режиме отладки
     */
    public function getDebug()
    {
        return $this->_debugMode;
    }

    /**
     * Генерация имени таблицы с префиксом и алиасом
     *
     * @param  string  $name  имя таблицы
     * @param  string  $alias  желаемый алиас таблицы
     * @return string имя таблицы с префиксом и алиасом
     */
    public function getTable($name, $alias = '')
    {
        if (!isset($this->_table[$name])) {
            $this->_table[$name] = $this->modx->getFullTableName($name);
        }
        $table = $this->_table[$name];
        if (!empty($alias) && is_scalar($alias)) {
            $table .= " as `" . $alias . "`";
        }

        return $table;
    }

    /**
     * @param $name
     * @param $table
     * @param $alias
     * @return mixed
     */
    public function TableAlias($name, $table, $alias)
    {
        if (!$this->checkTableAlias($name, $table)) {
            $this->AddTable[$table][$name] = $alias;
        }

        return $this->AddTable[$table][$name];
    }

    /**
     * @param $name
     * @param $table
     * @return bool
     */
    public function checkTableAlias($name, $table)
    {
        return isset($this->AddTable[$table][$name]);
    }

    /**
     * Разбор JSON строки при помощи json_decode
     *
     * @param $json string строка c JSON
     * @param  array  $config  ассоциативный массив с настройками для json_decode
     * @param  bool  $nop  создавать ли пустой объект запрашиваемого типа
     * @return array|mixed|xNop
     */
    public function jsonDecode($json, $config = [], $nop = false)
    {
        $this->debug->debug(
            'Decode JSON: ' . $this->debug->dumpData($json) . "\r\nwith config: " . $this->debug->dumpData($config),
            'jsonDecode',
            2
        );
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
    public function isErrorJSON($json)
    {
        $error = jsonHelper::json_last_error_msg();
        if (!in_array($error, ['error_none', 'other'])) {
            $this->debug->error($this->getMsg('json.' . $error) . ": " . $this->debug->dumpData($json, 'code'), 'JSON');
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
        $tmp = $this->getCFGDef('requestActive', '') != '' || in_array('request', $extenders);
        if ($tmp && !$this->_loadExtender('request')) {
            //OR request in extender's parameter
            throw new Exception('Error load request extender');
        }

        $tmp = $this->getCFGDef('summary', '') != '' || in_array('summary', $extenders);
        if ($tmp && !$this->_loadExtender('summary')) {
            //OR summary in extender's parameter
            throw new Exception('Error load summary extender');
        }

        if ((int) $this->getCFGDef('display', 0) > 0 && ( //OR paginate in extender's parameter
                in_array('paginate', $extenders) || $this->getCFGDef('paginate', '') != '' ||
                $this->getCFGDef('TplPrevP', '') != '' || $this->getCFGDef('TplPage', '') != '' ||
                $this->getCFGDef('TplCurrentPage', '') != '' || $this->getCFGDef('TplWrapPaginate', '') != '' ||
                $this->getCFGDef('pageLimit', '') != '' || $this->getCFGDef('pageAdjacents', '') != '' ||
                $this->getCFGDef('PaginateClass', '') != '' || $this->getCFGDef('TplNextP', '') != ''
            ) && !$this->_loadExtender('paginate')
        ) {
            throw new Exception('Error load paginate extender');
        } else {
            if ((int) $this->getCFGDef('display', 0) == 0) {
                $extenders = $this->unsetArrayVal($extenders, 'paginate');
            }
        }

        if ($this->getCFGDef('prepare', '') != '' || $this->getCFGDef('prepareWrap') != '') {
            $this->_loadExtender('prepare');
        }

        $this->config->setConfig(['extender' => implode(",", $extenders)]);
        $this->debug->debugEnd("checkDL");

        return $flag;
    }

    /**
     * Удаление определенных данных из массива
     *
     * @param  array  $data  массив с данными
     * @param  mixed  $val  значение которые необходимо удалить из массива
     * @return array отчищеный массив с данными
     */
    private function unsetArrayVal($data, $val)
    {
        $out = [];
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
     * @param  int  $id  уникальный идентификатор страницы
     * @return string URL страницы
     */
    public function getUrl($id = 0)
    {
        $id = ((int) $id > 0) ? (int) $id : $this->getCurrentMODXPageID();

        $link = $this->checkExtender('request') ? $this->extender['request']->getLink() : $this->getRequest();
        if ($id == $this->modx->config['site_start']) {
            $url = $this->modx->config['site_url'] . ($link != '' ? "?{$link}" : "");
        } else {
            $url = $this->modx->makeUrl($id, '', $link, $this->getCFGDef('urlScheme', ''));
        }

        return $url;
    }

    /**
     * Получение массива документов из базы
     * @param  mixed  $tvlist  дополнительные параметры выборки
     * @return array Массив документов выбранных из базы
     */
    abstract public function getDocs($tvlist = '');

    /**
     * Подготовка результатов к отображению.
     *
     * @param  string  $tpl  шаблон
     * @return mixed подготовленный к отображению результат выборки
     */
    abstract public function _render($tpl = '');

    /**
     * Подготовка результатов к отображению в соответствии с настройками
     *
     * @param  string  $tpl  шаблон
     * @return string
     */
    public function render($tpl = '')
    {
        if ($this->extPaginate === null) {
            $this->toPlaceholders(count($this->_docs), 1, 'count');
        }

        $this->debug->debug(['Render data with template ' => $tpl], 'render', 2, ['html']);
        $out = '';
        if (1 == $this->getCFGDef('tree', '0')) {
            foreach ($this->_tree as $item) {
                $out .= $this->renderTree($item);
            }
            $out = $this->renderWrap($out);
        } else {
            $out = $this->_render($tpl);
        }

        if ($out) {
            $this->outData = $this->parseSource($out);
        }
        $this->debug->debugEnd('render');

        return $this->outData;
    }

    /**
     * @param  string  $out
     * @return string
     */
    public function parseSource($out = '')
    {
        if ($this->getCFGDef('parseDocumentSource', 1)) {
            $out = DLTemplate::getInstance($this->modx)->parseDocumentSource($out);
        }

        return $out;
    }

    /***************************************************
     ****************** CORE Block *********************
     ***************************************************/

    /**
     * Определение ID страницы открытой во фронте
     *
     * @return int
     */
    public function getCurrentMODXPageID()
    {
        $id = isset($this->modx->documentIdentifier) ? (int) $this->modx->documentIdentifier : 0;
        $docData = isset($this->modx->documentObject) ? $this->modx->documentObject : [];

        return empty($id) ? \APIHelpers::getkey($docData, 'id', 0) : $id;
    }

    /**
     * Display and save error information
     *
     * @param  string  $message  error message
     * @param  integer  $code  error number
     * @param  string  $file  error on file
     * @param  integer  $line  error on line
     * @param  array  $trace  stack trace
     */
    public function ErrorLogger($message, $code, $file, $line, $trace)
    {
        if (abs($this->getCFGDef('debug', '0')) == '1') {
            $out = "CODE #" . $code . "<br />";
            $out .= "on file: " . $file . ":" . $line . "<br />";
            $out .= "<pre>";
            $out .= print_r($trace, 1);
            $out .= "</pre>";

            $message = $out . $message;
        }
        die($message);
    }

    /**
     * Получение объекта DocumentParser
     *
     * @return DocumentParser
     */
    public function getMODX()
    {
        return $this->modx;
    }

    /**
     * load extenders
     *
     * @param  string  $ext  name extender separated by ,
     * @return boolean status load extenders
     * @throws Exception
     */
    public function loadExtender($ext = '')
    {
        $out = true;
        if ($ext != '') {
            $ext = explode(",", $ext);
            foreach ($ext as $item) {
                if ($item != '' && !$this->_loadExtender($item)) {
                    throw new Exception('Error load ' . APIHelpers::e($item) . ' extender');
                }
            }
        }

        return $out;
    }

    /**
     * Получение информации из конфига
     *
     * @param  string  $name  имя параметра в конфиге
     * @param  mixed  $def  значение по умолчанию, если в конфиге нет искомого параметра
     * @return mixed значение из конфига
     */
    public function getCFGDef($name, $def = null)
    {
        $out = $this->config->getCFGDef($name, $def);
        if ($name == 'dateFormat' && !empty($out) && is_scalar($out) && strpos($out, '%') !== false) {
            $replace = [
                '%d' => 'd', '%a' => 'D', '%e' => 'j', '%A' => 'l', '%u' => 'N', '%w' => 'w', '%j' => 'z',
                '%V' => 'W',
                '%B' => 'F', '%m' => 'm', '%b' => 'M',
                '%G' => 'o', '%Y' => 'Y', '%y' => 'y',
                '%P' => 'a', '%p' => 'A', '%l' => 'g', '%I' => 'h', '%H' => 'H', '%M' => 'i', '%S' => 's',
                '%z' => 'O', '%Z' => 'T',
                '%s' => 'U'
            ];
            $out = strtr($out, $replace);
        }

        return $out;
    }

    /**
     * Сохранение данных в массив плейсхолдеров
     *
     * @param  mixed  $data  данные
     * @param  int  $set  устанавливать ли глобальнй плейсхолдер MODX
     * @param  string  $key  ключ локального плейсхолдера
     * @return string
     */
    public function toPlaceholders($data, $set = 0, $key = 'contentPlaceholder')
    {
        $this->debug->debug(null, 'toPlaceholders', 2);
        if ($set == 0) {
            $set = $this->getCFGDef('contentPlaceholder', 0);
        }
        $this->_plh[$key] = $data;
        $id = $this->getCFGDef('id', '');
        if ($id != '') {
            $id .= ".";
        }
        $out = DLTemplate::getInstance($this->getMODX())->toPlaceholders($data, $set, $key, $id);

        $this->debug->debugEnd(
            "toPlaceholders",
            [$key . " placeholder" => $data],
            ['html']
        );

        return $out;
    }

    /**
     * Предварительная обработка данных перед вставкой в SQL запрос вида IN
     * Если данные в виде строки, то происходит попытка сформировать массив из этой строки по разделителю $sep
     * Точно по тому, по которому потом данные будут собраны обратно
     *
     * @param  integer|string|array  $data  данные для обработки
     * @param  string  $sep  разделитель
     * @param  boolean  $quote  заключать ли данные на выходе в кавычки
     * @return string обработанная строка
     */
    public function sanitarIn($data, $sep = ',', $quote = true)
    {
        return APIhelpers::sanitarIn($data, $sep, $quote);
    }

    /**
     * Загрузка кастомного лексикона
     *
     * В файле с кастомным лексиконом ключи в массиве дожны быть полные
     * Например:
     *      - core.bla-bla
     *      - paginate.next
     *
     * @param  string  $lang  имя языкового пакета
     * @return array
     */
    public function getCustomLang($lang = '')
    {
        $langDir = $this->getCFGDef('langDir', 'assets/snippets/DocLister/lang/');

        return $this->lexicon->fromFile($lang, '', $langDir);
    }

    /**
     * Загрузка языкового пакета
     *
     * @param  array|string  $name  ключ языкового пакета
     * @param  string  $lang  имя языкового пакета
     * @return array массив с лексиконом
     */
    public function loadLang($name = 'core', $lang = '')
    {
        $this->debug->debug(
            'Load language ' . $this->debug->dumpData($name) . "." . $this->debug->dumpData($lang),
            'loadlang',
            2
        );
        $this->debug->debugEnd("loadlang");

        return $this->lexicon->fromFile($name, $lang);
    }

    /**
     * Получение строки из языкового пакета
     *
     * @param  string  $name  имя записи в языковом пакете
     * @param  string  $def  Строка по умолчанию, если запись в языковом пакете не будет обнаружена
     * @return string строка в соответствии с текущими языковыми настройками
     */
    public function getMsg($name, $def = '')
    {
        return $this->lexicon->get($name, $def);
    }

    /**
     * Переменовывание элементов массива
     *
     * @param  array  $data  массив с данными
     * @param  string  $prefix  префикс ключей
     * @param  string  $suffix  суффикс ключей
     * @param  string  $sep  разделитель суффиксов, префиксов и ключей массива
     * @return array массив с переименованными ключами
     */
    public function renameKeyArr($data, $prefix = '', $suffix = '', $sep = '.')
    {
        return \APIHelpers::renameKeyArr($data, $prefix, $suffix, $sep);
    }

    /**
     * Установка локали
     *
     * @param  string  $locale  локаль
     * @return string имя установленной локали
     */
    public function setLocate($locale = '')
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
     * @param  array  $data  массив сформированный как дерево
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

        $data[$this->getCFGDef("sysKey", "dl") . ".wrap"] = $this->renderWrap($out);
        $out = $this->parseChunk($this->getCFGDef('tpl', ''), $data);

        return $out;
    }

    /**
     * refactor $modx->getChunk();
     *
     * @param  string  $name  Template: chunk name || @CODE: template || @FILE: file with template
     * @return string html template with placeholders without data
     */
    private function _getChunk($name)
    {
        $this->debug->debug(['Get chunk by name' => $name], "getChunk", 2, ['html']);
        //without trim
        $tpl = DLTemplate::getInstance($this->getMODX())->getChunk($name);
        $tpl = $this->parseLang($tpl);

        $this->debug->debugEnd("getChunk");

        return $tpl;
    }

    /**
     * Замена в шаблоне фраз из лексикона
     *
     * @param  string  $tpl  HTML шаблон
     * @return string
     */
    public function parseLang($tpl)
    {
        $this->debug->debug(["parseLang" => $tpl], "parseLang", 2, ['html']);
        $tpl = $this->lexicon->parse($tpl);
        $this->debug->debugEnd("parseLang");

        return $tpl;
    }

    /**
     * refactor $modx->parseChunk();
     *
     * @param  string  $name  Template: chunk name || @CODE: template || @FILE: file with template
     * @param  array  $data  paceholder
     * @param  bool  $parseDocumentSource  render html template via DocumentParser::parseDocumentSource()
     * @return string html template with data without placeholders
     */
    public function parseChunk($name, $data = [], $parseDocumentSource = false)
    {
        $this->debug->debug(
            ["parseChunk" => $name, "With data" => print_r($data, 1)],
            "parseChunk",
            2,
            ['html', null]
        );
        $disablePHx = $this->getCFGDef('disablePHx', 0);
        $out = $this->DLTemplate->parseChunk($name, $data, $parseDocumentSource, (bool) $disablePHx);
        $out = $this->parseLang($out);
        if (empty($out)) {
            $this->debug->debug("Empty chunk: " . $this->debug->dumpData($name), '', 2);
        }
        $this->debug->debugEnd("parseChunk");

        return $out;
    }

    /**
     * Get full template from parameter name
     *
     * @param  string  $name  param name
     * @param  string  $val  default value
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
     * Помещение html кода в какой-то блок обертку
     *
     * @param  string  $data  html код который нужно обернуть в ownerTPL
     * @return string результатирующий html код
     */
    public function renderWrap($data)
    {
        $out = $data;
        $docs = count($this->_docs) - $this->skippedDocs;
        $wrap = $this->getCFGDef('prepareWrap');
        if ((($this->getCFGDef("noneWrapOuter",
                        "1") && $docs == 0) || $docs > 0) && !empty($this->ownerTPL) || !empty($wrap)) {
            $this->debug->debug("", "renderWrapTPL", 2);
            $parse = true;
            $plh = [$this->getCFGDef("sysKey", "dl") . ".wrap" => $data];
            /**
             * @var $extPrepare prepare_DL_Extender
             */
            $extPrepare = $this->getExtender('prepare');
            if ($extPrepare) {
                $params = $extPrepare->init($this, [
                    'data'      => [
                        'docs'         => $this->_docs,
                        'placeholders' => $plh
                    ],
                    'nameParam' => 'prepareWrap',
                    'return'    => 'placeholders'
                ]);
                if ($params === false) {
                    $out = $data;
                    $parse = false;
                }
                $plh = $params;
            }
            if ($parse && !empty($this->ownerTPL)) {
                $this->debug->updateMessage(
                    ["render ownerTPL" => $this->ownerTPL, "With data" => print_r($plh, 1)],
                    "renderWrapTPL",
                    ['html', null]
                );
                $out = $this->parseChunk($this->ownerTPL, $plh);
            }
            if (empty($this->ownerTPL)) {
                $this->debug->updateMessage("empty ownerTPL", "renderWrapTPL");
            }
            $this->debug->debugEnd("renderWrapTPL");
        }

        return $out;
    }

    /**
     * Единые обработки массива с данными о документе для всех контроллеров
     *
     * @param  array  $data  массив с данными о текущем документе
     * @param  int  $i  номер итерации в цикле
     * @return array массив с данными которые можно использовать в цикле render метода
     */
    protected function uniformPrepare(&$data, $i = 0)
    {
        $class = [];

        $iterationName = ($i % 2 == 1) ? 'Odd' : 'Even';
        $tmp = strtolower($iterationName);
        $class[] = $this->getCFGDef($tmp . 'Class', $tmp);

        $this->renderTPL = $this->getCFGDef('tplId' . $i, $this->renderTPL);
        $this->renderTPL = $this->getCFGDef('tpl' . $iterationName, $this->renderTPL);
        $iteration = $i;

        if ($this->extPaginate) {
            $offset = $this->getCFGDef(
                'reversePagination',
                0
            ) && $this->extPaginate->currentPage() > 1 ? $this->extPaginate->totalPage() * $this->getCFGDef(
                    'display',
                    0
                ) - $this->extPaginate->totalDocs() : 0;
            if ($this->getCFGDef('maxDocs', 0) && !$this->getCFGDef(
                    'reversePagination',
                    0
                ) && $this->extPaginate->currentPage() == $this->extPaginate->totalPage()
            ) {
                $iteration += $this->getCFGDef('display', 0);
            }
            $iteration += $this->getCFGDef(
                    'display',
                    0
                ) * ($this->extPaginate->currentPage() - 1) - $offset;
        }

        $data[$this->getCFGDef(
            "sysKey",
            "dl"
        ) . '.full_iteration'] = $iteration;

        if ($i == 1) {
            $this->renderTPL = $this->getCFGDef('tplFirst', $this->renderTPL);
            $class[] = $this->getCFGDef('firstClass', 'first');
            $data[$this->getCFGDef("sysKey", "dl") . '.is_first'] = 1;
        } else {
            $data[$this->getCFGDef("sysKey", "dl") . '.is_first'] = 0;
        }

        if ($i == (count($this->_docs) - $this->skippedDocs)) {
            $this->renderTPL = $this->getCFGDef('tplLast', $this->renderTPL);
            $class[] = $this->getCFGDef('lastClass', 'last');
            $data[$this->getCFGDef("sysKey", "dl") . '.is_last'] = 1;
        } else {
            $data[$this->getCFGDef("sysKey", "dl") . '.is_last'] = 0;
        }

        if (array_key_exists('id', $data) && $this->modx->documentIdentifier == $data['id']) {
            $this->renderTPL = $this->getCFGDef('tplCurrent', $this->renderTPL);
            $data[$this->getCFGDef(
                "sysKey",
                "dl"
            ) . '.active'] = 1; //[+active+] - 1 if $modx->documentIdentifer equal ID this element
            $class[] = $this->getCFGDef('currentClass', 'current');
        } else {
            $data[$this->getCFGDef("sysKey", "dl") . '.active'] = 0;
        }

        $class = implode(" ", $class);
        $data[$this->getCFGDef("sysKey", "dl") . '.class'] = $class;

        /**
         * @var $extE e_DL_Extender
         */
        $extE = $this->getExtender('e', true, true);
        if ($out = $extE->init($this, compact('data'))) {
            if (is_array($out)) {
                $data = $out;
            }
        }

        return compact('class', 'iterationName');
    }

    /**
     * Формирование JSON ответа
     *
     * @param  array  $data  массив данных которые подготавливаются к выводу в JSON
     * @param  mixed  $fields  список полей учавствующих в JSON ответе. может быть либо массив, либо строка с разделителем , (запятая)
     * @param  array  $array  данные которые необходимо примешать к ответу на каждой записи $data
     * @return string JSON строка
     */
    public function getJSON($data, $fields, $array = [])
    {
        $out = [];
        $fields = is_array($fields) ? $fields : explode(",", $fields);
        if (is_array($array) && count($array) > 0) {
            $tmp = [];
            foreach ($data as $i => $v) { //array_merge not valid work with integer index key
                $tmp[$i] = (isset($array[$i]) ? array_merge($v, $array[$i]) : $v);
            }
            $data = $tmp;
        }

        foreach ($data as $num => $doc) {
            $tmp = [];
            foreach ($doc as $name => $value) {
                if (in_array($name, $fields) || (isset($fields[0]) && $fields[0] == '1')) {
                    $tmp[str_replace(".", "_", $name)] = $value; //JSON element name without dot
                }
            }
            $out[$num] = $tmp;
        }

        $format = $this->getCFGDef('JSONformat', 'old');
        switch ($format) {
            case 'new':
                $return['rows'] = array_values($out);
                $return['total'] = $this->getChildrenCount();
                break;
            case 'simple':
                $return = array_values($out);
                break;
            default:
                $return = $out;
                break;
        }
        $this->outData = json_encode($return);
        $this->isErrorJSON($return);

        return $this->getCFGDef('debug') ? jsonHelper::json_format($this->outData) : $this->outData;
    }

    /**
     * @param  array  $item
     * @param  null  $extSummary
     * @param  string  $introField
     * @param  string  $contentField
     * @return mixed|string
     */
    protected function getSummary(array $item = [], $extSummary = null, $introField = '', $contentField = '')
    {
        $out = '';

        if (is_null($extSummary)) {
            /**
             * @var $extSummary summary_DL_Extender
             */
            $extSummary = $this->getExtender('summary');
        }
        $introField = $this->getCFGDef("introField", $introField);
        $contentField = $this->getCFGDef("contentField", $contentField);

        if (!empty($introField) && !empty($item[$introField]) && mb_strlen($item[$introField], 'UTF-8') > 0) {
            $out = $item[$introField];
        } else {
            if (!empty($contentField) && !empty($item[$contentField]) && mb_strlen($item[$contentField], 'UTF-8') > 0) {
                $out = $extSummary->init($this, [
                    "content"      => $item[$contentField],
                    "action"       => $this->getCFGDef("summary", ""),
                    "cutSummary"   => $this->getCFGDef('cutSummary'),
                    "dotSummary"   => $this->getCFGDef('dotSummary'),
                    'breakSummary' => $this->getCFGDef('breakSummary')
                ]);
            }
        }

        return $out;
    }

    /**
     * @param  string  $name  extender name
     * @return boolean status extender load
     */
    public function checkExtender($name)
    {
        return (isset($this->extender[$name]) && $this->extender[$name] instanceof $name . "_DL_Extender");
    }

    /**
     * @param $name
     * @param $obj
     */
    public function setExtender($name, $obj)
    {
        $this->extender[$name] = $obj;
    }

    /**
     * Вытащить экземпляр класса экстендера из общего массива экстендеров
     *
     * @param  string  $name  имя экстендера
     * @param  bool  $autoload  Если экстендер не загружен, то пытаться ли его загрузить
     * @param  bool  $nop  если экстендер не загружен, то загружать ли xNop
     * @return null|xNop
     */
    public function getExtender($name, $autoload = false, $nop = false)
    {
        $out = null;
        if ((is_scalar($name) && $this->checkExtender($name)) || ($autoload && $this->_loadExtender($name))) {
            $out = $this->extender[$name];
        }
        if ($nop && is_null($out)) {
            $out = new xNop();
        }

        return $out;
    }

    /**
     * load extender
     *
     * @param  string  $name  name extender
     * @return boolean $flag status load extender
     */
    protected function _loadExtender($name)
    {
        $this->debug->debug('Load Extender ' . $this->debug->dumpData($name), 'LoadExtender', 2);
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
                $this->extender[$name] = new $classname($this, $name);
                $flag = true;
            }
        }
        if (!$flag) {
            $this->debug->debug("Error load Extender " . $this->debug->dumpData($name));
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
     * @param  mixed  $IDs  список id документов по которым необходима выборка
     * @return array очищенный массив
     */
    public function setIDs($IDs)
    {
        $this->debug->debug('set ID list ' . $this->debug->dumpData($IDs), 'setIDs', 2);
        $IDs = $this->cleanIDs($IDs);
        $type = $this->getCFGDef('idType', 'parents');
        $depth = $this->getCFGDef('depth', '');
        if ($type == 'parents' && $depth > 0) {
            $out = $this->extCache->load('children');
            if ($out === false) {
                $tmp = $IDs;
                do {
                    if (count($tmp) > 0) {
                        $tmp = $this->getChildrenFolder($tmp);
                        $IDs = array_merge($IDs, $tmp);
                    }
                } while ((--$depth) > 0);
                $this->extCache->save($IDs, 'children');
            } else {
                $IDs = $out;
            }
        }
        $this->debug->debugEnd("setIDs");

        return ($this->IDs = $IDs);
    }

    /**
     * @return int
     */
    public function getIDs()
    {
        return $this->IDs;
    }

    /**
     * Очистка данных и уникализация списка цифр.
     * Если был $IDs был передан как строка, то эта строка будет преобразована в массив по разделителю $sep
     * @param  mixed  $IDs  данные для обработки
     * @param  string  $sep  разделитель
     * @return array очищенный массив с данными
     */
    public function cleanIDs($IDs, $sep = ',')
    {
        $this->debug->debug(
            'clean IDs ' . $this->debug->dumpData($IDs) . ' with separator ' . $this->debug->dumpData($sep),
            'cleanIDs',
            2
        );
        $out = [];
        if (!is_array($IDs)) {
            $IDs = explode($sep, $IDs ?? '');
        }
        foreach ($IDs as $item) {
            $item = trim($item);
            if (is_numeric($item) && (int) $item >= 0) { //Fix 0xfffffffff
                $out[] = (int) $item;
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
    protected function checkIDs()
    {
        return (is_array($this->IDs) && count($this->IDs) > 0) ? true : false;
    }

    /**
     * Get all field values from array documents
     *
     * @param  string  $userField  field name
     * @param  boolean  $uniq  Only unique values
     * @return array all field values
     * @global array $_docs all documents
     */
    public function getOneField($userField, $uniq = false)
    {
        $out = [];
        foreach ($this->_docs as $doc => $val) {
            if (isset($val[$userField]) && (($uniq && !in_array($val[$userField], $out)) || !$uniq)) {
                $out[$doc] = $val[$userField];
            }
        }

        return $out;
    }

    /**
     * @return DLCollection
     */
    public function docsCollection()
    {
        return new DLCollection($this->modx, $this->_docs);
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
     * @param  string|array  $id  значение PrimaryKey родителя
     * @return array массив документов
     */
    abstract public function getChildrenFolder($id);

    /**
     * @param  string  $group
     * @return string
     */
    protected function getGroupSQL($group = '')
    {
        $out = '';
        if ($group != '') {
            $out = 'GROUP BY ' . $group;
        }

        return $out;
    }

    /**
     *    Sorting method in SQL queries
     *
     * @param  string  $sortName  default sort field
     * @param  string  $orderDef  default order (ASC|DESC)
     *
     * @return string Order by for SQL
     * @global string $order
     * @global string $orderBy
     * @global string $sortBy
     *
     */
    protected function SortOrderSQL($sortName, $orderDef = 'DESC')
    {
        $this->debug->debug('', 'sortORDER', 2);

        $sort = '';
        switch ($this->getCFGDef('sortType', '')) {
            case 'none':
                break;
            case 'doclist':
                $idList = $this->sanitarIn($this->IDs, ',', false);
                $out = ['orderBy' => "FIND_IN_SET({$this->getCFGDef('sortBy', $this->getPK())}, '{$idList}')"];
                $this->config->setConfig($out); //reload config;
                $sort = "ORDER BY " . $out['orderBy'];
                break;
            default:
                $out = ['orderBy' => '', 'order' => '', 'sortBy' => ''];
                if (($tmp = $this->getCFGDef('orderBy', '')) != '') {
                    $out['orderBy'] = $tmp;
                } else {
                    switch (true) {
                        case ('' != ($tmp = $this->getCFGDef('sortDir', ''))): //higher priority than order
                            $out['order'] = $tmp;
                        // no break
                        case ('' != ($tmp = $this->getCFGDef('order', ''))):
                            $out['order'] = $tmp;
                        // no break
                    }
                    if ('' == $out['order'] || !in_array(strtoupper($out['order']), ['ASC', 'DESC'])) {
                        $out['order'] = $orderDef; //Default
                    }

                    $out['sortBy'] = (($tmp = $this->getCFGDef('sortBy', '')) != '') ? $tmp : $sortName;
                    $out['orderBy'] = $out['sortBy'] . " " . $out['order'];
                }
                $this->config->setConfig($out); //reload config;
                $sort = "ORDER BY " . $out['orderBy'];
                break;
        }
        $this->debug->debugEnd("sortORDER", 'Get sort order for SQL: ' . $this->debug->dumpData($sort));

        return $sort;
    }

    /**
     * Получение LIMIT вставки в SQL запрос
     *
     * @return string LIMIT вставка в SQL запрос
     */
    protected function LimitSQL($limit = 0, $offset = 0)
    {
        $this->debug->debug('', 'limitSQL', 2);
        $ret = '';
        if ($limit == 0) {
            $limit = $this->getCFGDef('display', 0);
        }
        $maxDocs = $this->getCFGDef('maxDocs', 0);
        if ($maxDocs > 0 && $limit > $maxDocs) {
            $limit = $maxDocs;
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
            $ret = "LIMIT " . (int) $offset . "," . (int) $limit;
        } else {
            if ($offset != 0) {
                /**
                 * To retrieve all rows from a certain offset up to the end of the result set, you can use some large number for the second parameter
                 * @see http://dev.mysql.com/doc/refman/5.0/en/select.html
                 */
                $ret = "LIMIT " . (int) $offset . ",18446744073709551615";
            }
        }
        $this->debug->debugEnd("limitSQL", "Get limit for SQL: " . $this->debug->dumpData($ret));

        return $ret;
    }

    /**
     * Clean up the modx and html tags
     *
     * @param  string  $data  String for cleaning
     * @param  string  $charset
     * @return string Clear string
     */
    public function sanitarData($data, $charset = 'UTF-8')
    {
        return APIHelpers::sanitarTag($data, $charset);
    }

    /**
     * run tree build
     *
     * @param  string  $idField  default name id field
     * @param  string  $parentField  default name parent field
     * @return array
     */
    public function treeBuild($idField = 'id', $parentField = 'parent')
    {
        return $this->_treeBuild(
            $this->_docs,
            $this->getCFGDef('idField', $idField),
            $this->getCFGDef('parentField', $parentField)
        );
    }

    /**
     * @see: https://github.com/DmitryKoterov/DbSimple/blob/master/lib/DbSimple/Generic.php#L986
     *
     * @param  array  $data  Associative data array
     * @param  string  $idName  name ID field in associative data array
     * @param  string  $pidName  name parent field in associative data array
     * @return array
     */
    private function _treeBuild($data, $idName, $pidName)
    {
        $children = []; // children of each ID
        $ids = [];
        foreach ($data as $i => $r) {
            $row =& $data[$i];
            $id = $row[$idName];
            $pid = $row[$pidName];
            $children[$pid][$id] =& $row;
            if (!isset($children[$id])) {
                $children[$id] = [];
            }
            $row['#childNodes'] =& $children[$id];
            $ids[$row[$idName]] = true;
        }
        // Root elements are elements with non-found PIDs.
        $this->_tree = [];
        foreach ($data as $i => $r) {
            $row =& $data[$i];
            if (!isset($ids[$row[$pidName]])) {
                $this->_tree[$row[$idName]] = $row;
            }
        }

        return $this->_tree;
    }

    /**
     * Получение PrimaryKey основной таблицы.
     * По умолчанию это id. Переопределить можно в контроллере присвоив другое значение переменной idField
     * @param  bool  $full  если true то возвращается значение для подстановки в запрос
     * @return string PrimaryKey основной таблицы
     */
    public function getPK($full = true)
    {
        $idField = isset($this->idField) ? $this->idField : 'id';
        if ($full) {
            $idField = '`' . $idField . '`';
            if (!empty($this->alias)) {
                $idField = '`' . $this->alias . '`.' . $idField;
            }
        }

        return $idField;
    }

    /**
     * Получение Parent key
     * По умолчанию это parent. Переопределить можно в контроллере присвоив другое значение переменной parentField
     * @param  bool  $full  если true то возвращается значение для подстановки в запрос
     * @return string Parent Key основной таблицы
     */
    public function getParentField($full = true)
    {
        $parentField = isset($this->parentField) ? $this->parentField : '';
        if ($full && !empty($parentField)) {
            $parentField = '`' . $parentField . '`';
            if (!empty($this->alias)) {
                $parentField = '`' . $this->alias . '`.' . $parentField;
            }
        }

        return $parentField;
    }

    /**
     * Разбор фильтров
     * OR(AND(filter:field:operator:value;filter2:field:oerpator:value);(...)), etc.
     *
     * @param  string  $filter_string  строка со всеми фильтрами
     * @return mixed результат разбора фильтров
     */
    protected function getFilters($filter_string)
    {
        $this->debug->debug("getFilters: " . $this->debug->dumpData($filter_string), 'getFilter', 1);
        // the filter parameter tells us, which filters can be used in this query
        $filter_string = ltrim(trim($filter_string, ';'));
        $output = ['join' => '', 'where' => ''];
        if (!$filter_string) {
            return $output;
        }
        $logic_op_found = false;
        $joins = $wheres = [];
        foreach ($this->_logic_ops as $op => $sql) {
            if (strpos($filter_string, $op) === 0) {
                $logic_op_found = true;
                $subfilters = mb_substr($filter_string, strlen($op) + 1, mb_strlen($filter_string, "UTF-8"), "UTF-8");
                $subfilters = $this->smartSplit($subfilters);
                $lastFilter = '';
                foreach ($subfilters as $filter) {
                    /**
                     * С правой стороны не выполняется trim, т.к. там находятся значения. А они могу быть чувствительны к пробелам
                     */
                    $subfilter = $this->getFilters(ltrim($filter) . ltrim($lastFilter));
                    if (!$subfilter) {
                        $lastFilter = explode(';', $filter, 2);
                        $subfilter = isset($lastFilter[1]) ? $this->getFilters($lastFilter[1]) : '';
                        $lastFilter = $lastFilter[0];
                        if (!$subfilter) {
                            continue;
                        }
                    }
                    if ($subfilter['join']) {
                        $joins[] = $subfilter['join'];
                    }
                    if ($subfilter['where']) {
                        $wheres[] = $subfilter['where'];
                    }
                }
                $output['join'] = !empty($joins) ? implode(' ', array_reverse($joins)) : '';
                $output['where'] = !empty($wheres) ? '(' . implode($sql, array_reverse($wheres)) . ')' : '';
            }
        }

        if (!$logic_op_found) {
            $filter = $this->loadFilter($filter_string);
            if (!$filter) {
                $this->debug->warning('Error while loading DocLister filter "' . $this->debug->dumpData($filter_string) . '": check syntax!');
                $output = false;
            } else {
                $output['join'] = $filter->get_join();
                $output['where'] = $filter->get_where();

            }
        }
        $this->debug->debug('getFilter');

        return $output;
    }

    /**
     * @return mixed
     */
    public function filtersWhere()
    {
        return APIHelpers::getkey($this->_filters, 'where', '');
    }

    /**
     * @return mixed
     */
    public function filtersJoin()
    {
        return APIHelpers::getkey($this->_filters, 'join', '');
    }

    /**
     * @param  string  $join
     * @return $this
     */
    public function setFiltersJoin($join = '')
    {
        if (!empty($join)) {
            if (!empty($this->_filters['join'])) {
                $this->_filters['join'] .= ' ' . $join;
            } else {
                $this->_filters['join'] = $join;
            }
        }

        return $this;
    }

    /**
     * Приведение типа поля
     *
     * @param $field string имя поля
     * @param $type string тип фильтрации
     * @return string имя поля с учетом приведения типа
     */
    public function changeSortType($field, $type)
    {
        $type = trim($type);
        switch (strtoupper($type)) {
            case 'DECIMAL':
                $field = 'CAST(' . $field . ' as DECIMAL(10,2))';
                break;
            case 'UNSIGNED':
                $field = 'CAST(' . $field . ' as UNSIGNED)';
                break;
            case 'BINARY':
                $field = 'CAST(' . $field . ' as BINARY)';
                break;
            case 'DATETIME':
                $field = 'CAST(' . $field . ' as DATETIME)';
                break;
            case 'SIGNED':
                $field = 'CAST(' . $field . ' as SIGNED)';
                break;
        }

        return $field;
    }

    /**
     * Загрузка фильтра
     * @param  string  $filter  срока с параметрами фильтрации
     * @return bool
     */
    protected function loadFilter($filter)
    {
        $this->debug->debug('Load filter ' . $this->debug->dumpData($filter), 'loadFilter', 2);
        $out = false;
        $fltr_params = explode(':', $filter, 2);
        $fltr = APIHelpers::getkey($fltr_params, 0, null);
        /**
         * @var tv_DL_filter|content_DL_filter $fltr_class
         */
        $fltr_class = $fltr . '_DL_filter';
        // check if the filter is implemented
        if (!is_null($fltr)) {
            if (!class_exists($fltr_class) && file_exists(__DIR__ . '/filter/' . $fltr . '.filter.php')) {
                require_once dirname(__FILE__) . '/filter/' . $fltr . '.filter.php';
            }
            if (class_exists($fltr_class)) {
                $this->totalFilters++;
                $fltr_obj = new $fltr_class();
                if ($fltr_obj->init($this, $filter)) {
                    $out = $fltr_obj;
                } else {
                    $this->debug->error("Wrong filter parameter: '{$this->debug->dumpData($filter)}'", 'Filter');
                }
            }
        }
        if (!$out) {
            $this->debug->error("Error load Filter: '{$this->debug->dumpData($filter)}'", 'Filter');
        }

        $this->debug->debugEnd("loadFilter");

        return $out;
    }

    /**
     * Общее число фильтров
     * @return int
     */
    public function getCountFilters()
    {
        return (int) $this->totalFilters;
    }

    /**
     * Выполнить SQL запрос
     * @param  string  $q  SQL запрос
     */
    public function dbQuery($q)
    {
        $this->debug->debug($q, "query", 1, 'sql');
        $out = $this->modx->db->query($q);
        $this->debug->debugEnd("query");

        return $out;
    }

    /**
     * Экранирование строки в SQL запросе LIKE
     * @see: http://stackoverflow.com/a/3683868/2323306
     *
     * @param  string  $field  поле по которому осуществляется поиск
     * @param  string  $value  искомое значение
     * @param  string  $escape  экранирующий символ
     * @param  string  $tpl  шаблон подстановки значения в SQL запрос
     * @return string строка для подстановки в SQL запрос
     */
    public function LikeEscape($field, $value, $escape = '=', $tpl = '%[+value+]%')
    {
        return sqlHelper::LikeEscape($this->modx, $field, $value, $escape, $tpl);
    }

    /**
     * Получение REQUEST_URI без GET-ключа с
     * @return string
     */
    public function getRequest()
    {
        $query = parse_url(MODX_SITE_URL . $_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '';
        parse_str($query, $url);

        return http_build_query(array_merge($url, [DocLister::AliasRequest => null]));
    }
}
