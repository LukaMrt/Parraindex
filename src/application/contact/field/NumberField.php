<?php

namespace App\application\contact\field;

class NumberField extends Field
{

    public function isValid(string $value): bool
    {
        return is_numeric($value);
    }
}
