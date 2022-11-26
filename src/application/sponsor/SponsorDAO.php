<?php

namespace App\application\sponsor;

interface SponsorDAO {

	public function getGodFathers(int $personId): array;

	public function getGodSons(int $personId): array;

}