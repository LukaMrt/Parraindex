<?php

namespace App\application\sponsor;

use App\application\person\PersonDAO;
use App\model\sponsor\Sponsor;
use App\model\sponsor\SponsorFactory;

class SponsorService
{
    private SponsorDAO $sponsorDAO;
    private PersonDAO $personDAO;


    public function __construct(SponsorDAO $sponsorDAO, PersonDAO $personDAO)
    {
        $this->sponsorDAO = $sponsorDAO;
        $this->personDAO = $personDAO;
    }


    public function getPersonFamily(int $personId): ?array
    {
        return $this->sponsorDAO->getPersonFamily($personId);
    }


    public function removeSponsor(int $id): void
    {
        $this->sponsorDAO->removeSponsor($id);
    }


    public function getSponsor(int $id): ?Sponsor
    {
        return $this->sponsorDAO->getSponsorById($id);
    }


    public function getSponsorById(int $int): ?Sponsor
    {
        $sponsor = $this->sponsorDAO->getSponsorById($int);

        if ($sponsor === null) {
            return null;
        }

        $godFather = $this->personDAO->getPersonById($sponsor->getGodFather()->getId());
        $godSon = $this->personDAO->getPersonById($sponsor->getGodChild()->getId());
        $sponsor->setGodFather($godFather);
        $sponsor->setGodSon($godSon);
        return $sponsor;
    }


    public function updateSponsor(int $id, array $parameters): void
    {

        $sponsor = $this->sponsorDAO->getSponsorById($id);

        if ($sponsor === null || $parameters['sponsorType'] === '2') {
            return;
        }

        $godFather = $sponsor->getGodFather();
        $godChild = $sponsor->getGodChild();

        $sponsor = SponsorFactory::createSponsor(
            $parameters['sponsorType'],
            $id,
            $godFather,
            $godChild,
            $parameters['sponsorDate'],
            $parameters['description']
        );

        $this->sponsorDAO->updateSponsor($sponsor);
    }


    public function createSponsor(array $parameters): void
    {

        $godFather = $this->personDAO->getPersonById($parameters['godFatherId']);
        $godChild = $this->personDAO->getPersonById($parameters['godChildId']);

        $sponsor = $this->sponsorDAO->getSponsorByPeopleId($godFather->getId(), $godChild->getId());

        if ($sponsor !== null) {
            return;
        }

        $sponsor = SponsorFactory::createSponsor(
            $parameters['sponsorType'],
            -1,
            $godFather,
            $godChild,
            $parameters['sponsorDate'],
            $parameters['description']
        );

        $this->sponsorDAO->addSponsor($sponsor);
    }


    public function addSponsor(Sponsor $sponsor): void
    {
        $this->sponsorDAO->addSponsor($sponsor);
    }
}
