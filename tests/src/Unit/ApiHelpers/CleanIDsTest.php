<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class CleanIDsTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidIdList()
    {
        try {
            APIhelpers::cleanIDs(null);
            $this->assertFalse(true);
        } catch (\Exception $exception) {
            $this->assertStringStartsWith('Invalid IDs list', $exception->getMessage());
        }

        try {
            APIhelpers::cleanIDs(new \stdClass);
            $this->assertFalse(true);
        } catch (\Exception $exception) {
            $this->assertStringStartsWith('Invalid IDs list', $exception->getMessage());
        }
    }

    public function testZero()
    {
        $this->assertSame(
            array(0),
            array_values(APIhelpers::cleanIDs(0))
        );

        $this->assertSame(
            array(0),
            array_values(APIhelpers::cleanIDs(''))
        );

        $this->assertSame(
            array(0),
            array_values(APIhelpers::cleanIDs('a,b,c,d'))
        );
    }

    public function testRealData()
    {
        $this->assertSame(
            array(0, 1, 2, 3, 4),
            array_values(APIhelpers::cleanIDs('-1,0,1,1,2,a,3,4'))
        );

        $this->assertSame(
            array(0, 1, 2, 3, 4),
            array_values(APIhelpers::cleanIDs(array(-1, 0, 1, 1, 2, 'a', 3, 4)))
        );
    }

    public function testCustomSeparator()
    {
        $this->assertSame(
            array(0, 1, 2, 3, 4),
            array_values(APIhelpers::cleanIDs('-1|0|1|1|2|a|3|4', '|'))
        );
    }

    public function testIgnore()
    {
        $this->assertSame(
            array(0, 1, 3, 4),
            array_values(APIhelpers::cleanIDs('-1,0,1,1,2,a,3,4', ',', array(2)))
        );

        $this->assertSame(
            array(0, 1, 3, 4),
            array_values(APIhelpers::cleanIDs(array(-1, 0, 1, 1, 2, 'a', 3, 4), null, array(2)))
        );
    }
}
