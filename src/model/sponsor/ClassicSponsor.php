<?php

namespace App\model\sponsor;

use App\model\user\User;
use App\model\utils\Id;
use DateTime;

class ClassicSponsor extends Sponsor {

    private Description $reason;

    public function __construct(Id $id, User $godFather, User $godSon, DateTime $date, Description $description) {
        parent::__construct($id, $godFather, $godSon, $date);
        $this->reason = $description;
    }

    public function describe(): string {
        return $this->reason;
    }

}