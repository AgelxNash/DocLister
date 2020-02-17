<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class GetKeyTest extends \PHPUnit_Framework_TestCase
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
        $this->assertNull(APIhelpers::getkey($this->data, 'empty'));
        $this->assertNull(APIhelpers::getkey($this->data, 'empty', 'my default'));
    }

    public function testGetKeyWithNoKey()
    {
        $this->assertSame('only_value', APIhelpers::getkey($this->data, 0));
        $this->assertSame('value_with_number_key', APIhelpers::getkey($this->data, 10));
    }

    public function testGetKeyWithArrayKey()
    {
        $this->assertSame(array('a', 'b', 'c'), APIhelpers::getkey($this->data, 'subArray'));
    }
}
