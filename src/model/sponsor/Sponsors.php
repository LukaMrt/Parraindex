<?php

namespace App\model\sponsor;

class Sponsors {

    private array $sponsors;

    public function __construct(Sponsor ...$sponsors) {
        $this->sponsors = $sponsors;
    }

}