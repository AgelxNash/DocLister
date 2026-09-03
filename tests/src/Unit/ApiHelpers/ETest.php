<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class ETest extends \PHPUnit\Framework\TestCase
{
    public function testEscapesSpecialChars()
    {
        $this->assertSame(
            '&lt;b&gt;&quot;x&quot;&lt;/b&gt;',
            APIhelpers::e('<b>"x"</b>')
        );
        $this->assertSame(
            '&lt;a href=&#039;url&#039;&gt;',
            APIhelpers::e("<a href='url'>")
        );
    }

    public function testDoubleEncodingDisabled()
    {
        $this->assertSame('&amp;', APIhelpers::e('&amp;'));
        $this->assertSame('&lt;b&gt;', APIhelpers::e('&lt;b&gt;'));
    }

    public function testNonScalarReturnsEmptyString()
    {
        $this->assertSame('', APIhelpers::e(array()));
        $this->assertSame('', APIhelpers::e(null));
        $this->assertSame('', APIhelpers::e(new \stdClass()));
    }
}
