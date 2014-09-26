<?php namespace DLCity;
class Helper
{
    protected static $modx = null;
    protected static $mode = 'city';

    public static function init(\DocumentParser $modx, $mode = 'city')
    {
        self::$modx = $modx;
        self::setMode($mode);
    }

    public function getTextMode()
    {
        $data = array(
            'city' => 'Город',
            'street' => 'Улица'
        );
        $mode = self::getMode();
        return isset($data[$mode]) ? $data[$mode] : '';
    }

    public static function getMode()
    {
        return self::$mode;
    }

    public static function setMode($text)
    {
        self::$mode = $text;
    }

    protected static function _counter($from, $where = '')
    {
        $q = self::$modx->db->select('count(id)', self::$modx->getFullTableName($from), $where);
        return self::$modx->db->getValue($q);
    }

    public static function countDisplayCity()
    {
        return self::_counter("city", "hide=0");
    }

    public static function countCity()
    {
        return self::_counter("city");
    }

    public static function countDisplayStreet()
    {
        return self::_counter("street", "hide=0");
    }

    public static function countStreet()
    {
        return self::_counter("street");
    }
}