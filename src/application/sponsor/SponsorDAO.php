<?php

namespace App\application\sponsor;

interface SponsorDAO {

	public function getPersonFamily(int $personId): array;

}