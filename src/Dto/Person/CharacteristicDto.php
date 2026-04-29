<?php

declare(strict_types=1);

namespace App\Dto\Person;

final readonly class CharacteristicDto
{
    public function __construct(
        public int $id,
        public ?string $value,
        public bool $visible,
        public string $typeTitle,
        public ?string $typeUrl,
    ) {
    }
}
