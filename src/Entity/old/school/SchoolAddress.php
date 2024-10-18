<?php

declare(strict_types=1);

namespace App\Entity\old\school;

/**
 * Address of a school
 */
class SchoolAddress
{

    /**
     * @return SchoolAddress A default empty address
     */
    public static function emptyAddress(): SchoolAddress
    {
        return new SchoolAddress('', '');
    }
}
