<?php

namespace App\model\person;

use App\model\association\Associations;
use App\model\family\Families;
use App\model\sponsor\Sponsors;
use App\model\person\characteristic\Characteristics;
use App\model\utils\Id;
use App\model\utils\Image;
use DateTime;

class Person {

    private Id $id;
    private Names $name;
    private DateTime $birthDate;
    private Biography $biography;
    private Image $picture;
    private Characteristics $characteristics;
    private Sponsors $sponsors;
    private Families $families;
    private Associations $associations;

}