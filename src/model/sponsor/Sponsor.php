<?php

namespace App\model\sponsor;

use App\model\person\Person;
use DateTime;

abstract class Sponsor
{

    private int $id;
    private Person $godFather;
    private Person $godSon;
    private ?DateTime $date;


    protected function __construct(int $id, Person $godFather, Person $godSon, string $date)
    {
        $this->id = $id;
        $this->godFather = $godFather;
        $this->godSon = $godSon;
        $this->date = null;

        if ($date) {
            $this->date = DateTime::createFromFormat("Y-m-d", $date);
        }
    }


    public function getId(): int
    {
        return $this->id;
    }


    public function getGodFather(): Person
    {
        return $this->godFather;
    }


    public function setGodFather(?Person $godFather): void
    {
        $this->godFather = $godFather ?? $this->godFather;
    }


    public function getGodChild(): Person
    {
        return $this->godSon;
    }


    public function getDate(): DateTime
    {
        return $this->date;
    }


    public function formatDate(string $format): string
    {

        if ($this->date) {
            return $this->date->format($format);
        }

        return '';
    }


    abstract public function getType(): string;


    abstract public function getTypeId(): int;


    abstract public function getDescriptionTitle(): string;


    abstract public function getDescription(): string;


    abstract public function getIcon(): string;


    public function setGodSon(?Person $godSon): void
    {
        $this->godSon = $godSon ?? $this->godSon;
    }

}
