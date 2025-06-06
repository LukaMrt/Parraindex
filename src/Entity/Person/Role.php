<?php

declare(strict_types=1);

namespace App\Entity\Person;

enum Role: string
{
    case ADMIN = 'ROLE_ADMIN';
    case USER  = 'ROLE_USER';

    public function toString(): string
    {
        return match ($this) {
            self::ADMIN => 'ADMIN',
            self::USER => 'USER',
        };
    }
}
