<?php

declare(strict_types=1);

namespace App\Entity\old\sponsor;

use App\Entity\old\person\Person;
use JsonSerializable;

/**
 * Classic sponsor
 */
class ClassicSponsor extends Sponsor implements JsonSerializable
{
    /**
     * @var string Reason of the sponsor
     */
    private string $reason;


    /**
     * @param int $id Id of the sponsor
     * @param ?Person $godFather Godfather of the sponsor
     * @param ?Person $godChild Godson of the sponsor
     * @param string $date Date of the sponsor
     * @param string $reason Reason of the sponsor
     */
    public function __construct(int $id, ?Person $godFather, ?Person $godChild, string $date, string $reason)
    {
        parent::__construct($id, $godFather, $godChild, $date);
        $this->reason = $reason;
    }


    /**
     * @return string Reason of the sponsor
     */
    #[\Override]
    public function getDescription(): string
    {
        return $this->reason;
    }


    /**
     * @return string Type of the sponsor
     */
    #[\Override]
    public function getType(): string
    {
        return 'Parrainage IUT';
    }


    /**
     * @return string Title of the description
     */
    #[\Override]
    public function getDescriptionTitle(): string
    {
        return 'Raison du choix';
    }


    /**
     * @return string Icon of the sponsor
     */
    #[\Override]
    public function getIcon(): string
    {
        return 'hammers.svg';
    }


    /**
     * @return int Type id of the sponsor
     */
    #[\Override]
    public function getTypeId(): int
    {
        return 0;
    }


    #[\Override]
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), get_object_vars($this));
    }
}
