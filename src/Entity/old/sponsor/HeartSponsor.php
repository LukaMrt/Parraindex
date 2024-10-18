<?php

declare(strict_types=1);

namespace App\Entity\old\sponsor;

use App\Entity\old\person\Person;
use JsonSerializable;

/**
 * Heart sponsor
 */
class HeartSponsor extends Sponsor implements JsonSerializable
{
    /**
     * @var string Description of the sponsor
     */
    private string $description;


    /**
     * @param int $id Id of the sponsor
     * @param Person $godFather Godfather of the sponsor
     * @param Person $godChild Godson of the sponsor
     * @param string $date Date of the sponsor
     * @param string $description Reason of the sponsor
     */
    public function __construct(int $id, Person $godFather, Person $godChild, string $date, string $description)
    {
        parent::__construct($id, $godFather, $godChild, $date);
        $this->description = $description;
    }


    /**
     * @return string Description of the sponsor
     */
    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }


    /**
     * @return string Title of the sponsor type
     */
    #[\Override]
    public function getType(): string
    {
        return 'Parrainage de coeur';
    }


    /**
     * @return string Description title of the sponsor
     */
    #[\Override]
    public function getDescriptionTitle(): string
    {
        return 'Description de la demande';
    }


    /**
     * @return string Icon of the sponsor
     */
    #[\Override]
    public function getIcon(): string
    {
        return 'heart.svg';
    }


    /**
     * @return int Id of the sponsor type
     */
    #[\Override]
    public function getTypeId(): int
    {
        return 1;
    }


    #[\Override]
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), get_object_vars($this));
    }
}
