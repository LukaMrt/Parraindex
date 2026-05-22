<?php

declare(strict_types=1);

namespace App\Dto\Person;

final readonly class PersonLinkDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {
    }
}
