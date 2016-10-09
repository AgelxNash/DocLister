<?php namespace DLCity;
/**
 * Class Helper
 * @package DLCity
 */
class Helper extends \Module\Helper
{
    /**
     * @var string
     */
    protected static $mode = 'city';

    /**
     * @return string
     */
    public static function getTextMode()
    {
        $data = array(
            'city' => 'Город',
            'street' => 'Улица'
        );
        $mode = self::getMode();
        return isset($data[$mode]) ? $data[$mode] : '';
    }

    /**
     * @return mixed
     */
    public static function countDisplayCity()
    {
        return self::_counter("city", "hide=0");
    }

    /**
     * @return mixed
     */
    public static function countCity()
    {
        return self::_counter("city");
    }

    /**
     * @return mixed
     */
    public static function countDisplayStreet()
    {
        return self::_counter("street", "hide=0");
    }

    /**
     * @return mixed
     */
    public static function countStreet()
    {
        return self::_counter("street");
    }
}