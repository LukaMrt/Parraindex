<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact\field;

use App\Application\contact\field\EmailField;
use PHPUnit\Framework\TestCase;

final class EmailFieldTest extends TestCase
{
    private EmailField $emailField;


    #[\Override]
    protected function setUp(): void
    {
        $this->emailField = new EmailField('email', 'error');
    }


    public function testGetnameReturnsEmail(): void
    {
        $result = $this->emailField->getName();

        $this->assertSame('email', $result);
    }


    public function testIsValidReturnsTrueWhenEmailIsValid(): void
    {
        $result = $this->emailField->isValid('test.testa@gmail.com');
        $this->assertTrue($result);
    }


    public function testIsValidReturnsFalseWhenEmailIsInvalid(): void
    {
        $result = $this->emailField->isValid('test.testa');
        $this->assertFalse($result);
    }
}
