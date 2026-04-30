<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Characteristic;

use App\Entity\Characteristic\CharacteristicType;
use PHPUnit\Framework\TestCase;

final class CharacteristicTypeTest extends TestCase
{
    public function testEqualsReturnsTrueForSameId(): void
    {
        // Given
        $type1 = new CharacteristicType();
        $type1->setId(1);

        $type2 = new CharacteristicType();
        $type2->setId(1);

        // When
        $result = $type1->equals($type2);

        // Then
        $this->assertTrue($result);
    }

    public function testEqualsReturnsFalseForDifferentId(): void
    {
        // Given
        $type1 = new CharacteristicType();
        $type1->setId(1);

        $type2 = new CharacteristicType();
        $type2->setId(2);

        // When
        $result = $type1->equals($type2);

        // Then
        $this->assertFalse($result);
    }
}
