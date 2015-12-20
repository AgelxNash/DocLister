<?php
include_once(MODX_BASE_PATH . 'assets/lib/SqlFormatter.php');

class DLdebug
{
    private $_log = array();
    private $_calcLog = array();

    /**
     * Объект унаследованный от абстрактоного класса DocLister
     * @var DocLister
     * @access protected
     */
    protected $DocLister = null;

    /**
     * Объект DocumentParser - основной класс MODX
     * @var DocumentParser
     * @access protected
     */
    protected $modx = null;

    public function __construct($DocLister)
    {
        if ($DocLister instanceof DocLister) {
            $this->DocLister = $DocLister;
            $this->modx = $this->DocLister->getMODX();
        }

    }

    public function getLog(){
        return $this->_log;
    }
    public function clearLog(){
        $this->_log = array();
        return $this;
    }
    public function countLog(){
        return count($this->_log);
    }
    /*
     * 1 - SQL
     * 2 - Full debug
     */
    public function debug($message, $key = null, $mode = 0, $format = false)
    {
        $mode = (int)$mode;
        if ($mode > 0 && $this->DocLister->getDebug() >= $mode) {
            $data = array(
                'msg' => $message,
                'format' => $format,
                'start' => microtime(true) - $this->DocLister->getTimeStart()
            );
            if (is_scalar($key) && !empty($key)) {
                $data['time'] = microtime(true);
                $this->_calcLog[$key] = $data;
            } else {
                $this->_log[$this->countLog()] = $data;
            }
        }
    }
    public function updateMessage($message, $key){
        if (is_scalar($key) && !empty($key) && isset($this->_calcLog[$key])) {
            $this->_calcLog[$key]['msg'] = $message;
        }
    }

    public function debugEnd($key, $msg = null)
    {
        if (is_scalar($key) && isset($this->_calcLog[$key], $this->_calcLog[$key]['time']) && $this->DocLister->getDebug() > 0) {
            $this->_log[$this->countLog()] = array(
                'msg' => isset($msg) ? $msg : $this->_calcLog[$key]['msg'],
                'start' => $this->_calcLog[$key]['start'],
                'time' => microtime(true) - $this->_calcLog[$key]['time'],
                'format' => $this->_calcLog[$key]['format']
            );
            unset($this->_calcLog[$key]['time']);
        }
    }


    public function critical($message, $title = '')
    {
        //@TODO: dump $_SERVER/$_POST/$_GET/$_COOKIE
    }

    public function info($message, $title = '')
    {
        $this->_sendLogEvent(1, $message, $title);
    }

    public function warning($message, $title = '')
    {
        $this->_sendLogEvent(2, $message, $title);
    }

    public function error($message, $title = '')
    {
        $this->_sendLogEvent(3, $message, $title);
    }

    private function _sendLogEvent($type, $message, $title = '')
    {
        $title = "DocLister" . (!empty($title) ? ' - ' . $title : '');
        $this->modx->logEvent(0, $type, $message, $title);
    }

    public function showLog()
    {
        $out = "";
        if ($this->DocLister->getDebug() > 0 && is_array($this->_log)) {
            foreach ($this->_log as $item) {
                $item['time'] = isset($item['time']) ? round(floatval($item['time']), 5) : 0;
                $item['start'] = isset($item['start']) ? round(floatval($item['start']), 5) : 0;

                if (isset($item['msg'])) {
                    if(empty($item['format'])){
                        $item['msg'] = $this->dumpData($item['msg']);
                    }else{
                       $item['msg'] = $this->dumpData(SqlFormatter::format($item['msg']), '', null);
                    }
                } else {
                    $item['msg'] = '';
                }

                $tpl = '<li>
                            <strong>action time</strong>: <em>[+time+]</em> &middot; <strong>total time</strong>: <em>[+start+]</em><br />
                            <blockquote>[+msg+]</blockquote>
                    </li>';
                $out .= $this->DocLister->parseChunk("@CODE: " . $tpl, $item);
            }
            if (!empty($out)) {
                $out = $this->DocLister->parseChunk("@CODE:
                <style>.dlDebug{
                    background: #eee !important;
                    padding:0 !important;
                    margin: 0 !important;
                    text-align:left;
                    font-size:14px !important;
                    width:100%;
                    z-index:999;
                }
                .dlDebug > ul{
                    list-style:none !important;
                    padding: 3px !important;
                    margin: 0 !important;
                    border: 2px solid #000;
                }
                .dlDebug > ul > li{
                    border-top: 1px solid #000 !important;
                    background: none;
                    margin: 0;
                    padding: 0;
                    width:100%;
                }
                .dlDebug > ul > li:first-child {
                    border-top: 0 !important;
                }
                .dlDebug > ul > li > blockquote{
                    border-left: 4px solid #aaa !important;
                    font-family: monospace !important;
                    margin: 5px 0 !important;
                    padding:5px !important;

                    word-wrap: break-word !important;
                    white-space: pre-wrap !important;
                    white-space: -moz-pre-wrap !important;
                    white-space: -pre-wrap !important;
                    white-space: -o-pre-wrap !important;
                    word-break: break-all !important;
                }
                </style>
                <div class=\"dlDebug\"><ul>[+wrap+]</ul></div>", array('wrap' => $out));
            }
        }
        return $out;
    }

    public function dumpData($data, $wrap = '', $charset = 'UTF-8')
    {
        $out = $this->DocLister->sanitarData(print_r($data, 1), $charset);
        if (!empty($wrap) && is_string($wrap)) {
            $out = "<{$wrap}>{$out}</{$wrap}>";
        }
        return $out;
    }
}