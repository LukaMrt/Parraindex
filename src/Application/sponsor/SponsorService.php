<?php

namespace App\Application\sponsor;

use App\Application\person\PersonDAO;
use App\Entity\old\sponsor\Sponsor;
use App\Entity\old\sponsor\SponsorFactory;
use App\Repository\PersonRepository;
use App\Repository\SponsorRepository;

readonly class SponsorService
{
    public function __construct(
        private PersonRepository $personRepository,
        private SponsorRepository $sponsorRepository ) {
    }


    /**
     * Get person family by id
     * @param int $personId Person id
     * @return array|null
     */
    public function getPersonFamily(int $personId): ?array
    {
        return $this->sponsorDAO->getPersonFamily($personId);
    }


    /**
     * Remove sponsor by id
     * @param int $id Id
     * @return void
     */
    public function removeSponsor(int $id): void
    {
        $this->sponsorDAO->removeSponsor($id);
    }


    /**
     * Get sponsor by id
     * @param int $id Id
     * @return Sponsor|null
     */
    public function getSponsor(int $id): ?Sponsor
    {
        return $this->sponsorDAO->getSponsorById($id);
    }


    /**
     * Get sponsor by id
     * @param int $int God father id
     * @return Sponsor|null
     */
    public function getSponsorById(int $int): ?Sponsor
    {
        $sponsor = $this->sponsorDAO->getSponsorById($int);

        if ($sponsor === null) {
            return null;
        }

        $godFather = $this->personRepository->getById($sponsor->getGodFather()->getId());
        $godSon = $this->personRepository->getById($sponsor->getGodChild()->getId());
        $sponsor->setGodFather($godFather);
        $sponsor->setGodChild($godSon);
        return $sponsor;
    }


    /**
     * Update sponsor by id
     * @param int $id Id
     * @param array $parameters Parameters
     * @return void
     */
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


    /**
     * Create sponsor
     * @param array $parameters Parameters
     * @return void
     */
    public function createSponsor(array $parameters): void
    {

        $godFather = $this->personRepository->getById($parameters['godFatherId']);
        $godChild = $this->personRepository->getById($parameters['godChildId']);

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


    /**
     * Get sponsor by people id
     * @param Sponsor $sponsor Sponsor
     * @return void
     */
    public function addSponsor(Sponsor $sponsor): void
    {
        $this->sponsorDAO->addSponsor($sponsor);
    }
}
