<?php

declare(strict_types=1);

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
    private ?DateTime $dateTime = null;


    /**
     * @param int $id The id of the sponsor
     * @param ?Person $godFather The godfather of the sponsor
     * @param ?Person $godChild The godchild of the sponsor
     * @param string $date The date of the sponsor
     */
    protected function __construct(int $id, ?Person $godFather, ?Person $godChild, string $date)
    {
        $this->id        = $id;
        $this->godFather = $godFather;
        $this->godChild  = $godChild;

        if ($date !== '' && $date !== '0') {
            $this->dateTime = DateTime::createFromFormat("Y-m-d", $date);
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
     * @param Person|null $person The new godfather of the sponsor
     * @param bool $force If true, the godchild will be set even if it is null
     */
    public function setGodFather(?Person $person, bool $force = false): void
    {
        if ($force || $person instanceof \App\Entity\old\person\Person) {
            $this->godFather = $person;
        }

        $this->godFather = $person ?? $this->godFather;
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
     * @param Person|null $person The new godchild of the sponsor
     * @param bool $force If true, the godchild will be set even if it is null
     */
    public function setGodChild(?Person $person, bool $force = false): void
    {
        if ($force || $person instanceof \App\Entity\old\person\Person) {
            $this->godChild = $person;
        }
    }


    /**
     * @return ?DateTime The date of the sponsor
     */
    public function getDate(): ?DateTime
    {
        return $this->dateTime;
    }


    /**
     * Formats the date of the sponsor
     * Used in the twig view
     * @param string $format The format to use
     * @return string The formatted date of the sponsor
     */
    public function formatDate(string $format): string
    {

        if ($this->dateTime instanceof \DateTime) {
            return $this->dateTime->format($format);
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


    #[\Override]
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
