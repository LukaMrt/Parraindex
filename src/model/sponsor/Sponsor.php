<?php

namespace App\model\sponsor;

use App\model\user\User;
use App\model\utils\Id;
use DateTime;

abstract class Sponsor {

    private Id $id;
    private User $godFather;
    private User $godSon;
    private DateTime $date;

    protected function __construct(Id $id, User $godFather, User $godSon, DateTime $date) {
        $this->id = $id;
        $this->godFather = $godFather;
        $this->godSon = $godSon;
        $this->date = $date;
    }

    abstract public function describe(): string;

}