<?php

declare(strict_types=1);

namespace App\Dto\Person;

use Symfony\Component\Validator\Constraints as Assert;

final class PersonRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public readonly string $firstName,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public readonly string $lastName,

        #[Assert\Positive]
        #[Assert\Range(min: 1900, max: 2100)]
        public readonly int $startYear,

        public readonly ?string $biography = null,

        public readonly ?string $description = null,

        #[Assert\CssColor([Assert\CssColor::HEX_LONG])]
        public readonly ?string $color = null,
    ) {
    }
}
