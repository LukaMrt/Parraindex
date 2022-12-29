<?php

namespace App\model\school;

class SchoolAddress {

	private string $street;
	private string $city;

	public function __construct(string $street, string $city) {
		$this->street = $street;
		$this->city = $city;
	}

    public static function emptyAddress() {
        return new SchoolAddress('', '');
    }

}