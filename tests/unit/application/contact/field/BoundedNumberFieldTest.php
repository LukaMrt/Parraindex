<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact\field;

use App\Application\contact\field\BoundedNumberField;
use PHPUnit\Framework\TestCase;

final class BoundedNumberFieldTest extends TestCase
{
    private BoundedNumberField $boundedNumberField;


    #[\Override]
    protected function setUp(): void
    {
        $this->boundedNumberField = new BoundedNumberField('bounded', 'error', 0, 10);
    }


    public function testGetnameReturnsBounded(): void
    {
        $result = $this->boundedNumberField->getName();

        $this->assertSame('bounded', $result);
    }


    public function testIsValidReturnsTrueWhenValueIsWithinBounds(): void
    {
        $result = $this->boundedNumberField->isValid(5);

        $this->assertTrue($result);
    }


    public function testIsValidReturnsTrueWhenValueIsBottomBound(): void
    {
        $result = $this->boundedNumberField->isValid(0);

        $this->assertTrue($result);
    }


    public function testIsValidReturnsTrueWhenValueIsTopBound(): void
    {
        $result = $this->boundedNumberField->isValid(10);

        $this->assertTrue($result);
    }


    public function testIsValidReturnsFalseWhenValueIsBelowBounds(): void
    {
        $result = $this->boundedNumberField->isValid(-1);

        $this->assertFalse($result);
    }


    public function testIsValidReturnsFalseWhenValueIsAboveBounds(): void
    {
        $result = $this->boundedNumberField->isValid(11);

        $this->assertFalse($result);
    }
}
