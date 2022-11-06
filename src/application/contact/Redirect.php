<?php

namespace App\application\contact;

use JetBrains\PhpStorm\NoReturn;

interface Redirect {

	#[NoReturn] public function redirect(string $url): void;

}