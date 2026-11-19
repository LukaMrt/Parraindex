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
        self::assertSame('Parrainage de coeur', $result);
    }

    public function testGetTitleReturnsCorrectTitleForClassic(): void
    {
        // Given
        $type = Type::CLASSIC;

        // When
        $result = $type->getTitle();

        // Then
        self::assertSame('Parrainage IUT', $result);
    }

    public function testGetTitleReturnsCorrectTitleForUnknown(): void
    {
        // Given
        $type = Type::UNKNOWN;

        // When
        $result = $type->getTitle();

        // Then
        self::assertSame('Inconnu', $result);
    }

    public function testGetIconReturnsCorrectIconForHeart(): void
    {
        // Given
        $type = Type::HEART;

        // When
        $result = $type->getIcon();

        // Then
        self::assertSame('heart.svg', $result);
    }

    public function testGetIconReturnsCorrectIconForClassic(): void
    {
        // Given
        $type = Type::CLASSIC;

        // When
        $result = $type->getIcon();

        // Then
        self::assertSame('chain.svg', $result);
    }

    public function testGetIconReturnsCorrectIconForUnknown(): void
    {
        // Given
        $type = Type::UNKNOWN;

        // When
        $result = $type->getIcon();

        // Then
        self::assertSame('interrogation.svg', $result);
    }

    public function testGetTitleReturnsCorrectTitleForFaluche(): void
    {
        // Given
        $type = Type::FALUCHE;

        // When
        $result = $type->getTitle();

        // Then
        self::assertSame('Faluche', $result);
    }

    public function testGetIconReturnsCorrectIconForFaluche(): void
    {
        // Given
        $type = Type::FALUCHE;

        // When
        $result = $type->getIcon();

        // Then
        self::assertSame('faluche.svg', $result);
    }
}
