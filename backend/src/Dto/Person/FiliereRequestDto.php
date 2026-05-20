<?php

declare(strict_types=1);

namespace App\Dto\Person;

use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(
    expression: 'this.endYear === null || this.endYear > this.startYear',
    message: "L'année de fin doit être supérieure à l'année de début.",
)]
final readonly class FiliereRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,
        #[Assert\NotNull]
        #[Assert\Range(min: 1900, max: 2100)]
        public ?int $startYear,
        #[Assert\Range(min: 1900, max: 2100)]
        public ?int $endYear,
    ) {
    }
}
