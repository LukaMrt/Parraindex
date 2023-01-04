<?php

namespace App\model\sponsor;

use App\model\person\Person;

class SponsorFactory
{
    public static function createSponsor(
        int $type,
        int $id,
        Person $godFather,
        Person $godChild,
        string $date,
        string $description
    ): Sponsor {

        return match ($type) {
            0 => new ClassicSponsor($id, $godFather, $godChild, $date, $description),
            1 => new HeartSponsor($id, $godFather, $godChild, $date, $description),
            default => new UnknownSponsor($id, $godFather, $godChild, $date),
        };
    }
}
