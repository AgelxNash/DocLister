<?php namespace RedirectMap;

/**
 * Class Helper
 * @package RedirectMap
 */
class Helper extends \Module\Helper
{
    /**
     * @return mixed
     */
    public static function countRules()
    {
        return self::_counter("redirect_map");
    }

    /**
     * @return mixed
     */
    public static function countDeactiveRules()
    {
        return self::_counter("redirect_map", "`active`='0'");
    }

    /**
     * @return mixed
     */
    public static function countActiveRules()
    {
        return self::_counter("redirect_map", "`active`='1'");
    }
}