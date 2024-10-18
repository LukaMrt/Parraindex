<?php

declare(strict_types=1);

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
    #[\Override]
    public function getDescription(): string
    {
        return $this->getDescriptionTitle();
    }


    /**
     * @return string Description title of the sponsor (empty)
     */
    #[\Override]
    public function getDescriptionTitle(): string
    {
        return $this->getType();
    }


    /**
     * @return string Type of the sponsor (empty)
     */
    #[\Override]
    public function getType(): string
    {
        return '';
    }


    /**
     * @return string Icon of the sponsor
     */
    #[\Override]
    public function getIcon(): string
    {
        return 'interrogation.svg';
    }


    /**
     * @return int Type id of the sponsor
     */
    #[\Override]
    public function getTypeId(): int
    {
        return 2;
    }
}
