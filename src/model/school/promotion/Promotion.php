<?php

namespace App\model\school\promotion;

use App\model\school\degree\Degree;
use App\model\school\School;
use App\model\utils\Id;

class Promotion {

    private Id $id;
    private Degree $degree;
    private School $school;
    private Year $year;
    private PromotionDescription $description;
    private Students $students;

    public function __construct(Id $id, Degree $degree, School $school, Year $year, PromotionDescription $description, Students $students) {
        $this->id = $id;
        $this->degree = $degree;
        $this->school = $school;
        $this->year = $year;
        $this->description = $description;
        $this->students = $students;
    }


}