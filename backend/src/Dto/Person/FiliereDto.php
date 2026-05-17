<?php

declare(strict_types=1);

namespace App\Dto\Person;

final readonly class FiliereDto
{
    public function __construct(
        public string $name,
        public int $startYear,
        public ?int $endYear,
    ) {
    }
}
