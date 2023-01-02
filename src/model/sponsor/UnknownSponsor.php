<?php

namespace App\model\sponsor;

use App\model\person\Person;

class UnknownSponsor extends Sponsor
{
    public function __construct(int $id, Person $godFather, Person $godChild, string $date = '')
    {
        parent::__construct($id, $godFather, $godChild, $date);
    }

    public function getDescriptionTitle(): string
    {
        return $this->getType();
    }

    public function getType(): string
    {
        return '';
    }

    public function getDescription(): string
    {
        return $this->getType();
    }

    public function getIcon(): string
    {
        return 'interrogation.svg';
    }

    public function getTypeId(): int
    {
        return 2;
    }
}
