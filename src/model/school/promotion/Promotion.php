<?php

namespace App\model\school\promotion;

use App\model\person\Person;
use App\model\school\degree\Degree;
use App\model\school\School;

class Promotion
{
    private int $id;
    private Degree $degree;
    private School $school;
    private int $year;
    private string $description;
    private array $students;

    public function __construct(
        int    $id,
        Degree $degree,
        School $school,
        int    $year,
        string $description,
        Person ...$students
    )
    {
        $this->id = $id;
        $this->degree = $degree;
        $this->school = $school;
        $this->year = $year;
        $this->description = $description;
        $this->students = $students;
    }

    public function getYear(): int
    {
        return $this->year;
    }
}
