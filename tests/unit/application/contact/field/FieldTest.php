<?php

namespace unit\application\contact\field;

use App\application\contact\field\Field;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    private Field $field;


    public function setUp(): void
    {
        $this->field = new Field('name', 'error');
    }


    public function testGetnameReturnsName(): void
    {
        $result = $this->field->getName();

        $this->assertEquals('name', $result);
    }


    public function testGeterrorReturnsError(): void
    {
        $result = $this->field->getError();

        $this->assertEquals('error', $result);
    }


    public function testIsValidReturnsTrueWhenValueIsNotEmpty(): void
    {
        $result = $this->field->isValid('value');

        $this->assertTrue($result);
    }


    public function testIsValidReturnsFalseWhenValueIsEmpty(): void
    {
        $result = $this->field->isValid('');

        $this->assertFalse($result);
    }


    public function testIsValidReturnsFalseWhenValueIsWhitespace(): void
    {
        $result = $this->field->isValid(' ');

        $this->assertFalse($result);
    }
}
