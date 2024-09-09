<?php

namespace App\Entity\old\sponsor;

use App\Entity\old\person\Person;
use DateTime;
use JsonSerializable;

/**
 * Represents a sponsor
 */
abstract class Sponsor implements JsonSerializable
{
    /**
     * @var int The id of the sponsor
     */
    private int $id;
    /**
     * @var ?Person The godfather of the sponsor
     */
    private ?Person $godFather;
    /**
     * @var ?Person The godchild of the sponsor
     */
    private ?Person $godChild;
    /**
     * @var DateTime|null The date of the sponsor
     */
    private ?DateTime $date;


    /**
     * @param int $id The id of the sponsor
     * @param ?Person $godFather The godfather of the sponsor
     * @param ?Person $godChild The godchild of the sponsor
     * @param string $date The date of the sponsor
     */
    protected function __construct(int $id, ?Person $godFather, ?Person $godChild, string $date)
    {
        $this->id = $id;
        $this->godFather = $godFather;
        $this->godChild = $godChild;
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
     * @param bool $force If true, the godchild will be set even if it is null
     * @return void
     */
    public function setGodFather(?Person $godFather, bool $force = false): void
    {
        if ($force || $godFather) {
            $this->godFather = $godFather;
        }
        $this->godFather = $godFather ?? $this->godFather;
    }


    /**
     * @return Person The godchild of the sponsor
     */
    public function getGodChild(): Person
    {
        return $this->godChild;
    }


    /**
     * Sets the godchild of the sponsor
     * @param Person|null $godSon The new godchild of the sponsor
     * @param bool $force If true, the godchild will be set even if it is null
     * @return void
     */
    public function setGodChild(?Person $godSon, bool $force = false): void
    {
        if ($force || $godSon) {
            $this->godChild = $godSon;
        }
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


    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
