<?php namespace DLCity;
class Helper extends \Module\Helper
{
    protected static $mode = 'city';

    public static function getTextMode()
    {
        $data = array(
            'city' => 'Город',
            'street' => 'Улица'
        );
        $mode = self::getMode();
        return isset($data[$mode]) ? $data[$mode] : '';
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