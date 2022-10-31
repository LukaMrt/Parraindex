<?php

namespace App\model\person\characteristic;

class Value {

    private string $value;

    public function __construct(string $value) {
        $this->value = $value;
    }

    public function __toString() {
        return $this->value;
    }

}