<?php

namespace unit\application\contact\field;

use App\application\contact\field\NumberField;
use PHPUnit\Framework\TestCase;

class NumberFieldTest extends TestCase
{
    private NumberField $numberField;


    public function setUp(): void
    {
        $this->numberField = new NumberField('number', 'error');
    }


    public function testGetnameReturnsNumber()
    {
        $result = $this->numberField->getName();

        $this->assertEquals('number', $result);
    }


    public function testIsvalidReturnsTrueWhenValueIsNumeric(): void
    {
        $result = $this->numberField->isValid('123');

        $this->assertTrue($result);
    }


    public function testIsvalidReturnsFalseWhenValueIsNotNumeric(): void
    {
        $result = $this->numberField->isValid('not a number');

        $this->assertFalse($result);
    }


    public function testIsvalidReturnsFalseWhenValueIsEmpty(): void
    {
        $result = $this->numberField->isValid('');

        $this->assertFalse($result);
    }
}
