<?php

namespace App\model\school\degree;

class Degree
{

    private int $id;
    private string $name;
    private int $level;
    private int $ects;
    private int $duration;
    private bool $official;


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
