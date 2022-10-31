<?php

namespace App\model\school;

use App\model\person\Person;
use App\model\utils\Id;
use DateTime;

class School {

    private Id $id;
    private SchoolName $name;
    private SchoolAddress $address;
    private DateTime $creationDate;
    private ?Person $director;

}