<?php

declare(strict_types=1);

namespace App\Dto\Person;

final readonly class AssociationResponseDto
{
    public function __construct(
        public string $name,
        public ?string $logoUrl,
        public string $poste,
    ) {
    }
}
