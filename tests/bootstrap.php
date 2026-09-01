<?php
include_once dirname(__DIR__) . '/vendor/autoload.php';

if (!function_exists('evolutionCMS')) {
    /**
     * Evolution CMS helper used by APIhelpers::sanitarIn().
     * Unit tests bind the mocked DocumentParser to $GLOBALS['modx'].
     */
    function evolutionCMS()
    {
        if (isset($GLOBALS['modx'])) {
            return $GLOBALS['modx'];
        }

        throw new \RuntimeException('evolutionCMS() is unavailable: DocumentParser is not initialized');
    }
}
