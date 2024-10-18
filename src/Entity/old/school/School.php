<?php

namespace App\Entity\old\school;

use App\Entity\old\person\Person;
use DateTime;

/**
 * School class
 */
class School
{
    /**
     * @var int Id of the school
     */
    private int $id;
    /**
     * @var string Name of the school
     */
    private string $name;
    /**
     * @var SchoolAddress Address of the school
     */
    private SchoolAddress $address;
    /**
     * @var DateTime Date of creation of the school
     */
    private DateTime $creationDate;
    /**
     * @var Person|null Director of the school
     */
    private ?Person $director;


    /**
     * @param int $id Id of the school
     * @param string $name Name of the school
     * @param SchoolAddress $address Address of the school
     * @param DateTime $creationDate Date of creation of the school
     * @param Person|null $director Director of the school
     */
    public function __construct(
        int $id,
        string $name,
        SchoolAddress $address,
        DateTime $creationDate,
        ?Person $director = null
    ) {
        $this->id           = $id;
        $this->name         = $name;
        $this->address      = $address;
        $this->creationDate = $creationDate;
        $this->director     = $director;
    }
}
