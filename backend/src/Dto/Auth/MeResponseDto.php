<?php

declare(strict_types=1);

namespace App\Dto\Auth;

use App\Dto\Person\PersonResponseDto;

final readonly class MeResponseDto
{
    public function __construct(
        public int $id,
        public string $email,
        public bool $isAdmin,
        public bool $isVerified,
        public PersonResponseDto $person,
    ) {
    }
}
