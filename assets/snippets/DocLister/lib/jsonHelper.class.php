<?php
if (!defined("JSON_ERROR_UTF8")) define("JSON_ERROR_UTF8", 5); //PHP < 5.3.3
include_once("xnop.class.php");

class jsonHelper
{
    protected static $_error = array(
        0 => 'error_none', //JSON_ERROR_NONE
        1 => 'error_depth', //JSON_ERROR_DEPTH
        2 => 'error_state_mismatch', //JSON_ERROR_STATE_MISMATCH
        3 => 'error_ctrl_char', //JSON_ERROR_CTRL_CHAR
        4 => 'error_syntax', //JSON_ERROR_SYNTAX
        5 => 'error_utf8' //SON_ERROR_UTF8
    );

    /**
     * Разбор JSON строки при помощи json_decode
     *
     * @param $json string строка c JSON
     * @param array $config ассоциативный массив с настройками для json_decode
     * @param bool $nop создавать ли пустой объект запрашиваемого типа
     * @return array|mixed|xNop
     */
    public static function jsonDecode($json, $config = array(), $nop = false)
    {
        if (isset($config['assoc'])) {
            $assoc = (boolean)$config['assoc'];
        } else {
            $assoc = false;
        }

        if (isset($config['depth']) && (int)$config['depth'] > 0) {
            $depth = (int)$config['depth'];
        } else {
            $depth = 512;
        }

        if (version_compare(phpversion(), '5.3.0', '<')) {
            $out = json_decode($json, $assoc);
        } else {
            $out = json_decode($json, $assoc, $depth);
        }

        if ($nop && is_null($out)) {
            if ($assoc) {
                $out = array();
            } else {
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
    public static function json_last_error_msg()
    {
        if (function_exists('json_last_error')) {
            $error = json_last_error();
        } else {
            $error = 999;
        }
        return isset(self::$_error[$error]) ? self::$_error[$error] : 'other';
    }
}