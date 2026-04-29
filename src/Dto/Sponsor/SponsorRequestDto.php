<?php

declare(strict_types=1);

namespace App\Dto\Sponsor;

use App\Entity\Sponsor\Type;
use Symfony\Component\Validator\Constraints as Assert;

final class SponsorRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $godFatherId,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $godChildId,

        public readonly Type $type = Type::CLASSIC,

        public readonly ?string $description = null,

        public readonly ?string $date = null,
    ) {
    }
}
