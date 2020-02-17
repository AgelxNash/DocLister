<?php namespace DocLister\Tests\Real;

use DocumentParser;

abstract class TestAbstract extends \PHPUnit_Framework_TestCase
{
    protected $modx;

    protected function mockMODX(array $config = array())
    {
        $modx = new DocumentParser();

        $modx->documentIdentifier = 1;

        return $modx;
    }
}
