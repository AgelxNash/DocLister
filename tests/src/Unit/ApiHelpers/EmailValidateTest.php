<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class EmailValidateTest extends \PHPUnit\Framework\TestCase
{
    public function testValidEmailWithoutDnsCheck()
    {
        $this->assertFalse(APIhelpers::emailValidate('user@example.com', false));
    }

    public function testInvalidFormat()
    {
        $this->assertSame('format', APIhelpers::emailValidate('not-an-email', false));
        $this->assertSame('format', APIhelpers::emailValidate('', false));
        $this->assertSame('format', APIhelpers::emailValidate('a@b', false));
    }

    /**
     * TLD .invalid зарезервирован RFC 6761 и никогда не резолвится,
     * поэтому DNS-проверка детерминированно возвращает ошибку 'dns'
     * (в том числе в окружении без доступа к сети).
     */
    public function testReservedDomainFailsDnsCheck()
    {
        $this->assertSame('dns', APIhelpers::emailValidate('user@domain.invalid', true));
    }
}
