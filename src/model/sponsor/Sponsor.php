<?php

namespace App\model\sponsor;

use App\model\person\Person;
use App\model\utils\Id;
use DateTime;

abstract class Sponsor {

    private Id $id;
    private Person $godFather;
    private Person $godSon;
    private DateTime $date;

    protected function __construct(Id $id, Person $godFather, Person $godSon, DateTime $date) {
        $this->id = $id;
        $this->godFather = $godFather;
        $this->godSon = $godSon;
        $this->date = $date;
    }

    abstract public function describe(): string;

}