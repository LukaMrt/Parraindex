<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact\field;

use App\Application\contact\field\NumberField;
use PHPUnit\Framework\TestCase;

final class NumberFieldTest extends TestCase
{
    private NumberField $numberField;


    #[\Override]
    protected function setUp(): void
    {
        $this->numberField = new NumberField('number', 'error');
    }


    public function testGetnameReturnsNumber(): void
    {
        $result = $this->numberField->getName();

        $this->assertSame('number', $result);
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
