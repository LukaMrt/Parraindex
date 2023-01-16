<?php

namespace unit\application\contact\field;

use App\application\contact\field\BoundedNumberField;
use PHPUnit\Framework\TestCase;

class BoundedNumberFieldTest extends TestCase
{
    private BoundedNumberField $boundedField;


    public function setUp(): void
    {
        $this->boundedField = new BoundedNumberField('bounded', 'error', 0, 10);
    }


    public function testGetnameReturnsBounded()
    {
        $result = $this->boundedField->getName();

        $this->assertEquals('bounded', $result);
    }


    public function testIsValidReturnsTrueWhenValueIsWithinBounds()
    {
        $result = $this->boundedField->isValid(5);

        $this->assertTrue($result);
    }


    public function testIsValidReturnsTrueWhenValueIsBottomBound()
    {
        $result = $this->boundedField->isValid(0);

        $this->assertTrue($result);
    }


    public function testIsValidReturnsTrueWhenValueIsTopBound()
    {
        $result = $this->boundedField->isValid(10);

        $this->assertTrue($result);
    }


    public function testIsValidReturnsFalseWhenValueIsBelowBounds()
    {
        $result = $this->boundedField->isValid(-1);

        $this->assertFalse($result);
    }


    public function testIsValidReturnsFalseWhenValueIsAboveBounds()
    {
        $result = $this->boundedField->isValid(11);

        $this->assertFalse($result);
    }
}
