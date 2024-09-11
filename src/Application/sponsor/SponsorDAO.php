<?php

namespace App\Application\sponsor;

use App\Entity\old\sponsor\Sponsor;

interface SponsorDAO
{
    /**
     * Get sponsor by id
     * @param int $id Id
     * @return Sponsor|null
     */
    public function getSponsorById(int $id): ?Sponsor;


    /**
     * Get person Family by id
     * @param int $personId Person id
     * @return array|null
     */
    public function getPersonFamily(int $personId): ?array;


    /**
     * Get person sponsor by people id
     * @param int $godFatherId God father id
     * @param int $godChildId God child id
     * @return Sponsor|null
     */
    public function getSponsorByPeopleId(int $godFatherId, int $godChildId): ?Sponsor;


    /**
     * Remove sponsor
     * @param int $id Id
     * @return void
     */
    public function removeSponsor(int $id): void;


    /**
     * Add sponsor
     * @param Sponsor $sponsor Sponsor
     * @return void
     */
    public function addSponsor(Sponsor $sponsor): void;


    /**
     * Update sponsor
     * @param Sponsor $sponsor Sponsor
     * @return void
     */
    public function updateSponsor(Sponsor $sponsor): void;
}
