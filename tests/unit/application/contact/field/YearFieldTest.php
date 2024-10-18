<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact\field;

use App\Application\contact\field\YearField;
use PHPUnit\Framework\TestCase;

final class YearFieldTest extends TestCase
{
    private YearField $yearField;


    #[\Override]
    protected function setUp(): void
    {
        $this->yearField = new YearField('year', 'error');
    }


    public function testGetnameReturnsYear(): void
    {
        $result = $this->yearField->getName();

        $this->assertSame('year', $result);
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
