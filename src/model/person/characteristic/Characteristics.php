<?php

namespace App\model\person\characteristic;

class Characteristics {

    private array $characteristics;

    public function __construct(Characteristic... $characteristics) {
        $this->characteristics = $characteristics;
    }

    public function addCharacteristic(Characteristic $characteristic): void {
        $this->characteristics[] = $characteristic;
    }

}