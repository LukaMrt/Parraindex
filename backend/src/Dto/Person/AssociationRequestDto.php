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
    ) {
    }
}
