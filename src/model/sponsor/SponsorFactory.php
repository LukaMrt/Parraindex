<?php

namespace App\model\sponsor;

use App\model\person\Person;

/**
 * Factory for sponsors
 */
class SponsorFactory
{
    /**
     * @param int $type Type of the sponsor
     * @param int $id Id of the sponsor
     * @param Person $godFather Godfather of the sponsor
     * @param Person $godChild Godchild of the sponsor
     * @param string $date Date of the sponsor
     * @param string $description Description of the sponsor
     * @return Sponsor The sponsor corresponding to the given type
     */
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
