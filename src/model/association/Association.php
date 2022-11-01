<?php

namespace App\model\association;

class Association {

	private int $siret;
	private string $name;

	public function __construct(int $siret, string $name) {
		$this->siret = $siret;
		$this->name = $name;
	}

}