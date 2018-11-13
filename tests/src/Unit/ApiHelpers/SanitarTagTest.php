<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class SanitarTagTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyString()
    {
        $this->assertEquals(
            '',
            APIhelpers::sanitarTag('', null)
        );
        $this->assertEquals(
            '',
            APIhelpers::sanitarTag(new \stdClass, null)
        );
        $this->assertEquals(
            '',
            APIhelpers::sanitarTag(null, null)
        );
    }
    public function testDefaultProperty()
    {
        $this->assertEquals(
            'clear string',
            APIhelpers::sanitarTag('clear string', null)
        );
        $source = '[[tag? &param=`value`]]';
        $replacing = '&#91;&#91;tag? &param=&#96;value&#96;&#93;&#93;';
        $this->assertEquals(
            $replacing,
            APIhelpers::sanitarTag($source, null)
        );
        $this->assertEquals(
            array($replacing => $replacing),
            APIhelpers::sanitarTag(array(
                $source => $source
            ), null)
        );
        $this->assertEquals(
            array(
                $replacing => array(
                    $replacing => $replacing
                )
            ),
            APIhelpers::sanitarTag(array(
                $source => array(
                    $source => $source
                )
            ), null)
        );
    }
    public function testChangingProperty()
    {
        $property = array('[' => '', ']' => '&#93;');
        $this->assertEquals(
            'clear string',
            APIhelpers::sanitarTag('clear string', null, $property)
        );
        $source = '[[tag? &param=`value`]]';
        $replacing = 'tag? &param=`value`&#93;&#93;';
        $this->assertEquals(
            $replacing,
            APIhelpers::sanitarTag($source, null, $property)
        );
        $this->assertEquals(
            array($replacing => $replacing),
            APIhelpers::sanitarTag(
                array(
                    $source => $source
                ),
                null,
                $property
            )
        );
        $this->assertEquals(
            array(
                $replacing => array(
                    $replacing => $replacing
                )
            ),
            APIhelpers::sanitarTag(
                array(
                    $source => array(
                        $source => $source
                    )
                ),
                null,
                $property
            )
        );
    }
}
