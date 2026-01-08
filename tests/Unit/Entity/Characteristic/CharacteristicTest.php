<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Characteristic;

use App\Entity\Characteristic\Characteristic;
use PHPUnit\Framework\TestCase;

final class CharacteristicTest extends TestCase
{
    public function testToStringReturnsValueWhenSet(): void
    {
        // Given
        $characteristic = new Characteristic();
        $characteristic->setValue('test@example.com');

        // When
        $result = (string) $characteristic;

        // Then
        $this->assertSame('test@example.com', $result);
    }

    public function testToStringReturnsEmptyStringWhenValueIsNull(): void
    {
        // Given
        $characteristic = new Characteristic();

        // When
        $result = (string) $characteristic;

        // Then
        $this->assertSame('', $result);
    }
}
