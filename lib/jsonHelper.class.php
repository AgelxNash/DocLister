<?php
if (!defined("JSON_ERROR_UTF8")) define("JSON_ERROR_UTF8", 5); //PHP < 5.3.3
include_once("xnop.class.php");

class jsonHelper {
    protected static $_error = array(
        JSON_ERROR_NONE => 'error_none',
        JSON_ERROR_DEPTH => 'error_depth',
        JSON_ERROR_STATE_MISMATCH => 'error_state_mismatch',
        JSON_ERROR_CTRL_CHAR => 'error_ctrl_char',
        JSON_ERROR_SYNTAX => 'error_syntax',
        JSON_ERROR_UTF8 => 'error_utf8'
    );

    /**
     * Разбор JSON строки при помощи json_decode
     *
     * @param $json string строка c JSON
     * @param array $config ассоциативный массив с настройками для json_decode
     * @param bool $nop создавать ли пустой объект запрашиваемого типа
     * @return array|mixed|xNop
     */
    public static function jsonDecode($json, $config = array(), $nop = false){
        if(isset($config['assoc'])){
            $assoc = (boolean)$config['assoc'];
        }else{
            $assoc = false;
        }

        if(isset($config['assoc']) && (int)$config['depth']>0){
            $depth = (int)$config['depth'];
        }else{
            $depth = 512;
        }

        $out = json_decode($json, $assoc, $depth);
        if($nop && is_null($out)){
            if($assoc){
                $out = array();
            }else{
                $out = new xNop();
            }
        }
        return $out;
    }

    /**
     * Получение кода последенй ошибки
     * @see http://www.php.net/manual/ru/function.json-last-error-msg.php
     * @return string
     */
    public static function json_last_error_msg(){
        $error = json_last_error();
        return isset(static::$_error[$error]) ? static::$_error[$error] : 'other';
    }
}