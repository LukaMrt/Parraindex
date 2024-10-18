<?php

namespace App\Entity\old\school\promotion;

use App\Entity\old\person\Person;
use App\Entity\old\school\degree\Degree;
use App\Entity\old\school\School;
use JsonSerializable;

/**
 * Promotion of students in a school
 */
class Promotion implements JsonSerializable
{
    /**
     * @var int Id of the promotion
     */
    private int $id;
    /**
     * @var Degree Degree objective of the promotion
     */
    private Degree $degree;
    /**
     * @var School School of the promotion
     */
    private School $school;
    /**
     * @var int Year of the promotion
     */
    private int $year;
    /**
     * @var string Description of the promotion
     */
    private string $description;
    /**
     * @var Person[] Students of the promotion
     */
    private array $students;


    /**
     * @param int $id Id of the promotion
     * @param Degree $degree Degree objective of the promotion
     * @param School $school School of the promotion
     * @param int $year Year of the promotion
     * @param string $description Description of the promotion
     * @param Person ...$students Students of the promotion
     */
    public function __construct(
        int $id,
        Degree $degree,
        School $school,
        int $year,
        string $description,
        Person ...$students
    ) {
        $this->id          = $id;
        $this->degree      = $degree;
        $this->school      = $school;
        $this->year        = $year;
        $this->description = $description;
        $this->students    = $students;
    }


    /**
     * @return int Year of the promotion
     */
    public function getYear(): int
    {
        return $this->year;
    }


    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
