<?php

namespace App\Entity\Sponsor;

enum Type: int
{
    case HEART   = 0;
    case CLASSIC = 1;
    case UNKNOWN = 2;

    public function getTitle(): string
    {
        return match ($this) {
            self::HEART   => 'Parrainage de coeur',
            self::CLASSIC => 'Parrainage IUT',
            self::UNKNOWN => '',
        };
    }
}