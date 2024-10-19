<?php

declare(strict_types=1);

namespace App\Application\sponsor;

use App\Entity\Sponsor\Sponsor;
use App\Entity\old\sponsor\SponsorFactory;
use App\Repository\PersonRepository;
use App\Repository\SponsorRepository;

readonly class SponsorService
{
    public SponsorDAO $sponsorDAO;

    public function __construct(
        private PersonRepository $personRepository,
        private SponsorRepository $sponsorRepository
    ) {
    }


    /**
     * Remove sponsor by id
     * @param int $id Id
     */
    public function removeSponsor(int $id): void
    {
        $this->sponsorDAO->removeSponsor($id);
    }



    /**
     * Get sponsor by id
     * @param int $id God father id
     */
    public function getSponsorById(int $id): ?Sponsor
    {
        return $this->sponsorRepository->getById($id);
    }


    /**
     * Update sponsor by id
     * @param int $id Id
     * @param array $parameters Parameters
     */
    public function updateSponsor(int $id, array $parameters): void
    {

        $sponsor = $this->sponsorDAO->getSponsorById($id);

        if ($sponsor === null || $parameters['sponsorType'] === '2') {
            return;
        }

        $godFather = $sponsor->getGodFather();
        $godChild  = $sponsor->getGodChild();

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
     */
    public function createSponsor(array $parameters): void
    {

        $godFather = $this->personRepository->getById($parameters['godFatherId']);
        $godChild  = $this->personRepository->getById($parameters['godChildId']);

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
     */
    public function addSponsor(Sponsor $sponsor): void
    {
        $this->sponsorDAO->addSponsor($sponsor);
    }
}
