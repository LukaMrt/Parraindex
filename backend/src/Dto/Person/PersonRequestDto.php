<?php

declare(strict_types=1);

namespace App\Dto\Person;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PersonRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $firstName,
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $lastName,
        #[Assert\Positive]
        #[Assert\Range(min: 1900, max: 2100)]
        public int $startYear,
        public ?string $biography = null,
        public ?string $description = null,
        #[Assert\CssColor([Assert\CssColor::HEX_LONG])]
        public ?string $color = null,
    ) {
    }
}
