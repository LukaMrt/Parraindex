<?php

namespace unit\application\contact\field;

use App\application\contact\field\EmailField;
use PHPUnit\Framework\TestCase;

class EmailFieldTest extends TestCase
{

    private EmailField $emailField;


    public function setUp(): void
    {
        $this->emailField = new EmailField('email', 'error');
    }


    public function testGetnameReturnsEmail()
    {
        $result = $this->emailField->getName();

        $this->assertEquals('email', $result);
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