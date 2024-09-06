<?php

namespace App\Entity\school\degree;

/**
 * Represents a school degree
 */
class Degree
{
    /**
     * @var int Id of the degree
     */
    private int $id;
    /**
     * @var string Name of the degree
     */
    private string $name;
    /**
     * @var int Number of years after secondary school to complete the degree
     */
    private int $level;
    /**
     * @var int Number of ects of the degree
     */
    private int $ects;
    /**
     * @var int Number of years of the degree
     */
    private int $duration;
    /**
     * @var bool Whether the degree is recognized by the state or not
     */
    private bool $official;


    /**
     * @param int $id Id of the degree
     * @param string $name Name of the degree
     * @param int $level Number of years after secondary school to complete the degree
     * @param int $ects Number of ects of the degree
     * @param int $duration Number of years of the degree
     * @param bool $official Whether the degree is recognized by the state or not
     */
    public function __construct(int $id, string $name, int $level, int $ects, int $duration, bool $official)
    {
        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->ects = $ects;
        $this->duration = $duration;
        $this->official = $official;
    }
}
