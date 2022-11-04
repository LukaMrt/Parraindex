<?php

namespace App\model\school;

class SchoolAddress {

	private int $number;
	private string $street;
	private string $city;

	public function __construct(int $number, string $street, string $city) {
		$this->number = $number;
		$this->street = $street;
		$this->city = $city;
	}

}