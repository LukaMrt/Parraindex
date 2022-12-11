<?php

namespace App\application\random;

interface Random {

	public function generate(int $length): string;

}