<?php

namespace App\model\sponsor;

use App\model\person\Person;
use App\model\utils\Id;
use DateTime;

class HeartSponsor extends Sponsor {

    private Description $description;

    public function __construct(Id $id, Person $godFather, Person $godSon, DateTime $date, Description $description) {
        parent::__construct($id, $godFather, $godSon, $date);
        $this->description = $description;
    }

    public function describe(): string {
        return $this->description;
    }

}