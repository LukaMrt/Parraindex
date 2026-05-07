<?php

declare(strict_types=1);

namespace App\Dto\Sponsor;

use App\Entity\Sponsor\Type;

enum SponsorTypeDto: string
{
    case HEART   = 'HEART';
    case CLASSIC = 'CLASSIC';
    case UNKNOWN = 'UNKNOWN';

    public function toEntity(): Type
    {
        return match ($this) {
            self::HEART   => Type::HEART,
            self::CLASSIC => Type::CLASSIC,
            self::UNKNOWN => Type::UNKNOWN,
        };
    }
}
