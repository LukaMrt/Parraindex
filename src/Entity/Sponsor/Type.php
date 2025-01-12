<?php

declare(strict_types=1);

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
            self::UNKNOWN => 'Inconnu',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function allTitles(): array
    {
        return [
            self::HEART->value   => 'Parrainage de coeur',
            self::CLASSIC->value => 'Parrainage IUT',
            self::UNKNOWN->value => 'Inconnu',
        ];
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HEART   => 'heart.svg',
            self::CLASSIC => 'chain.svg',
            self::UNKNOWN => 'interrogation.svg',
        };
    }
}
