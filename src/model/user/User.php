<?php

namespace App\model\user;

use App\model\sponsor\Sponsors;
use App\model\user\characteristic\Characteristics;
use App\model\utils\Id;
use App\model\utils\Image;
use DateTime;

class User {

    private Id $id;
    private Names $name;
    private DateTime $birthDate;
    private Biography $biography;
    private Image $picture;
    private Characteristics $characteristics;
    private Sponsors $sponsors;

}