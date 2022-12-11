<?php

namespace App\application\random;

class DefaultRandom implements Random {

	public function generate(int $length): string {
		return bin2hex(random_bytes($length));
	}

}