<?php

namespace unit\application\contact\field;

use App\application\contact\field\DateField;
use PHPUnit\Framework\TestCase;

class DateFieldTest extends TestCase
{
    private DateField $dateField;


    public function setUp(): void
    {
        $this->dateField = new DateField('date', 'error');
    }


    public function testGetnameReturnsDate()
    {
        $result = $this->dateField->getName();

        $this->assertEquals('date', $result);
    }


    public function testIsvalidReturnsTrueWhenDateIsValid(): void
    {
        $result = $this->dateField->isValid('2020-01-01');

        $this->assertTrue($result);
    }


    public function testIsvalidReturnsTrueWhenDateIsFirstJanuary2010(): void
    {
        $result = $this->dateField->isValid('2010-01-01');

        $this->assertTrue($result);
    }


    public function testIsvalidReturnsFalseWhenDateIsInvalid(): void
    {
        $result = $this->dateField->isValid('2020-01-32');

        $this->assertFalse($result);
    }


    public function testIsvalidReturnsFalseWhenDateIsIn2004(): void
    {
        $result = $this->dateField->isValid('2005-01-01');

        $this->assertFalse($result);
    }
}
