<?php

declare(strict_types=1);

namespace App\Dto\Person;

use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: PersonSummaryDto::class)]
final readonly class PersonSummaryDto
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $fullName,
        public ?string $picture,
        public string $color,
        public int $startYear,
    ) {
    }
}
