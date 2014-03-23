<?php
class DLTemplate{
    protected $modx = null;

    /**
     * @var cached reference to singleton instance
     */
    protected static $instance;

    /**
     * gets the instance via lazy initialization (created on first usage)
     *
     * @return self
     */
    public static function getInstance(DocumentParser $modx)
    {

        if (null === static::$instance) {
            static::$instance = new static($modx);
        }
        return static::$instance;
    }

    /**
     * is not allowed to call from outside: private!
     *
     */
    private function __construct(DocumentParser $modx)
    {
        $this->modx = $modx;
    }

    /**
     * prevent the instance from being cloned
     *
     * @return void
     */
    private function __clone()
    {

    }

    /**
     * prevent from being unserialized
     *
     * @return void
     */
    private function __wakeup()
    {

    }

    /**
     * Сохранение данных в массив плейсхолдеров
     *
     * @param mixed $data данные
     * @param int $set устанавливать ли глобальнй плейсхолдер MODX
     * @param string $key ключ локального плейсхолдера
     * @param string $prefix префикс для ключей массива
     * @return string
     */
    public function toPlaceholders($data, $set = 0, $key = 'contentPlaceholder', $prefix = '')
    {
        $out = '';
        if ($set != 0) {
            $this->modx->toPlaceholder($key, $data, $prefix);
        } else {
            $out = $data;
        }
        return $out;
    }
    /**
     * refactor $modx->getChunk();
     *
     * @param string $name Template: chunk name || @CODE: template || @FILE: file with template
     * @return string html template with placeholders without data
     */
    public function getChunk($name){
        $tpl = '';
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
                case '@INLINE':
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
                default:{
                    $tpl = $this->modx->getChunk($name);
                }
            }
            $this->modx->chunkCache[$name] = $tpl;
        }else{
            if($name!=''){
                $tpl = $this->modx->getChunk($name);
            }
        }
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
        $out = null;
        if (is_array($data) && ($out = $this->getChunk($name)) != '') {
            if(preg_match("/\[\+[a-zA-Z0-9\.\_\-]+\+\]/",$out)){
                $data = $this->renameKeyArr($data, '[', ']', '+');
                $out = str_replace(array_keys($data), array_values($data), $out);
            }
        }
        return $out;
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
    public function renameKeyArr($data, $prefix = '', $suffix = '', $sep = '.')
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
}