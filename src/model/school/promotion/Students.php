<?php

namespace App\model\school\promotion;

use App\model\person\Person;

class Students {

    private array $students;

    public function __construct(Person... $students) {
        $this->students = $students;
    }

}