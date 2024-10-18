<?php

declare(strict_types=1);

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
     * @var int Year of the promotion
     */
    private int $year;




    /**
     * @param int $id Id of the promotion
     * @param Degree $degree Degree objective of the promotion
     * @param School $school School of the promotion
     * @param int $year Year of the promotion
     * @param string $description Description of the promotion
     * @param Person ...$person Students of the promotion
     */
    public function __construct(
        int $id,
        Degree $degree,
        School $school,
        int $year,
        string $description,
        Person ...$person
    ) {
        $this->year        = $year;
    }


    /**
     * @return int Year of the promotion
     */
    public function getYear(): int
    {
        return $this->year;
    }


    #[\Override]
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
