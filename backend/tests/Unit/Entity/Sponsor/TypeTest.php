<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Sponsor;

use App\Entity\Sponsor\Type;
use PHPUnit\Framework\TestCase;

final class TypeTest extends TestCase
{
    public function testGetTitleReturnsCorrectTitleForHeart(): void
    {
        // Given
        $type = Type::HEART;

        // When
        $result = $type->getTitle();

        // Then
        $this->assertSame('Parrainage de coeur', $result);
    }

    public function testGetTitleReturnsCorrectTitleForClassic(): void
    {
        // Given
        $type = Type::CLASSIC;

        // When
        $result = $type->getTitle();

        // Then
        $this->assertSame('Parrainage IUT', $result);
    }

    public function testGetTitleReturnsCorrectTitleForUnknown(): void
    {
        // Given
        $type = Type::UNKNOWN;

        // When
        $result = $type->getTitle();

        // Then
        $this->assertSame('Inconnu', $result);
    }

    public function testGetIconReturnsCorrectIconForHeart(): void
    {
        // Given
        $type = Type::HEART;

        // When
        $result = $type->getIcon();

        // Then
        $this->assertSame('heart.svg', $result);
    }

    public function testGetIconReturnsCorrectIconForClassic(): void
    {
        // Given
        $type = Type::CLASSIC;

        // When
        $result = $type->getIcon();

        // Then
        $this->assertSame('chain.svg', $result);
    }

    public function testGetIconReturnsCorrectIconForUnknown(): void
    {
        // Given
        $type = Type::UNKNOWN;

        // When
        $result = $type->getIcon();

        // Then
        $this->assertSame('interrogation.svg', $result);
    }

    public function testAllTitlesReturnsAssociativeArrayWithThreeEntries(): void
    {
        // Given & When
        $result = Type::allTitles();

        // Then
        $this->assertCount(3, $result);
        $this->assertArrayHasKey(0, $result); // HEART
        $this->assertArrayHasKey(1, $result); // CLASSIC
        $this->assertArrayHasKey(2, $result); // UNKNOWN
        $this->assertSame('Parrainage de coeur', $result[0]);
        $this->assertSame('Parrainage IUT', $result[1]);
        $this->assertSame('Inconnu', $result[2]);
    }
}
