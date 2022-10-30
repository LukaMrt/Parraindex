<?php

namespace App\model\utils;

class Url {

    private string $url;

    public function __construct(string $url) {
        $this->url = $url;
    }

    public function __toString() {
        return $this->url;
    }

}