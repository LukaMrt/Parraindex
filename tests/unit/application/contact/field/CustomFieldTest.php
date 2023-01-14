<?php

namespace unit\application\contact\field;

use App\application\contact\field\CustomField;
use PHPUnit\Framework\TestCase;

class CustomFieldTest extends TestCase
{
    private CustomField $customField;


    public function testGetnameReturnsCustom()
    {
        $this->customField = new CustomField('custom', 'error', fn() => true);

        $result = $this->customField->getName();

        $this->assertEquals('custom', $result);
    }


    public function testIsvalidReturnsTrueWhenCustomTestReturnsTrue()
    {
        $this->customField = new CustomField('custom', 'error', fn() => true);

        $result = $this->customField->isValid('test');

        $this->assertTrue($result);
    }


    public function testIsvalidReturnsFalseWhenCustomTestReturnsFalse()
    {
        $this->customField = new CustomField('custom', 'error', fn() => false);

        $result = $this->customField->isValid('test');

        $this->assertFalse($result);
    }
}
