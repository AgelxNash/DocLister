<?php namespace RedirectMap;

class Debug
{
    /**
     * @var \DocumentParser
     */
    protected static $modx = null;
    protected static $title = null;

    public static function init(\DocumentParser $modx, $title)
    {
        self::$modx = $modx;
        self::$title = $title;
    }

    public static function info($message, $title = '')
    {
        self::_sendLogEvent(1, $message, $title);
    }

    public static function warning($message, $title = '')
    {
        self::_sendLogEvent(2, $message, $title);
    }

    public static function error($message, $title = '')
    {
        self::_sendLogEvent(3, $message, $title);
    }

    private static function _sendLogEvent($type, $message, $title = '')
    {
        $title = self::$title . (!empty($title) ? ' - ' . $title : '');
        if (is_array($message)) {
            $message = self::arrayToList($message);
        }
        self::$modx->logEvent(0, $type, $message, $title);
    }

    private static function arrayToList($data)
    {
        $msg = array();
        foreach ($data as $key => $text) {
            if (is_array($text)) {
                $text = self::arrayToList($text);
            }
            $msg[] = '<li><strong>' . $key . ':</strong> ' . $text . "</li>";
        }
        return "<ul>" . implode("", $msg) . "</ul>";
    }
}