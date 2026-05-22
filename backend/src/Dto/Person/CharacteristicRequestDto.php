<?php

declare(strict_types=1);

namespace App\Dto\Person;

final readonly class CharacteristicRequestDto
{
    public function __construct(
        public ?int $id = null,
        public ?int $typeId = null,
        public ?string $value = null,
        public bool $visible = false,
    ) {
    }
}
