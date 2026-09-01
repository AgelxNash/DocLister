<?php namespace DocLister\Tests\Real;

use DocumentParser;

abstract class TestAbstract extends \PHPUnit\Framework\TestCase
{
    protected $modx;

    protected function mockMODX(array $config = array())
    {
        try {
            $modx = new DocumentParser();
        } catch (\Exception $exception) {
            $this->markTestSkipped('Real suite requires MySQL: ' . $exception->getMessage());
        }

        $modx->documentIdentifier = 1;
        $GLOBALS['modx'] = $modx;

        return $modx;
    }
}
