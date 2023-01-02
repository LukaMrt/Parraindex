<?php

namespace App\application\contact\field;

class BoundedNumberField extends Field
{
    private int $min;
    private int $max;

    public function __construct(string $name, string $error, int $min, int $max)
    {
        parent::__construct($name, $error);
        $this->min = min($min, $max);
        $this->max = max($min, $max);
    }

    public function isValid($value): bool
    {
        return $this->min <= $value && $value <= $this->max;
    }
}
