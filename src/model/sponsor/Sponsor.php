<?php

namespace App\model\sponsor;

use App\model\person\Person;
use DateTime;

/**
 * Represents a sponsor
 */
abstract class Sponsor
{
    /**
     * @var int The id of the sponsor
     */
    private int $id;
    /**
     * @var Person The godfather of the sponsor
     */
    private Person $godFather;
    /**
     * @var Person The godchild of the sponsor
     */
    private Person $godSon;
    /**
     * @var DateTime|null The date of the sponsor
     */
    private ?DateTime $date;


    /**
     * @param int $id The id of the sponsor
     * @param Person $godFather The godfather of the sponsor
     * @param Person $godSon The godchild of the sponsor
     * @param string $date The date of the sponsor
     */
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


    /**
     * @return int The id of the sponsor
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return Person The godfather of the sponsor
     */
    public function getGodFather(): Person
    {
        return $this->godFather;
    }


    /**
     * Sets the godfather of the sponsor
     * @param Person|null $godFather The new godfather of the sponsor
     * @return void
     */
    public function setGodFather(?Person $godFather): void
    {
        $this->godFather = $godFather ?? $this->godFather;
    }


    /**
     * @return Person The godchild of the sponsor
     */
    public function getGodChild(): Person
    {
        return $this->godSon;
    }


    /**
     * Sets the godchild of the sponsor
     * @param Person|null $godSon The new godchild of the sponsor
     * @return void
     */
    public function setGodSon(?Person $godSon): void
    {
        $this->godSon = $godSon ?? $this->godSon;
    }


    /**
     * @return ?DateTime The date of the sponsor
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }


    /**
     * Formats the date of the sponsor
     * Used in the twig view
     * @param string $format The format to use
     * @return string The formatted date of the sponsor
     */
    public function formatDate(string $format): string
    {

        if ($this->date) {
            return $this->date->format($format);
        }

        return '';
    }


    /**
     * @return string The type of the sponsor
     */
    abstract public function getType(): string;


    /**
     * @return int The type id of the sponsor
     */
    abstract public function getTypeId(): int;


    /**
     * @return string The description title of the sponsor
     */
    abstract public function getDescriptionTitle(): string;


    /**
     * @return string The description of the sponsor
     */
    abstract public function getDescription(): string;


    /**
     * @return string The icon of the sponsor
     */
    abstract public function getIcon(): string;
}
