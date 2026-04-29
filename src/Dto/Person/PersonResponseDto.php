<?php

declare(strict_types=1);

namespace App\Dto\Person;

use App\Dto\Sponsor\SponsorSummaryDto;

final readonly class PersonResponseDto
{
    /**
     * @param SponsorSummaryDto[] $godFathers
     * @param SponsorSummaryDto[] $godChildren
     * @param CharacteristicDto[] $characteristics
     */
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $fullName,
        public ?string $picture,
        public string $color,
        public int $startYear,
        public ?string $birthdate,
        public ?string $biography,
        public ?string $description,
        public array $godFathers,
        public array $godChildren,
        public array $characteristics,
    ) {
    }
}
