<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Sponsor;

use App\Entity\Sponsor\Sponsor;
use PHPUnit\Framework\TestCase;

final class SponsorTest extends TestCase
{
    public function testFormatDateReturnsFormattedDateWhenDateIsSet(): void
    {
        // Given
        $sponsor = new Sponsor();
        $date    = new \DateTime('2024-01-15');
        $sponsor->setDate($date);

        // When
        $result = $sponsor->formatDate('Y-m-d');

        // Then
        $this->assertSame('2024-01-15', $result);
    }

    public function testFormatDateReturnsEmptyStringWhenDateIsNull(): void
    {
        // Given
        $sponsor = new Sponsor();

        // When
        $result = $sponsor->formatDate('Y-m-d');

        // Then
        $this->assertSame('', $result);
    }

    public function testFormatDateSupportsCustomFormat(): void
    {
        // Given
        $sponsor = new Sponsor();
        $date    = new \DateTime('2024-01-15 14:30:45');
        $sponsor->setDate($date);

        // When
        $result = $sponsor->formatDate('d/m/Y H:i');

        // Then
        $this->assertSame('15/01/2024 14:30', $result);
    }
}
