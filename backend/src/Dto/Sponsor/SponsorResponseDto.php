<?php

declare(strict_types=1);

namespace App\Dto\Sponsor;

final readonly class SponsorResponseDto
{
    public function __construct(
        public int $id,
        public int $godFatherId,
        public string $godFatherName,
        public int $godChildId,
        public string $godChildName,
        public string $type,
        public ?string $date,
        public ?string $description,
    ) {
    }
}
