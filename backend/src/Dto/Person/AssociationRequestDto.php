<?php

declare(strict_types=1);

namespace App\Dto\Person;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AssociationRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $poste,
        #[Assert\Date]
        public ?string $startDate = null,
        #[Assert\Date]
        public ?string $endDate = null,
    ) {
    }
}
