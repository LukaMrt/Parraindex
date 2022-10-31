<?php

namespace App\model\utils;

class Id {

    private int $id;

    public function __construct(int $id) {
        $this->id = $id;
    }

    public function __toString() {
        return (string) $this->id;
    }

    public function isValid(): bool {
        return $this->id >= 0;
    }

}