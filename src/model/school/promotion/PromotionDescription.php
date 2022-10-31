<?php

namespace App\model\school\promotion;

class PromotionDescription {

    private string $speciality;
    private string $description;

    public function __construct(string $speciality, string $description) {
        $this->speciality = $speciality;
        $this->description = $description;
    }

}