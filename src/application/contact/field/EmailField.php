<?php

namespace App\application\contact\field;

class EmailField extends Field
{

    public function isValid(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }
}
