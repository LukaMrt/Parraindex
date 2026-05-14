<?php

declare(strict_types=1);

namespace App\Entity\Sponsor;

enum Type: int
{
    case HEART   = 0;
    case CLASSIC = 1;
    case UNKNOWN = 2;
    case FALUCHE = 3;

    public function getTitle(): string
    {
        return match ($this) {
            self::HEART    => 'Parrainage de coeur',
            self::CLASSIC  => 'Parrainage IUT',
            self::UNKNOWN  => 'Inconnu',
            self::FALUCHE  => 'Faluche',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function allTitles(): array
    {
        return [
            self::HEART->value    => 'Parrainage de coeur',
            self::CLASSIC->value  => 'Parrainage IUT',
            self::UNKNOWN->value  => 'Inconnu',
            self::FALUCHE->value  => 'Faluche',
        ];
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HEART    => 'heart.svg',
            self::CLASSIC  => 'chain.svg',
            self::UNKNOWN  => 'interrogation.svg',
            self::FALUCHE  => 'faluche.svg',
        };
    }
}
