<?php

namespace App\model\sponsor;

use App\model\person\Person;
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
     * @param Person $godSon Godson of the sponsor
     * @param string $date Date of the sponsor
     * @param string $description Reason of the sponsor
     */
    public function __construct(int $id, Person $godFather, Person $godSon, string $date, string $description)
    {
        parent::__construct($id, $godFather, $godSon, $date);
        $this->description = $description;
    }


    /**
     * @return string Description of the sponsor
     */
    public function getDescription(): string
    {
        return $this->description;
    }


    /**
     * @return string Title of the sponsor type
     */
    public function getType(): string
    {
        return 'Parrainage de coeur';
    }


    /**
     * @return string Description title of the sponsor
     */
    public function getDescriptionTitle(): string
    {
        return 'Description de la demande';
    }


    /**
     * @return string Icon of the sponsor
     */
    public function getIcon(): string
    {
        return 'heart.svg';
    }


    /**
     * @return int Id of the sponsor type
     */
    public function getTypeId(): int
    {
        return 1;
    }


    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), get_object_vars($this));
    }
}
