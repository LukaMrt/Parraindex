<?php

declare(strict_types=1);

namespace App\Dto\Sponsor;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class SponsorRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $godFatherId,
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $godChildId,
        public SponsorTypeDto $type = SponsorTypeDto::CLASSIC,
        public ?string $description = null,
        public ?string $date = null,
    ) {
    }
}
