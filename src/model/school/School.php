<?php

namespace App\model\school;

use App\model\person\Person;
use DateTime;

class School
{
    private int $id;
    private string $name;
    private SchoolAddress $address;
    private DateTime $creationDate;
    private ?Person $director;


    public function __construct(
        int $id,
        string $name,
        SchoolAddress $address,
        DateTime $creationDate,
        ?Person $director = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->creationDate = $creationDate;
        $this->director = $director;
    }


    public static function emptySchool(): School
    {
        return new School(0, '', SchoolAddress::emptyAddress(), new DateTime());
    }
}
