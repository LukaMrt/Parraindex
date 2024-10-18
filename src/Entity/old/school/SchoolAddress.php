<?php

namespace App\Entity\old\school;

/**
 * Address of a school
 */
class SchoolAddress
{
    /**
     * @var string Street name of the school
     */
    private string $street;
    /**
     * @var string City of the school
     */
    private string $city;


    /**
     * @param string $street Street name of the school
     * @param string $city City of the school
     */
    public function __construct(string $street, string $city)
    {
        $this->street = $street;
        $this->city   = $city;
    }


    /**
     * @return SchoolAddress A default empty address
     */
    public static function emptyAddress(): SchoolAddress
    {
        return new SchoolAddress('', '');
    }
}
