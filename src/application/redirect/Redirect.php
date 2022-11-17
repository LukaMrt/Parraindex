<?php

namespace App\application\redirect;

interface Redirect {

    public function redirect(string $url): void;

}