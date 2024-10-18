<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact\field;

use App\Application\contact\field\Field;
use PHPUnit\Framework\TestCase;

final class FieldTest extends TestCase
{
    private Field $field;


    #[\Override]
    protected function setUp(): void
    {
        $this->field = new Field('name', 'error');
    }


    public function testGetnameReturnsName(): void
    {
        $result = $this->field->getName();

        $this->assertSame('name', $result);
    }


    public function testGeterrorReturnsError(): void
    {
        $result = $this->field->getError();

        $this->assertSame('error', $result);
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
