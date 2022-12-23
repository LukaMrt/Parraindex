<?php

namespace App\application\sponsor;

use App\model\sponsor\Sponsor;

interface SponsorDAO {

	public function getSponsorById(int $id): ?Sponsor;

	public function getPersonFamily(int $personId): ?array;

	public function getSponsorByPeopleId(int $godFatherId, int $godChildId): ?Sponsor;

}