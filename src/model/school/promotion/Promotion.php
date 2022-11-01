<?php

namespace App\model\school\promotion;

use App\model\person\Person;
use App\model\school\degree\Degree;
use App\model\school\School;
use App\model\utils\Id;

class Promotion {

    private Id $id;
    private Degree $degree;
    private School $school;
    private int $year;
    private string $description;
    private array $students;

    public function __construct(Id $id, Degree $degree, School $school, int $year, string $description, Person... $students) {
        $this->id = $id;
        $this->degree = $degree;
        $this->school = $school;
        $this->year = $year;
        $this->description = $description;
        $this->students = $students;
    }

}