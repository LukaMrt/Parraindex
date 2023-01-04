<?php

namespace App\model\sponsor;

use App\model\person\Person;

class ClassicSponsor extends Sponsor
{
    private string $reason;


    public function __construct(int $id, Person $godFather, Person $godSon, string $date, string $description)
    {
        parent::__construct($id, $godFather, $godSon, $date);
        $this->reason = $description;
    }


    public function getDescription(): string
    {
        return $this->reason;
    }


    public function getType(): string
    {
        return 'Parrainage IUT';
    }


    public function getDescriptionTitle(): string
    {
        return 'Raison';
    }


    public function getIcon(): string
    {
        return 'hammers.svg';
    }


    public function getTypeId(): int
    {
        return 0;
    }
}
