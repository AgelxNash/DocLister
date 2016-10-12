<?php namespace DocLister\Tests;

use APIhelpers;

class APIhelpersTest extends \PHPUnit_Framework_TestCase
{
    protected $data = array(
        'subArray' => array(
            'a',
            'b',
            'c'
        ),
        'empty'    => null,
        'scalar'   => 'my string',
        'only_value',
        '10'       => 'value_with_number_key',
    );

    public function testGetKeyWithEmptyKey()
    {
        $this->assertEquals(null, APIhelpers::getkey($this->data, 'empty'));
        $this->assertEquals(null, APIhelpers::getkey($this->data, 'empty', 'my default'));
    }

    public function testGetKeyWithNoKey()
    {
        $this->assertEquals('only_value', APIhelpers::getkey($this->data, 0));
        $this->assertEquals('value_with_number_key', APIhelpers::getkey($this->data, 10));
    }

    public function testGetKeyWithArrayKey()
    {
        $this->assertEquals(array('a', 'b', 'c'), APIhelpers::getkey($this->data, 'subArray'));
    }
}
