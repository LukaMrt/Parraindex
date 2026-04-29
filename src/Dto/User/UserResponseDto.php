<?php

declare(strict_types=1);

namespace App\Dto\User;

final readonly class UserResponseDto
{
    public function __construct(
        public int $id,
        public string $email,
        public bool $isAdmin,
        public bool $isVerified,
        public int $personId,
        public string $personFullName,
        public ?string $picture,
        public string $createdAt,
    ) {
    }
}
