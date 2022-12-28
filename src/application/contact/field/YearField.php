<?php

namespace App\application\contact\field;

class YearField extends Field {

	public function __construct(string $name, string $error) {
		parent::__construct($name, $error);
	}

	public function isValid(string $value): bool {
		return is_numeric($value) && 2010 <= $value && $value <= date('Y');
	}

}