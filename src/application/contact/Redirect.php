<?php

namespace App\application\contact;

interface Redirect {

	public function redirect(string $url): void;

}