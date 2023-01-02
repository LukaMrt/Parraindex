<?php

namespace unit\application\contact\field;

use App\application\contact\field\YearField;
use PHPUnit\Framework\TestCase;

class YearFieldTest extends TestCase
{

    private YearField $yearField;

    public function setUp(): void
    {
        $this->yearField = new YearField('year', 'error');
    }

    public function testGetnameReturnsYear()
    {
        $result = $this->yearField->getName();

        $this->assertEquals('year', $result);
    }

    public function testIsvalidReturnTrueWhenYearIs2010(): void
    {
        $result = $this->yearField->isValid('2010');

        $this->assertTrue($result);
    }

    public function testIsvalidReturnTrueWhenYearIsCurrentYear(): void
    {
        $result = $this->yearField->isValid(date('Y'));

        $this->assertTrue($result);
    }

    public function testIsvalidReturnFalseWhenYearIs2009(): void
    {
        $result = $this->yearField->isValid('2009');

        $this->assertFalse($result);
    }

    public function testIsvalidReturnFalseWhenYearIsNotNumeric(): void
    {
        $result = $this->yearField->isValid('');

        $this->assertFalse($result);
    }

}