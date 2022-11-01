<?php

namespace App\model\school\degree;

class Degree {

	private string $name;
	private int $level;
	private int $ects;
	private int $duration;
	private bool $official;

	function __construct(string $name, int $level, int $ects, int $duration, bool $official) {
		$this->name = $name;
		$this->level = $level;
		$this->ects = $ects;
		$this->duration = $duration;
		$this->official = $official;
	}

}