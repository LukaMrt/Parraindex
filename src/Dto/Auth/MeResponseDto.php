<?php

declare(strict_types=1);

namespace App\Dto\Auth;

use App\Dto\Person\PersonSummaryDto;

final readonly class MeResponseDto
{
    public function __construct(
        public int $id,
        public string $email,
        public bool $isAdmin,
        public bool $isVerified,
        public PersonSummaryDto $person,
    ) {
    }
}
