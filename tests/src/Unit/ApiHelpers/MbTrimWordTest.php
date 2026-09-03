<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class MbTrimWordTest extends \PHPUnit\Framework\TestCase
{
    public function testCutOnSpaceBoundary()
    {
        $this->assertSame('hello', APIhelpers::mb_trim_word('hello world foo', 5));
        $this->assertSame('hello', APIhelpers::mb_trim_word('hello world', 8));
    }

    public function testHtmlTagsStrippedAndSpacesCollapsed()
    {
        $this->assertSame('hello', APIhelpers::mb_trim_word('<p>hello <b>world</b></p>', 8));
        $this->assertSame('one two', APIhelpers::mb_trim_word('one  two   three', 7));
    }

    public function testTrailingPunctuationRemoved()
    {
        $this->assertSame('word', APIhelpers::mb_trim_word('word &nbsp;', 20));
    }

    public function testCyrillic()
    {
        $this->assertSame('слово', APIhelpers::mb_trim_word('слово длинное русское', 6));
    }

    /**
     * В усечённом фрагменте нет пробела - последнее слово не помещается целиком,
     * поэтому метод возвращает пустую строку (в том числе после неудачного mb_strripos).
     */
    public function testNoSpaceInsideCutFragmentReturnsEmptyString()
    {
        $this->assertSame('', APIhelpers::mb_trim_word('hello world', 4));
        $this->assertSame('', APIhelpers::mb_trim_word('hello, world', 5));
    }
}
