<?php

namespace App\model\person;

class Biography {

    private array $lines;

    public function __construct(string... $lines) {
        $this->lines = $lines;
    }

    public function __toString(): string {
        $string = "";
        foreach ($this->lines as $line) {
            $string .= $line . "\n";
        }
        return $string;
    }

}