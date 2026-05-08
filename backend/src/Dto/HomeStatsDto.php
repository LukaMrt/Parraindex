<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class HomeStatsDto
{
    /**
     * @param array<array{startYear: int, count: int}> $promoGroups
     */
    public function __construct(
        public int $totalPersons,
        public int $totalPromos,
        public array $promoGroups,
    ) {
    }
}
