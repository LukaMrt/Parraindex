<?php

namespace App\application\contact\field;

use DateTime;

class DateField extends Field {

	private const UNIX_TIMESTAMP_MIN = 1_262_304_000;

	public function __construct(string $name, string $error) {
		parent::__construct($name, $error);
	}

	public function isValid(string $value): bool {
		$date = DateTime::createFromFormat('Y-m-d', $value);
		return $date !== false && self::UNIX_TIMESTAMP_MIN <= $date->getTimestamp();
	}

}