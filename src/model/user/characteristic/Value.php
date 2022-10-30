<?php

namespace App\model\user\characteristic;

class Value {

    private string $value;

    public function __construct(string $value) {
        $this->value = $value;
    }

    public function __toString() {
        return $this->value;
    }

}