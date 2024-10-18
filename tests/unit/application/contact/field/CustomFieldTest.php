<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact\field;

use App\Application\contact\field\CustomField;
use PHPUnit\Framework\TestCase;

final class CustomFieldTest extends TestCase
{
    private CustomField $customField;


    public function testGetnameReturnsCustom(): void
    {
        $this->customField = new CustomField('custom', 'error', fn(): true => true);

        $result = $this->customField->getName();

        $this->assertSame('custom', $result);
    }


    public function testIsvalidReturnsTrueWhenCustomTestReturnsTrue(): void
    {
        $this->customField = new CustomField('custom', 'error', fn(): true => true);

        $result = $this->customField->isValid('test');

        $this->assertTrue($result);
    }


    public function testIsvalidReturnsFalseWhenCustomTestReturnsFalse(): void
    {
        $this->customField = new CustomField('custom', 'error', fn(): false => false);

        $result = $this->customField->isValid('test');

        $this->assertFalse($result);
    }
}
