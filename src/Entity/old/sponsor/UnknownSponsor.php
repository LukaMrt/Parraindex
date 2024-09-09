<?php

namespace App\Entity\old\sponsor;

use App\Entity\old\person\Person;

/**
 * Unknown sponsor
 */
class UnknownSponsor extends Sponsor
{
    /**
     * @param int $id Id of the sponsor
     * @param Person $godFather Godfather of the sponsor
     * @param Person $godChild Godchild of the sponsor
     * @param string $date Date of the sponsor
     */
    public function __construct(int $id, Person $godFather, Person $godChild, string $date = '')
    {
        parent::__construct($id, $godFather, $godChild, $date);
    }


    /**
     * @return string Description of the sponsor (empty)
     */
    public function getDescription(): string
    {
        return $this->getDescriptionTitle();
    }


    /**
     * @return string Description title of the sponsor (empty)
     */
    public function getDescriptionTitle(): string
    {
        return $this->getType();
    }


    /**
     * @return string Type of the sponsor (empty)
     */
    public function getType(): string
    {
        return '';
    }


    /**
     * @return string Icon of the sponsor
     */
    public function getIcon(): string
    {
        return 'interrogation.svg';
    }


    /**
     * @return int Type id of the sponsor
     */
    public function getTypeId(): int
    {
        return 2;
    }
}
