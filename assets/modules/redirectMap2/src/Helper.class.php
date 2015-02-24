<?php namespace RedirectMap;

class Helper extends \Module\Helper
{
    public static function countRules()
    {
        return self::_counter("redirect_map");
    }

    public static function countDeactiveRules()
    {
        return self::_counter("redirect_map", "`active`='0'");
    }

    public static function countActiveRules()
    {
        return self::_counter("redirect_map", "`active`='1'");
    }
}