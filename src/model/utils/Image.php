<?php

namespace App\model\utils;

class Image {

    private string $url;

    public function __construct(string $url) {
        $this->url = $url;
    }

    public static function empty(): Image {
        return new Image("");
    }

    public function __toString() {
        return $this->url;
    }

}