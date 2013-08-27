<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Agel_Nash
 * Date: 28.08.13
 * Time: 2:46
 * To change this template use File | Settings | File Templates.
 */

class debugDL{
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

    public function __construct($DocLister){
        if ($DocLister instanceof DocLister) {
            $this->DocLister = $DocLister;
            $this->modx = $this->DocLister->getMODX();
        }

    }

    /*
     * 1 - SQL
     * 2 - Full debug
     */
    public function debug($message, $key = '', $mode=0){
        $mode = (int)$mode;
        if($mode>0 && $this->DocLister->getDebug() >= $mode){
            $data = array(
                'msg'=> $message,
                'start' => $this->modx->getMicroTime() - $this->DocLister->getTimeStart()
            );
            if(is_scalar($key) && $key!=''){
                $data['time'] = $this->modx->getMicroTime();
                $this->_calcLog[$key] = $data;
            }else{
                $this->_log[count($this->_log)] = $data;
            }
        }
    }

    public function debugEnd($key,$msg=null){
        if(is_scalar($key) && isset($this->_calcLog[$key],$this->_calcLog[$key]['time']) && $this->DocLister->getDebug()>0){
            $this->_log[count($this->_log)] = array(
                'msg' => isset($msg) ? $msg : $this->_calcLog[$key]['msg'],
                'start'=>$this->_calcLog[$key]['start'],
                'time' => $this->modx->getMicroTime() - $this->_calcLog[$key]['time']
            );
            unset($this->_calcLog[$key]['time']);
        }
    }


    public function critical($message){
        //@TODO: dump $_SERVER/$_POST/$_GET/$_COOKIE
    }
    public function info($message){
        $this->_sendLogEvent(1,$message);
    }

    public function warning($message){
        $this->_sendLogEvent(2,$message);
    }
    public function error($message){
       $this->_sendLogEvent(3,$message);
    }

    private function _sendLogEvent($type, $message){
        $this->modx->logEvent(0, $type, $message, "DocLister");
    }

    public function showLog(){
        $out = "";
        if($this->DocLister->getDebug()>0 && is_array($this->_log)){
            foreach($this->_log as $item){
                if(!isset($item['time'])){
                    $item['time'] = 0;
                }

                if(isset($item['msg'])){
                    $item['msg']= $this->dumpData($item['msg']);
                }else{
                    $item['msg'] = '';
                }

                $tpl = '<strong>action time</strong>: <em>[+time+]</em><br />
                    <strong>total time</strong>: <em>[+start+]</em><br />
                    <code>[+msg+]</code>
                    <hr />';
                $out .= $this->DocLister->parseChunk("@CODE: ".$tpl, $item);
            }
            if(!empty($out)){
                $out = $this->DocLister->parseChunk("@CODE: <pre id='dlDebug'>[+wrap+]</pre>", array('wrap'=>$out));
            }
        }
        return $out;
    }

    protected function dumpData($data){
        return $this->DocLister->sanitarData(print_r($data,1));
    }


}