<?php

namespace App\application\contact\field;

class EmailField extends Field
{
    public function __construct(string $name, string $error)
    {
        parent::__construct($name, $error);
    }

    public function isValid(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }
}
