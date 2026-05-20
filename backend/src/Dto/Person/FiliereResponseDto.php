<?php

declare(strict_types=1);

namespace App\Dto\Person;

final readonly class FiliereResponseDto
{
    public function __construct(
        public string $name,
        public ?string $color,
        public ?int $startYear,
        public ?int $endYear,
        public ?string $schoolName,
        public ?string $schoolLogoUrl,
    ) {
    }
}
