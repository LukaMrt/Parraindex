<?php

declare(strict_types=1);

namespace App\Dto\Sponsor;

use App\Entity\Sponsor\Type;
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

        public Type $type = Type::CLASSIC,

        public ?string $description = null,

        public ?string $date = null,
    ) {
    }
}
